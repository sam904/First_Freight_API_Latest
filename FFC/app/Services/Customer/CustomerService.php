<?php

namespace App\Services\Customer;

use App\Helpers\SearchHelper;
use App\Models\Country;
use App\Models\Customer\Customer;
use App\Models\Customer\CustomerContactDetails;
use App\Models\Customer\CustomerDeliveryAddress;
use App\Models\Customer\CustomerFinanceDetails;
use App\Models\Customer\CustomerShippingAddress;
use App\Models\Customer\CustomerWarehouseAddress;
use App\Models\State;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CustomerService
{

    public function getAllCustomer(Request $request)
    {
        $searchTerm = $request->input('searchTerm');
        $filterBy = $request->input('filterBy');
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');
        $page = $request->input('page') ?: 1;
        $limit = $request->input('limit');
        $sortColumn = $request->input('sortColumn') ?: 'id';
        $sortDirection = $request->input('sortDirection') ?: 'desc';
        $isExport = $request->input('export') ?? false;
        $ids = $request->input('ids');

        $query = Customer::with([
            'country',
            'state',
            'contact',
            'finance',
            'delivery.state',
            'delivery.country',
        ]);

        // Apply filter by IDs if they are provided
        if (!empty($ids)) {
            $query->whereIn('id', $ids);
        }

        $contactFlag = false;
        $financeFlag = false;
        $deliveryFlag = false;
        $countryFlag = false;
        $stateFlag = false;

        // Check if filterBy contains contact, finance, delivery, country, state
        if (
            strpos($filterBy, 'contact.') === 0 ||
            strpos($filterBy, 'finance.') === 0 ||
            strpos($filterBy, 'delivery.') === 0 ||
            $filterBy == 'country' || $filterBy == 'state'
        ) {
            // Set filterBy to null in the request
            $request->merge(['filterBy' => null]);
            if (strpos($filterBy, 'contact.') === 0) {
                $filterBy = substr($filterBy, strlen('contact.'));
                $contactFlag = true;
            } elseif (strpos($filterBy, 'finance.') === 0) {
                $filterBy = substr($filterBy, strlen('finance.'));
                $financeFlag = true;
            } elseif (strpos($filterBy, 'delivery.') === 0) {
                $filterBy = substr($filterBy, strlen('delivery.'));
                $deliveryFlag = true;
            } elseif ($filterBy == 'country') {
                $countryFlag = true;
            } elseif ($filterBy == 'state') {
                $stateFlag = true;
            }
        }

        if ($filterBy == null) {
            Log::info('filterBy is null... so all flag is true');
            $contactFlag = true;
            $financeFlag = true;
            $deliveryFlag = true;
            $stateFlag = true;
            $countryFlag = true;
        }

        // Get all column names of the 'Customers' table
        $model = new Customer();
        // Apply search filters
        $query = SearchHelper::applySearchFilters($query, $model, $request);

        $query->where(
            function ($query) use ($searchTerm, $filterBy, $contactFlag, $financeFlag, $deliveryFlag, $countryFlag, $stateFlag) {

                // Search within related Contact fields
                if ($contactFlag) {
                    $contactModel = new CustomerContactDetails();
                    $searchableContactColumns = $contactModel->getSearchableColumns();
                    $query->orWhereHas('contact', function ($query) use ($searchTerm, $filterBy, $searchableContactColumns) {
                        if ($filterBy && in_array($filterBy, $searchableContactColumns)) {
                            Log::info('contact FilterBy =' . $filterBy);
                            $query->where($filterBy, 'LIKE', "%{$searchTerm}%");
                        } else {
                            Log::info('contact Search on whole table =' . $searchTerm);
                            $query->where('contact_name', 'LIKE', "%{$searchTerm}%")
                                ->orWhere('contact_designation', 'LIKE', "%{$searchTerm}%")
                                ->orWhere('contact_phone', 'LIKE', "%{$searchTerm}%")
                                ->orWhere('contact_email', 'LIKE', "%{$searchTerm}%")
                                ->orWhere('contact_fax', 'LIKE', "%{$searchTerm}%");
                        }
                    });
                }

                // Search within related Finance fields
                if ($financeFlag) {
                    $financeModel = new CustomerFinanceDetails();
                    $searchableFinanceColumns = $financeModel->getSearchableColumns();
                    $query->orWhereHas('finance', function ($query) use ($searchTerm, $filterBy, $searchableFinanceColumns) {
                        if ($filterBy && in_array($filterBy, $searchableFinanceColumns)) {
                            Log::info('finance FilterBy =' . $filterBy);
                            $query->where($filterBy, 'LIKE', "%{$searchTerm}%");
                        } else {
                            Log::info('finance Search on whole table =' . $searchTerm);
                            $query->where('finance_name', 'LIKE', "%{$searchTerm}%")
                                ->orWhere('finance_designation', 'LIKE', "%{$searchTerm}%")
                                ->orWhere('finance_phone', 'LIKE', "%{$searchTerm}%")
                                ->orWhere('finance_email', 'LIKE', "%{$searchTerm}%")
                                ->orWhere('finance_fax', 'LIKE', "%{$searchTerm}%");
                        }
                    });
                }

                // Search within related Delivery fields
                if ($deliveryFlag) {
                    $deliveryModel = new CustomerDeliveryAddress();
                    $searchableDeliveryColumns = $deliveryModel->getSearchableColumns();
                    $query->orWhereHas('delivery', function ($query) use ($searchTerm, $filterBy, $searchableDeliveryColumns) {
                        if ($filterBy && in_array($filterBy, $searchableDeliveryColumns)) {
                            Log::info('Delivery FilterBy =' . $filterBy);
                            $query->where($filterBy, 'LIKE', "%{$searchTerm}%");
                        } else {
                            Log::info('delivery Search on whole table =' . $searchTerm);
                            $query->where('delivery_name', 'LIKE', "%{$searchTerm}%")
                                ->orWhere('delivery_address', 'LIKE', "%{$searchTerm}%")
                                ->orWhere('delivery_city', 'LIKE', "%{$searchTerm}%")
                                ->orWhere('delivery_zip_code', 'LIKE', "%{$searchTerm}%");
                        }
                    })->orWhereHas('delivery.state', function ($query) use ($searchTerm) {
                        Log::info("delivery.state Search on state table = {$searchTerm}");
                        $query->where('name', 'LIKE', "%{$searchTerm}%");
                    })->orWhereHas('delivery.country', function ($query) use ($searchTerm) {
                        Log::info('delivery.country Search on country table =' . $searchTerm);
                        $query->where('name', 'LIKE', "%{$searchTerm}%");
                    });
                }

                if ($countryFlag) {
                    $countryModel = new Country();
                    $searchableCountryColumns = $countryModel->getSearchableColumns();
                    $query->orWhereHas('country', function ($query) use ($searchTerm, $filterBy, $searchableCountryColumns) {
                        if ($filterBy && in_array($filterBy, $searchableCountryColumns)) {
                            Log::info('country FilterBy =' . $filterBy);
                            $query->where($filterBy, 'LIKE', "%{$searchTerm}%");
                        } else {
                            Log::info('country Search on whole table =' . $searchTerm);
                            $query->where('name', 'LIKE', "%{$searchTerm}%")
                                ->orWhere('iso_code', 'LIKE', "%{$searchTerm}%");
                        }
                    });
                }

                if ($stateFlag) {
                    $stateModel = new State();
                    $searchableStateColumns = $stateModel->getSearchableColumns();
                    $query->orWhereHas('state', function ($query) use ($searchTerm, $filterBy, $searchableStateColumns) {
                        if ($filterBy && in_array($filterBy, $searchableStateColumns)) {
                            Log::info('state FilterBy =' . $filterBy);
                            $query->where($filterBy, 'LIKE', "%{$searchTerm}%");
                        } else {
                            Log::info('state Search on whole table =' . $searchTerm);
                            $query->where('name', 'LIKE', "%{$searchTerm}%");
                        }
                    });
                }
            }
        );
        if ($isExport && empty($limit)) {
            // Fetch all data without pagination
            Log::info("export is true and limit is empty");
            $startTime = microtime(true);
            $limit = $query->count();
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            Log::info("Customer Query Count = {$limit} && execution time: {$executionTime} seconds");
            return $query->orderBy($sortColumn, $sortDirection)->paginate($limit, ['*'], 'page', $page);
        } else {
            $limit = $limit ?: 10;
            return $query->orderBy($sortColumn, $sortDirection)->paginate($limit, ['*'], 'page', $page);
        }
    }
    public function createCustomer(Request $request)
    {
        // save customer data
        $customer = Customer::create([
            'company_name' => $request['company_name'],
            'customer_type' => $request['customer_type'],
            'address' => $request['address'],
            'city' => $request['city'],
            'state_id' => $request['state'],
            'country_id' => $request['country'],
            'zip_code' => $request['zip_code'],
            'company_tax_id' => $request['company_tax_id'],
            'payment_terms' => $request['payment_terms'],
            'credit_limit' => $request['credit_limit'],
            // 'contact_name' => $request['contact_name'],
            // 'phone' => $request['phone'],
            // 'email' => $request['email'],
        ]);
        Log::info("Custome save successfully...");

        // Create Warehouse Address
        // $this->storeWarehouseAddress($request, $customer);

        // Create Warehouse Address
        // $this->storeShippingAddress($request, $customer);

        // Create Warehouse Address
        $this->storeDeliveryAddress($request, $customer);

        // Create Warehouse Address
        $this->storeContactDetails($request, $customer);

        // Create Warehouse Address
        $this->storeFinanceDetails($request, $customer);

        return true;
    }


    public function updateCustomer(Request $request, $id, Customer $customer)
    {
        // Delete existing related records
        // $customer->warehouse()->delete();
        // $customer->shipping()->delete();
        $customer->delivery()->delete();
        $customer->contact()->delete();
        $customer->finance()->delete();

        $customer->update([
            'company_name' => $request['company_name'],
            'customer_type' => $request['customer_type'],
            'address' => $request['address'],
            'city' => $request['city'],
            'state_id' => $request['state'],
            'country_id' => $request['country'],
            'zip_code' => $request['zip_code'],
            'company_tax_id' => $request['company_tax_id'],
            'payment_terms' => $request['payment_terms'],
            'credit_limit' => $request['credit_limit'],
            // 'contact_name' => $request['contact_name'],
            // 'phone' => $request['phone'],
            // 'email' => $request['email'],
        ]);

        // Create Warehouse Address
        // $this->storeWarehouseAddress($request, $customer);

        // Create shipping Address
        // $this->storeShippingAddress($request, $customer);

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
        CustomerWarehouseAddress::create([
            'warehouse_name' => $request['warehouse_name'],
            'warehouse_address' => $request['warehouse_address'],
            'warehouse_city' => $request['warehouse_city'],
            'warehouse_state' => $request['warehouse_state'],
            'warehouse_country' => $request['warehouse_country'],
            'warehouse_zip_code' => $request['warehouse_zip_code'],
            'customer_id' => $customer->id
        ]);
    }

    public function storeShippingAddress(Request $request, $customer)
    {
        CustomerShippingAddress::create([
            'shipping_name' => $request['shipping_name'],
            'shipping_address' => $request['shipping_address'],
            'shipping_city' => $request['shipping_city'],
            'shipping_state' => $request['shipping_state'],
            'shipping_country' => $request['shipping_country'],
            'shipping_zip_code' => $request['shipping_zip_code'],
            'customer_id' => $customer->id
        ]);
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
