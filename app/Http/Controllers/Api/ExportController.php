<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Exports\ProductsExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\Snappy\Facades\SnappyPdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * @OA\Tag(
 *     name="Export",
 *     description="API Endpoints for exporting data"
 * )
 */
class ExportController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/export/products/excel",
     *     summary="Export products to Excel",
     *     description="Export all products to Excel file and download",
     *     operationId="exportProductsExcel",
     *     tags={"Export"},
     *     @OA\Response(
     *         response=200,
     *         description="Excel file download",
     *         @OA\MediaType(
     *             mediaType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function exportProductsExcel()
    {
        try {
            $fileName = 'products_' . date('Y-m-d_His') . '.xlsx';

            return Excel::download(new ProductsExport, $fileName);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export Excel file',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/export/products/pdf",
     *     summary="Export products to PDF",
     *     description="Export all products to PDF file using blade template and save to storage",
     *     operationId="exportProductsPdf",
     *     tags={"Export"},
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="PDF exported successfully"),
     *             @OA\Property(property="file_path", type="string", example="exports/products_2025-11-12_120530.pdf"),
     *             @OA\Property(property="download_url", type="string", example="/api/export/download/products_2025-11-12_120530.pdf")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function exportProductsPdf()
    {
        try {
            $products = Product::all();

            // Render blade -> HTML
            $html = view('exports.products-pdf', compact('products'))->render();

            $pdf = SnappyPdf::loadHTML($html);

            $pdf->setOption('enable-local-file-access', true);
            $pdf->setOption('margin-top', 10);
            $pdf->setOption('margin-right', 10);
            $pdf->setOption('margin-bottom', 10);
            $pdf->setOption('margin-left', 10);

            $fileName = 'products_' . date('Y-m-d_His') . '.pdf';
            $filePath = 'exports/' . $fileName;

            Storage::disk('public')->put($filePath, $pdf->output());

            return response()->json([
                'success' => true,
                'message' => 'PDF exported successfully',
                'file_path' => $filePath,
                'download_url' => '/api/export/download/' . $fileName
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export PDF file',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function exportProductsPdfDownload()
    {
        try {
            $products = Product::all();

            // Render blade -> HTML
            $html = view('exports.products-pdf', compact('products'))->render();

            $pdf = SnappyPdf::loadHTML($html);

            $pdf->setOption('enable-local-file-access', true);
            $pdf->setOption('margin-top', 10);
            $pdf->setOption('margin-right', 10);
            $pdf->setOption('margin-bottom', 10);
            $pdf->setOption('margin-left', 10);

            $fileName = 'products_' . date('Y-m-d_His') . '.pdf';

            return response($pdf->output(), 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export PDF file',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
