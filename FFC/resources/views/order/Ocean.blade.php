@php
$image = base64_encode(file_get_contents(public_path('images/ffc_logo.jpeg')));
// Check if orderDetails exists, and then access the related orderContainerDetails
$orderDetails = $data['orderDetails'] ?? null;

$shipper = $orderDetails[0]['shipper'] ?? null;
$shipper_address = $orderDetails[0]['shipper_address'] ?? null;
$consignee = $orderDetails[0]['consignee'] ?? null;
$consignee_address = $orderDetails[0]['consignee_address'] ?? null;
$buyer = $orderDetails[0]['buyer'] ?? null;
$buyer_address = $orderDetails[0]['buyer_address'] ?? null;
$notify_party = $orderDetails[0]['notify_party'] ?? null;
$hts_code = $orderDetails[0]['hts_code'] ?? null;
$commodity = $orderDetails[0]['commodity'] ?? null;
$weight = $orderDetails[0]['weight'] ?? null;
$ssl = $orderDetails[0]['streamship_line'] ?? null;
$etd = $orderDetails[0]['etd'] ?? null;
$eta = $orderDetails[0]['eta'] ?? null;
$si_cut_off = $orderDetails[0]['si_cut_off'] ?? null;
$vgm_cut_off = $orderDetails[0]['vgm_cut_off'] ?? null;
$all_inclusive_rate = $orderDetails[0]['all_inclusive_rate'] ?? null;
$freight_prepaid = $orderDetails[0]['freight_prepaid'] ?? null;
$received_date = \Carbon\Carbon::parse($data['received_date'])->format('m-d-Y');
// If orderDetails exists, get the orderContainerDetails for each orderDetails entry
// $orderContainerDetails1 = $orderDetails
// ? $orderDetails->map(function ($orderDetail) {
// return $orderDetail->orderContainerDetails;
// })
// : null;

$orderContainerDetails = $orderDetails
    ? $orderDetails->map(function ($orderDetail) {
        // Get the first element of the orderContainerDetails array
        return isset($orderDetail->orderContainerDetails[0]) ? $orderDetail->orderContainerDetails[0] : null;
    })
    : null;

foreach ($orderContainerDetails as $containerDetails) {
    $containerNo = $containerDetails['container_no'] ?? null;
    $containerSize = $containerDetails['container_size'] ?? null;
    $po = $containerDetails['po'] ?? null;
}

$orderDeliveries = $orderDetails
    ? $orderDetails->map(function ($orderDetail) {
        return $orderDetail->deliveries;
    })
    : null;

foreach ($orderDeliveries[0] as $delivery) {
    $scac = $delivery['vendor']['scac_number'] ?? null;
    $mc = $delivery['vendor']['mc_number'] ?? null;
    $usdot = $delivery['vendor']['us_dot_number'] ?? null;
    $transit_time = $delivery['transit_time'] ?? null;
    $empty_pick_up_cutoff_date = $delivery['empty_pick_up_cutoff_date'] ?? null;
    $transhipment_port = $delivery['transhipmentPort']['name'] ?? null;
    $ignate_cutoff_date = $delivery['ignate_cutoff_date'] ?? null;
}
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Delivery Order</title>
</head>
<style>
    @page {
        size: A4;
        margin: 4mm;
    }

    body {
        margin: 0;
        padding: 0;
        font-size: 10px;
        /* Adjust for readability */
    }
</style>

