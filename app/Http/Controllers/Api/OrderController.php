<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Orders",
 * )
 */
class OrderController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/orders",
     *     summary="Get list of orders",
     *     description="Get all orders for the authenticated user",
     *     operationId="getOrders",
     *     tags={"Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="user_id", type="integer", example=1),
     *                     @OA\Property(property="total_amount", type="number", format="float", example=299.98),
     *                     @OA\Property(property="status", type="string", example="pending"),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time"),
     *                     @OA\Property(property="items", type="array",
     *                         @OA\Items(
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="product_id", type="integer", example=1),
     *                             @OA\Property(property="quantity", type="integer", example=2),
     *                             @OA\Property(property="price", type="number", format="float", example=99.99),
     *                             @OA\Property(property="subtotal", type="number", format="float", example=199.98)
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $orders = Order::with(['items.product', 'user'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/orders",
     *     summary="Create a new order",
     *     description="Create a new order with products",
     *     operationId="createOrder",
     *     tags={"Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"items"},
     *             @OA\Property(property="items", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="product_id", type="integer", example=1),
     *                     @OA\Property(property="quantity", type="integer", example=2)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Order created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Order created successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Create order
            $order = Order::create([
                'user_id' => $request->user()->id,
                'total_amount' => 0,
                'status' => 'pending'
            ]);

            // Add items
            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                
                $subtotal = $product->price * $item['quantity'];

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                    'subtotal' => $subtotal
                ]);
            }

            // Update total amount
            $order->calculateTotal();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'data' => $order->load('items.product')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/orders/{id}",
     *     summary="Get order details",
     *     description="Get details of a specific order",
     *     operationId="getOrder",
     *     tags={"Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Order ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order not found"
     *     )
     * )
     */
    public function show(Request $request, $id)
    {
        $order = Order::with(['items.product', 'user'])
            ->where('user_id', $request->user()->id)
            ->find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $order
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/orders/{id}/items",
     *     summary="Add product to order",
     *     description="Add a product to an existing order",
     *     operationId="addOrderItem",
     *     tags={"Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Order ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"product_id", "quantity"},
     *             @OA\Property(property="product_id", type="integer", example=1),
     *             @OA\Property(property="quantity", type="integer", example=2)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product added successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order not found"
     *     )
     * )
     */
    public function addItem(Request $request, $id)
    {
        $order = Order::where('user_id', $request->user()->id)->find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $product = Product::findOrFail($request->product_id);
            $subtotal = $product->price * $request->quantity;

            // Check if product already exists in order
            $existingItem = OrderItem::where('order_id', $order->id)
                ->where('product_id', $product->id)
                ->first();

            if ($existingItem) {
                // Update existing item
                $existingItem->quantity += $request->quantity;
                $existingItem->subtotal = $existingItem->quantity * $existingItem->price;
                $existingItem->save();
            } else {
                // Create new item
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $request->quantity,
                    'price' => $product->price,
                    'subtotal' => $subtotal
                ]);
            }

            // Update total amount
            $order->calculateTotal();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Product added to order successfully',
                'data' => $order->load('items.product')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to add product to order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/orders/{id}/items/{itemId}",
     *     summary="Update order item",
     *     description="Update quantity of a product in order",
     *     operationId="updateOrderItem",
     *     tags={"Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Order ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="itemId",
     *         in="path",
     *         description="Order Item ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"quantity"},
     *             @OA\Property(property="quantity", type="integer", example=3)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order item updated successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order or item not found"
     *     )
     * )
     */
    public function updateItem(Request $request, $id, $itemId)
    {
        $order = Order::where('user_id', $request->user()->id)->find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $item = OrderItem::where('order_id', $order->id)->find($itemId);

            if (!$item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order item not found'
                ], 404);
            }

            $item->quantity = $request->quantity;
            $item->subtotal = $item->price * $request->quantity;
            $item->save();

            // Update total amount
            $order->calculateTotal();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order item updated successfully',
                'data' => $order->load('items.product')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update order item',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/orders/{id}/items/{itemId}",
     *     summary="Delete order item",
     *     description="Remove a product from order",
     *     operationId="deleteOrderItem",
     *     tags={"Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Order ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="itemId",
     *         in="path",
     *         description="Order Item ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order item deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order or item not found"
     *     )
     * )
     */
    public function deleteItem(Request $request, $id, $itemId)
    {
        $order = Order::where('user_id', $request->user()->id)->find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        try {
            DB::beginTransaction();

            $item = OrderItem::where('order_id', $order->id)->find($itemId);

            if (!$item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order item not found'
                ], 404);
            }

            $item->delete();

            // Update total amount
            $order->calculateTotal();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order item deleted successfully',
                'data' => $order->load('items.product')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete order item',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
