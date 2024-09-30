<?php

namespace App\Services\Customer;

use App\Models\Customer\Customer;
use App\Models\Customer\CustomerContactDetails;
use App\Models\Customer\CustomerDeliveryAddress;
use App\Models\Customer\CustomerFinanceDetails;
use App\Models\Customer\CustomerShippingAddress;
use App\Models\Customer\CustomerWarehouseAddress;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CustomerService
{

    public function createCustomer(Request $request)
    {

        // save customer data
        $customer = Customer::create([
            'company_name' => $request['company_name'],
            'customer_type' => $request['customer_type'],
            'address' => $request['address'],
            'city' => $request['city'],
            'state' => $request['state'],
            'country' => $request['country'],
            'zip_code' => $request['zip_code'],
            'company_tax_id' => $request['company_tax_id'],
            'payment_terms' => $request['payment_terms'],
            'credit_limit' => $request['credit_limit'],
            'contact_name' => $request['contact_name'],
            'phone' => $request['phone'],
            'email' => $request['email'],

        ]);

        // Create Warehouse Address
        $this->storeWarehouseAddress($request, $customer);

        // Create Warehouse Address
        $this->storeShippingAddress($request, $customer);

        // Create Warehouse Address
        $this->storeDeliveryAddress($request, $customer);

        // Create Warehouse Address
        $this->storeContactDetails($request, $customer);

        // Create Warehouse Address
        $this->storeFinanceDetails($request, $customer);

        return true;
    }


    public function updateCustomer(Request $request, $id)
    {
        $customer = Customer::find($id);

        // Delete existing related records
        $customer->warehouse()->delete();
        $customer->shipping()->delete();
        $customer->delivery()->delete();
        $customer->contact()->delete();
        $customer->finance()->delete();

        $customer->update([
            'company_name' => $request['company_name'],
            'customer_type' => $request['customer_type'],
            'address' => $request['address'],
            'city' => $request['city'],
            'state' => $request['state'],
            'country' => $request['country'],
            'zip_code' => $request['zip_code'],
            'company_tax_id' => $request['company_tax_id'],
            'payment_terms' => $request['payment_terms'],
            'credit_limit' => $request['credit_limit'],
            'contact_name' => $request['contact_name'],
            'phone' => $request['phone'],
            'email' => $request['email'],
        ]);

        // Create Warehouse Address
        $this->storeWarehouseAddress($request, $customer);

        // Create Warehouse Address
        $this->storeShippingAddress($request, $customer);

        // Create Warehouse Address
        $this->storeDeliveryAddress($request, $customer);

        // Create Warehouse Address
        $this->storeContactDetails($request, $customer);

        // Create Warehouse Address
        $this->storeFinanceDetails($request, $customer);


        return true;
    }


    public function storeWarehouseAddress(Request $request, $customer)
    {
        $warehouseData = $request->input('warehouse');
        $warehouse = [];

        foreach ($warehouseData as $warehouseItem) {
            $warehouse[] = new CustomerWarehouseAddress([
                'warehouse_name' => $warehouseItem['warehouse_name'],
                'warehouse_address' => $warehouseItem['warehouse_address'],
                'warehouse_city' => $warehouseItem['warehouse_city'],
                'warehouse_state' => $warehouseItem['warehouse_state'],
                'warehouse_country' => $warehouseItem['warehouse_country'],
                'warehouse_zip_code' => $warehouseItem['warehouse_zip_code'],
                'customer_id' => $customer->id
            ]);
        }

        // Save all warehouse related to customer
        $customer->warehouse()->saveMany($warehouse);
    }
    public function storeShippingAddress(Request $request, $customer)
    {
        $shippingData = $request->input('shipping');
        $shipping = [];

        foreach ($shippingData as $shippingItem) {
            $shipping[] = new CustomerShippingAddress([
                'shipping_name' => $shippingItem['shipping_name'],
                'shipping_address' => $shippingItem['shipping_address'],
                'shipping_city' => $shippingItem['shipping_city'],
                'shipping_state' => $shippingItem['shipping_state'],
                'shipping_country' => $shippingItem['shipping_country'],
                'shipping_zip_code' => $shippingItem['shipping_zip_code'],
                'customer_id' => $customer->id
            ]);
        }

        // Save all shipping related to customer
        $customer->shipping()->saveMany($shipping);
    }
    public function storeDeliveryAddress(Request $request, $customer)
    {
        $deliveryData = $request->input('delivery');
        $delivery = [];

        foreach ($deliveryData as $deliveryItem) {
            $delivery[] = new CustomerDeliveryAddress([
                'delivery_name' => $deliveryItem['delivery_name'],
                'delivery_address' => $deliveryItem['delivery_address'],
                'delivery_city' => $deliveryItem['delivery_city'],
                'delivery_state' => $deliveryItem['delivery_state'],
                'delivery_country' => $deliveryItem['delivery_country'],
                'delivery_zip_code' => $deliveryItem['delivery_zip_code'],
                'customer_id' => $customer->id
            ]);
        }

        // Save all delivery related to customer
        $customer->delivery()->saveMany($delivery);
    }
    public function storeContactDetails(Request $request, $customer)
    {
        $contactData = $request->input('contact');
        $contact = [];

        foreach ($contactData as $contactItem) {
            $contact[] = new CustomerContactDetails([
                'contact_name' => $contactItem['contact_name'],
                'contact_designation' => $contactItem['contact_designation'],
                'contact_phone' => $contactItem['contact_phone'],
                'contact_email' => $contactItem['contact_email'],
                'contact_fax' => $contactItem['contact_fax'],
                'customer_id' => $customer->id
            ]);
        }

        // Save all contact related to customer
        $customer->contact()->saveMany($contact);
    }
    public function storeFinanceDetails(Request $request, $customer)
    {
        $financeData = $request->input('finance');
        $finance = [];

        foreach ($financeData as $financeItem) {
            $finance[] = new CustomerFinanceDetails([
                'finance_name' => $financeItem['finance_name'],
                'finance_designation' => $financeItem['finance_designation'],
                'finance_phone' => $financeItem['finance_phone'],
                'finance_email' => $financeItem['finance_email'],
                'finance_fax' => $financeItem['finance_fax'],
                'customer_id' => $customer->id
            ]);
        }

        // Save all finance related to customer
        $customer->finance()->saveMany($finance);
    }
}