<body
    style="
      font-family: Verdana, Geneva, Tahoma, sans-serif;
      margin: 0;
      padding: 0;
      background-color: #f9f9f9;
    ">
    <div class="delivery-order" style="max-width: 100%; margin: 0 auto; padding: 10px">
        <header style="border-bottom: 2px solid #000; padding: 10px;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <!-- Left Section -->
                    <td style="width: 85px; vertical-align: top; padding-right: 10px;">
                        <div style="width: 100px; height: 90px;">
                            <img src="data:image/jpeg;base64,{{ $image }}" alt="FFC Logo" class="logo"
                                style="width: 85px; height: 61px; margin-bottom: 10px;" />
                        </div>
                        <h1 style="font-size: 14px; margin: 0;">Delivery Order</h1>
                    </td>

                    <!-- Right Section -->
                    <td style="width: 70%; vertical-align: top; text-align: right; padding-left: 10px;">
                        <div style="font-size: 12px; line-height: 1.4;">
                            <p style="margin: 0 0 8px 0;"><strong>SCAC:</strong> {{ $scac }}</p>
                            <p style="margin: 0 0 8px 0;"><strong>USDOT #:</strong> {{ $usdot }}</p>
                            <p style="margin: 0 0 8px 0;"><strong>MC #:</strong> {{ $mc }}</p>
                            <p style="margin: 0 0 8px 0;">
                                <a href="https://www.firstfreightcarriers.com">www.firstfreightcarriers.com</a>
                            </p>
                            <p style="margin: 5px 0; font-weight: 700; line-height: 1.6;">
                                Ocean Freight | Customs Filing | Trucking | Transload | Warehousing
                            </p>
                        </div>
                    </td>
                </tr>
            </table>
        </header>

        <section class="order-info" style="margin-top: 20px; border-bottom: 2px solid #7c7979">
            <table style="width: 100%; border-collapse: collapse; margin: 0; padding: 0;">
                <tr>
                    <!-- Forwarder Section -->
                    <td style="flex: 1; vertical-align: middle;">
                        <p style="font-size: 12px; margin: 5px 0;">
                            <strong>Forwarder:</strong> City Ocean Logistics
                        </p>
                    </td>
                    <!-- Date Section -->
                    <td style="text-align: right; vertical-align: middle;">
                        <p style="text-align: end;">
                            <span style="padding: 10px; background-color: #1976D20F; "><strong>Date:</strong>
                                {{ $received_date }}
                            </span>
                        </p>
                    </td>
                </tr>
            </table>

            <br />
        </section>

        <section class="contact" style="background-color: #ffffff; border-radius: 5px">
            <p class="highlight" style="color: rgb(10, 10, 10); padding: 10px; text-align: center">
                Email: Dispatch@firstfreightcarriers.com
            </p>
            <table class="details-table" style="width: 100%; margin-top: 10px; border-collapse: collapse">
                <thead>
                    <tr>
                        <th
                            style="
                  border: 1px solid #ddd;
                  padding: 8px;
                  text-align: left;
                  background-color: #1976D20F;
                ">
                            <strong>Lane</strong>
                        </th>
                        <td colspan="3" style="border: 1px solid #ddd; padding: 8px">
                            Kaohsiung, Taiwan to Savannah
                        </td>
                        <th
                            style="
                  border: 1px solid #ddd;
                  padding: 8px;
                  text-align: left;
                  background-color: #1976D20F;
                ">
                            <strong>Container</strong>
                        </th>
                        <td style="border: 1px solid #ddd; padding: 8px">{{ $containerNo }}</td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th
                            style="
                  border: 1px solid #ddd;
                  padding: 8px;
                  text-align: left;
                  background-color: #1976D20F;
                ">
                            <strong>SSL/Vessel</strong>
                        </th>
                        <td style="border: 1px solid #ddd; padding: 8px">
                            {{ $ssl }}
                        </td>
                        <th
                            style="
                  border: 1px solid #ddd;
                  padding: 8px;
                  text-align: left;
                  background-color: #1976D20F;
                ">
                            <strong>ETD</strong>
                        </th>
                        <td style="border: 1px solid #ddd; padding: 8px">{{ $etd }}</td>
                        <th
                            style="
                  border: 1px solid #ddd;
                  padding: 8px;
                  text-align: left;
                  background-color: #1976D20F;
                ">
                            ETA
                        </th>
                        <td style="border: 1px solid #ddd; padding: 8px">{{ $eta }}</td>
                    </tr>
                    <tr>
                        <th
                            style="
                  border: 1px solid #ddd;
                  padding: 8px;
                  text-align: left;
                  background-color: #1976D20F;
                ">
                            <strong>Transit Time</strong>
                        </th>
                        <td style="border: 1px solid #ddd; padding: 8px">{{ $transit_time }}</td>
                        <th
                            style="
                  border: 1px solid #ddd;
                  padding: 8px;
                  text-align: left;
                  background-color: #1976D20F;
                ">
                            <strong>Empty pick up cutoff date</strong>
                        </th>
                        <td style="border: 1px solid #ddd; padding: 8px">{{ $empty_pick_up_cutoff_date }}</td>
                        <th
                            style="
                  border: 1px solid #ddd;
                  padding: 8px;
                  text-align: left;
                  background-color: #1976D20F;
                ">
                            Ingate cutoff date
                        </th>
                        <td style="border: 1px solid #ddd; padding: 8px">{{ $ignate_cutoff_date }}</td>
                    </tr>
                    <tr>
                        <th
                            style="
                  border: 1px solid #ddd;
                  padding: 8px;
                  text-align: left;
                  background-color: #1976D20F;
                ">
                            <strong>SI Cutoff</strong>
                        </th>
                        <td style="border: 1px solid #ddd; padding: 8px">{{ $si_cut_off }}</td>
                        <th
                            style="
                  border: 1px solid #ddd;
                  padding: 8px;
                  text-align: left;
                  background-color: #1976D20F;
                ">
                            <strong>Transhipment Port</strong>
                        </th>
                        <td style="border: 1px solid #ddd; padding: 8px">{{ $transhipment_port }}</td>
                        <th
                            style="
                  border: 1px solid #ddd;
                  padding: 8px;
                  text-align: left;
                  background-color: #1976D20F;
                ">
                            VGM cutoff date
                        </th>
                        <td style="border: 1px solid #ddd; padding: 8px">{{ $vgm_cut_off }}</td>
                    </tr>
                    <tr>
                        <th
                            style="
                  border: 1px solid #ddd;
                  padding: 8px;
                  text-align: left;
                  background-color: #1976D20F;
                ">
                            <strong>All Inclusive Rate</strong>
                        </th>
                        <td style="border: 1px solid #ddd; padding: 8px">${{ $all_inclusive_rate }}</td>
                        <th
                            style="
                  border: 1px solid #ddd;
                  padding: 8px;
                  text-align: left;
                  background-color: #1976D20F;
                ">
                            <strong>Freight</strong>
                        </th>
                        <td style="border: 1px solid #ddd; padding: 8px">{{ $freight_prepaid }}</td>
                        <th colspan="2"
                            style="
                  border: 1px solid #ddd;
                  padding: 8px;
                  text-align: left;
                  background-color: #1976D20F;
                ">
                            BL to be submitted at origin
                        </th>
                    </tr>
                </tbody>
            </table>
        </section>

        <div
            style="
          display: flex;
          justify-content: center;
          margin-top: 2rem;
          align-items: flex-start;
        ">
            <!-- Left Table -->
            <table border="1" style="width: 100%; border-collapse: collapse; margin: 0">
                <tr>
                    <th rowspan="2"
                        style="
                width: 30%;
                padding: 8px;
                text-align: left;
                background-color: #1976D20F;
                border: 1px solid #ddd;
              ">
                        Shipper
                    </th>
                    <td
                        style="
                width: 70%;
                padding: 8px;
                text-align: left;
                border: 1px solid #ddd;
              ">
                        {{ $shipper }}
                    </td>
                    <th rowspan="2"
                        style="
                width: 30%;
                padding: 8px;
                text-align: left;
                background-color: #1976D20F;
                border: 1px solid #ddd;
              ">
                        Consignee
                    </th>
                    <td
                        style="
                width: 70%;
                padding: 8px;
                text-align: left;
                border: 1px solid #ddd;
              ">
                        {{ $consignee }}
                    </td>
                </tr>
                <tr>
                    <td
                        style="
                width: 80%;
                padding: 8px;
                text-align: left;
                border: 1px solid #ddd;
              ">
                        {{ $shipper_address }}
                    </td>
                    <td
                        style="
                width: 80%;
                padding: 8px;
                text-align: left;
                border: 1px solid #ddd;
              ">
                        {{ $consignee_address }}
                    </td>
                </tr>
                {{-- <tr>
                    <td
                        style="
                width: 80%;
                padding: 8px;
                text-align: left;
                border: 1px solid #ddd;
              ">
                        TAIPEI
                    </td>
                </tr>
                <tr>
                    <td
                        style="
                width: 80%;
                padding: 8px;
                text-align: left;
                border: 1px solid #ddd;
              ">
                        Taiwan
                    </td>
                </tr> --}}
                <tr>
                    <th rowspan="2"
                        style="
                width: 20%;
                padding: 8px;
                text-align: left;
                background-color: #1976D20F;
                border: 1px solid #ddd;
              ">
                        Buyer
                    </th>
                    <td
                        style="
                width: 80%;
                padding: 8px;
                text-align: left;
                border: 1px solid #ddd;
              ">
                        {{ $buyer }}
                    </td>
                    <th rowspan="4"
                        style="
                width: 20%;
                padding: 8px;
                text-align: left;
                background-color: #1976D20F;
                border: 1px solid #ddd;
              ">
                        Notify Party
                    </th>
                    <td
                        style="
                width: 80%;
                padding: 8px;
                text-align: left;
                border: 1px solid #ddd;
              ">
                        First Freight Carriers
                    </td>
                </tr>
                <tr>
                    <td style="width: 80%;padding: 8px;text-align: left;border: 1px solid #ddd;">
                        {{ $buyer_address }}
                    </td>
                    <td style="width: 80%;padding: 8px;text-align: left;border: 1px solid #ddd;">
                        42619 Windflower Dr, Ashburn, VA 20148
                    </td>
                </tr>

                {{-- <tr>
                    <td
                        style="
                width: 80%;
                padding: 8px;
                text-align: left;
                border: 1px solid #ddd;
              ">
                        Tampa, FL 33619
                    </td>
                </tr>
                <tr>
                    <td
                        style="
                width: 80%;
                padding: 8px;
                text-align: left;
                border: 1px solid #ddd;
              ">
                        USA
                    </td>
                </tr> --}}
                <tr>
                    <th
                        style="width: 20%;padding: 8px;text-align: left;background-color: #1976D20F;border: 1px solid #ddd;">
                        HTS Code
                    </th>
                    <td style="width: 80%; padding: 8px; text-align: left; border: 1px solid #ddd; ">
                        {{ $hts_code }}
                    </td>
                    <td style="width: 80%;padding: 8px;text-align: left;border: 1px solid #ddd;">
                        dispatch@firstfreightcarriers.com
                    </td>
                </tr>
                <tr>
                    <th
                        style="
                width: 20%;
                padding: 8px;
                text-align: left;
                background-color: #1976D20F;
                border: 1px solid #ddd;
              ">
                        Commodity
                    </th>
                    <td
                        style="
                width: 80%;
                padding: 8px;
                text-align: left;
                border: 1px solid #ddd;
              ">
                        {{ $commodity }}
                    </td>
                    <td style="width: 80%;padding: 8px;text-align: left;border: 1px solid #ddd;">
                        dispatch@firstfreightcarriers.com
                    </td>
                </tr>
                <tr>
                    <th
                        style="
                width: 20%;
                padding: 8px;
                text-align: left;
                background-color: #1976D20F;
                border: 1px solid #ddd;
              ">
                        Container Size
                    </th>
                    <td
                        style="
                width: 80%;
                padding: 8px;
                text-align: left;
                border: 1px solid #ddd;
              ">
                        {{ $containerSize }}
                    </td>
                    <th
                        style="
                width: 30%;
                padding: 8px;
                text-align: left;
                background-color: #1976D20F;
                border: 1px solid #ddd;
              ">
                        PO #/Proforma #
                    </th>
                    <td style="width: 70%; padding: 8px; text-align: left; border: 1px solid #ddd; ">
                        242322
                    </td>
                </tr>
                <tr>
                    <th
                        style="width: 20%; padding: 8px; text-align: left; background-color: #1976D20F; border: 1px solid #ddd;">
                        Weight
                    </th>
                    <th style="width: 80%; padding: 8px; text-align: left;  border: 1px solid #ddd;">
                        {{ $weight }}
                    </th>
                </tr>
            </table>

            <!-- Right Table -->
            {{-- <table border="1" style="width: 50%; border-collapse: collapse; margin: 0">
                <tr>
                    <th rowspan="2"
                        style=" width: 30%;padding: 8px;text-align: left;background-color: #1976D20F;border: 1px solid #ddd;">
                        Consignee
                    </th>
                    <td style=" width: 70%; padding: 8px; text-align: left; border: 1px solid #ddd;">
                        {{ $consignee }}
                    </td>
                </tr>
                <tr>
                    <td style=" width: 80%; padding: 8px; text-align: left; border: 1px solid #ddd; ">
                        {{ $consignee_address }}
                    </td>
                </tr>
                <tr>
                    <td style="  width: 80%; padding: 8px; text-align: left; border: 1px solid #ddd; ">
                        Tampa, FL 33619
                    </td>
                </tr>
                <tr>
                    <td style=" width: 80%; padding: 8px; text-align: left; border: 1px solid #ddd; ">
                        USA
                    </td>
                </tr>
                <tr>
                    <th rowspan="4"
                        style="width: 20%; padding: 8px; text-align: left; background-color: #1976D20F; border: 1px solid #ddd; ">
                        Notify Party
                    </th>
                    <td style=" width: 80%; padding: 8px; text-align: left; border: 1px solid #ddd; ">
                        First Freight Carriers
                    </td>
                </tr>
                <tr>
                    <td style="width: 80%; padding: 8px; text-align: left; border: 1px solid #ddd; ">
                        42619 Windflower Dr, Ashburn, VA 20148
                    </td>
                </tr>
                <tr>
                    <td style="width: 80%;padding: 8px;text-align: left;border: 1px solid #ddd;">
                        dispatch@firstfreightcarriers.com
                    </td>
                </tr>
                <tr>
                    <td style="width: 80%; padding: 8px; text-align: left; border: 1px solid #ddd;">
                        dispatch@firstfreightcarriers.com
                    </td>
                </tr>
                <tr>
                    <th
                        style="width: 30%; padding: 8px; text-align: left; background-color: #1976D20F; border: 1px solid #ddd; ">
                        PO #/Proforma #
                    </th>
                    <td style="width: 70%; padding: 8px; text-align: left; border: 1px solid #ddd; ">
                        242322
                    </td>
                </tr>
            </table> --}}
        </div>

        <table class="details-table" style="width: 100%; margin-top: 20px; border-collapse: collapse">
            <thead>
                <tr>
                    <th
                        style="
                width: 15rem;
                border: 1px solid #ddd;
                padding: 8px;
                text-align: center;
                background-color: #1976D20F;
              ">
                        <strong>Bill To:</strong>
                    </th>
                    <td colspan="2" style="border: 1px solid #ddd; padding: 8px; text-align: left">
                        <p style="margin: 0">First Freight Carriers, LLC</p>
                        <p style="margin: 0">42619 Windflower Drive</p>
                        <p style="margin: 0">Ashburn, VA 20148, USA</p>
                    </td>
                </tr>
            </thead>
        </table>

        <table class="details-table" style="width: 100%; margin-top: 20px; border-collapse: collapse">
            <thead>
                <tr>
                    <th
                        style="
                width: 15rem;
                border: 1px solid #ddd;
                padding: 8px;
                text-align: center;
                background-color: #1976D20F;
              ">
                        <strong>Email Invoice to</strong>
                    </th>
                    <td colspan="2" style="border: 1px solid #ddd; padding: 8px; text-align: left">
                        <p style="margin: 0">
                            Accountant@firstfreightcarriers.com and
                            Nathan@firstfreightcarriers.com
                        </p>
                        <p style="margin: 0">
                            Please include container number with your invoice
                        </p>
                    </td>
                </tr>
            </thead>
        </table>

        {{-- <footer style="margin-top: 3rem;display: flex;justify-content: space-between;align-items: flex-start;">
            <div style="flex: 1">
                <p style="font-size: 12px; margin: 5px 0">
                    42619 Windflower Drive,Ashbum,VA 20148
                </p>
            </div>
            <div style="text-align: right">
                <p style="font-size: 12px; margin: 5px 0">
                    p.703.738.2834; F:703.842.8668
                </p>
            </div>
        </footer> --}}
        <div style="position: absolute; bottom: 0; width: 100%; text-align: center; font-size: 12px;">
            <table style="width: 100%; border-collapse: collapse; ">
                <tr>
                    <td style="flex: 1; vertical-align: middle;">
                        <p>42619 Windflower Drive,Ashbum,VA 20148</p>
                    </td>
                    <td style="text-align: right; vertical-align: middle;">
                        <p style="margin-right: 6%">p.703.738.2834; F:703.842.8668</p>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>

</html>