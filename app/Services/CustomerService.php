<?php

namespace App\Services;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class CustomerService
{
    /**
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAllCustomers(int $perPage = 15): LengthAwarePaginator
    {
        return Customer::orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * @param int $id
     * @return Customer|null
     */
    public function getCustomerById(int $id): ?Customer
    {
        return Customer::find($id);
    }

    /**
     * @param array $data
     * @return Customer
     */
    public function createCustomer(array $data): Customer
    {
        return Customer::create($data);
    }

    /**
     * @param Customer $customer
     * @param array $data
     * @return Customer
     */
    public function updateCustomer(Customer $customer, array $data): Customer
    {
        $customer->update($data);
        return $customer->fresh();
    }

    /**
     * @param Customer $customer
     * @return bool
     */
    public function deleteCustomer(Customer $customer): bool
    {
        return $customer->delete();
    }

    /**
     * @param string $keyword
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function searchCustomers(string $keyword, int $perPage = 15): LengthAwarePaginator
    {
        return Customer::where('name', 'like', "%{$keyword}%")
            ->orWhere('email', 'like', "%{$keyword}%")
            ->orWhere('phone', 'like', "%{$keyword}%")
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getActiveCustomers(int $perPage = 15): LengthAwarePaginator
    {
        return Customer::where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * @param Customer $customer
     * @return Customer
     */
    public function toggleActiveStatus(Customer $customer): Customer
    {
        $customer->is_active = !$customer->is_active;
        $customer->save();
        return $customer;
    }
}
