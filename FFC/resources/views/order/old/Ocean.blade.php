@php
    // Check if orderDetails exists, and then access the related orderContainerDetails
    $orderDetails = $data['orderDetails'] ?? null;

    $shipper = $orderDetails[0]['shipper'] ?? '';
    $shipper_address = $orderDetails[0]['shipper_address'] ?? '';
    $consignee = $orderDetails[0]['consignee'] ?? '';
    $consignee_address = $orderDetails[0]['consignee_address'] ?? '';
    $buyer = $orderDetails[0]['buyer'] ?? '';
    $buyer_address = $orderDetails[0]['buyer_address'] ?? '';
    $notify_party = $orderDetails[0]['notify_party'] ?? '';
    $hts_code = $orderDetails[0]['hts_code'] ?? '';
    $commodity = $orderDetails[0]['commodity'] ?? '';
    $weight = $orderDetails[0]['weight'] ?? '';
    $ssl = $orderDetails[0]['streamship_line'] ?? '';
    $etd = $orderDetails[0]['etd'] ?? '';
    $eta = $orderDetails[0]['eta'] ?? '';
    $si_cut_off = $orderDetails[0]['si_cut_off'] ?? '';
    $vgm_cut_off = $orderDetails[0]['vgm_cut_off'] ?? '';
    $all_inclusive_rate = $orderDetails[0]['all_inclusive_rate'] ?? '';
    $freight_prepaid = $orderDetails[0]['freight_prepaid'] ?? '';
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

    $orderDeliveries = $orderDetails
        ? $orderDetails->map(function ($orderDetail) {
            return $orderDetail->deliveries;
        })
        : null;

    foreach ($orderDeliveries[0] as $delivery) {
        $scac = $delivery['vendor']['scac_number'];
        $mc = $delivery['vendor']['mc_number'];
        $usdot = $delivery['vendor']['us_dot_number'];
        $transit_time = $delivery['transit_time'];
        $empty_pick_up_cutoff_date = $delivery['empty_pick_up_cutoff_date'];
        $transhipment_port = $delivery['transhipmentPort']['name'];
        $ignate_cutoff_date = '?';
    }
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Delivery Order</title>
</head>

<body
    style="
      font-family: Verdana, Geneva, Tahoma, sans-serif;
      margin: 0;
      padding: 0;
      background-color: #f9f9f9;
    ">
    <div class="delivery-order" style="max-width: 80rem; margin: 0 auto; padding: 20px">
        <header
            style="
          display: flex;
          justify-content: space-between;
          align-items: center;
          color: rgb(5, 5, 5);
          padding: 5px;
          border-bottom: 2px solid #7c7979;
        ">
            <div class="header-left">
                <img src="https://via.placeholder.com/120" alt="FFC Logo" class="logo"
                    style="width: 120px; height: auto" />
                <h1 style="font-size: 24px; margin: 0">Delivery Order</h1>
            </div>
            <div class="header-right">
                <p style="text-align: right; font-size: 14px">
                    <strong>SCAC:</strong> {{ $scac }}
                </p>
                <p style="text-align: right; font-size: 14px">
                    <strong>USDOT #:</strong> {{ $usdot }}
                </p>
                <p style="text-align: right; font-size: 14px">
                    <strong>MC #:</strong> {{ $mc }}
                </p>
                <p style="text-align: right; font-size: 14px">
                    <a href="https://www.firstfreightcarriers.com">www.firstfreightcarriers.com</a>
                </p>
                <p style="font-size: 14px; font-weight: bold">
                    Ocean Freight | Customs Filing | Trucking | Transload | Warehousing
                </p>
            </div>
        </header>

        <section class="order-info" style="margin-top: 20px; border-bottom: 2px solid #7c7979">
            <div
                style="
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
          ">
                <div style="flex: 1">
                    <p style="font-size: 14px; margin: 5px 0">
                        <strong>Forwarder :</strong> City Ocean Logistics
                    </p>
                </div>
                <div style="text-align: right">
                    <p
                        style="
                border: 1px solid #ddd;
                /* padding: 8px; */
                text-align: left;
                background-color: rgb(221, 238, 240);
              ">
                        <strong>Date:</strong> {{ $received_date }}
                    </p>
                </div>
            </div>
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
                  background-color: rgb(221, 238, 240);
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
                  background-color: rgb(221, 238, 240);
                ">
                            <strong>Container</strong>
                        </th>
                        <td style="border: 1px solid #ddd; padding: 8px">1 x 40HQ</td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th
                            style="
                  border: 1px solid #ddd;
                  padding: 8px;
                  text-align: left;
                  background-color: rgb(221, 238, 240);
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
                  background-color: rgb(221, 238, 240);
                ">
                            <strong>ETD</strong>
                        </th>
                        <td style="border: 1px solid #ddd; padding: 8px">{{ $etd }}</td>
                        <th
                            style="
                  border: 1px solid #ddd;
                  padding: 8px;
                  text-align: left;
                  background-color: rgb(221, 238, 240);
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
                  background-color: rgb(221, 238, 240);
                ">
                            <strong>Transit Time</strong>
                        </th>
                        <td style="border: 1px solid #ddd; padding: 8px">{{ $transit_time }}</td>
                        <th
                            style="
                  border: 1px solid #ddd;
                  padding: 8px;
                  text-align: left;
                  background-color: rgb(221, 238, 240);
                ">
                            <strong>Empty pick up cutoff date</strong>
                        </th>
                        <td style="border: 1px solid #ddd; padding: 8px">{{ $empty_pick_up_cutoff_date }}</td>
                        <th
                            style="
                  border: 1px solid #ddd;
                  padding: 8px;
                  text-align: left;
                  background-color: rgb(221, 238, 240);
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
                  background-color: rgb(221, 238, 240);
                ">
                            <strong>SI Cutoff</strong>
                        </th>
                        <td style="border: 1px solid #ddd; padding: 8px">{{ $si_cut_off }}</td>
                        <th
                            style="
                  border: 1px solid #ddd;
                  padding: 8px;
                  text-align: left;
                  background-color: rgb(221, 238, 240);
                ">
                            <strong>Transhipment Port</strong>
                        </th>
                        <td style="border: 1px solid #ddd; padding: 8px">{{ $transhipment_port }}</td>
                        <th
                            style="
                  border: 1px solid #ddd;
                  padding: 8px;
                  text-align: left;
                  background-color: rgb(221, 238, 240);
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
                  background-color: rgb(221, 238, 240);
                ">
                            <strong>All Inclusive Rate</strong>
                        </th>
                        <td style="border: 1px solid #ddd; padding: 8px">${{ $all_inclusive_rate }}</td>
                        <th
                            style="
                  border: 1px solid #ddd;
                  padding: 8px;
                  text-align: left;
                  background-color: rgb(221, 238, 240);
                ">
                            <strong>Freight</strong>
                        </th>
                        <td style="border: 1px solid #ddd; padding: 8px">{{ $freight_prepaid }}</td>
                        <th colspan="2"
                            style="
                  border: 1px solid #ddd;
                  padding: 8px;
                  text-align: left;
                  background-color: rgb(221, 238, 240);
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
            <table border="1" style="width: 50%; border-collapse: collapse; margin: 0">
                <tr>
                    <th rowspan="4"
                        style="
                width: 30%;
                padding: 8px;
                text-align: left;
                background-color: rgb(221, 238, 240);
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
                </tr>
                <tr>
                    <td
                        style="
                width: 80%;
                padding: 8px;
                text-align: left;
                border: 1px solid #ddd;
              ">
                        ?
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
                        ?
                    </td>
                </tr>
                <tr>
                    <th rowspan="4"
                        style="
                width: 20%;
                padding: 8px;
                text-align: left;
                background-color: rgb(221, 238, 240);
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
                </tr>
                <tr>
                    <td
                        style="
                width: 80%;
                padding: 8px;
                text-align: left;
                border: 1px solid #ddd;
              ">
                        {{ $buyer_address }}
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
                        ?
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
                        ?
                    </td>
                </tr>
                <tr>
                    <th
                        style="
                width: 20%;
                padding: 8px;
                text-align: left;
                background-color: rgb(221, 238, 240);
                border: 1px solid #ddd;
              ">
                        HTS Code
                    </th>
                    <td
                        style="
                width: 80%;
                padding: 8px;
                text-align: left;
                border: 1px solid #ddd;
              ">
                        {{ $hts_code }}
                    </td>
                </tr>
                <tr>
                    <th
                        style="
                width: 20%;
                padding: 8px;
                text-align: left;
                background-color: rgb(221, 238, 240);
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
                </tr>
                <tr>
                    <th
                        style="
                width: 20%;
                padding: 8px;
                text-align: left;
                background-color: rgb(221, 238, 240);
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
                        40HQ
                    </td>
                </tr>
                <tr>
                    <th
                        style="
                width: 20%;
                padding: 8px;
                text-align: left;
                background-color: rgb(221, 238, 240);
                border: 1px solid #ddd;
              ">
                        Weight
                    </th>
                    <td
                        style="
                width: 80%;
                padding: 8px;
                text-align: left;
                border: 1px solid #ddd;
              ">
                        {{ $weight }}
                    </td>
                </tr>
            </table>

            <!-- Right Table -->
            <table border="1" style="width: 50%; border-collapse: collapse; margin: 0">
                <tr>
                    <th rowspan="4"
                        style="
                width: 30%;
                padding: 8px;
                text-align: left;
                background-color: rgb(221, 238, 240);
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
                        {{ $consignee_address }}
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
                        ?
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
                        ?
                    </td>
                </tr>
                <tr>
                    <th rowspan="4"
                        style="
                width: 20%;
                padding: 8px;
                text-align: left;
                background-color: rgb(221, 238, 240);
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
                    <td
                        style="
                width: 80%;
                padding: 8px;
                text-align: left;
                border: 1px solid #ddd;
              ">
                        42619 Windflower Dr, Ashburn, VA 20148
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
                        dispatch@firstfreightcarriers.com
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
                        dispatch@firstfreightcarriers.com
                    </td>
                </tr>
                <tr>
                    <th
                        style="
                width: 30%;
                padding: 8px;
                text-align: left;
                background-color: rgb(221, 238, 240);
                border: 1px solid #ddd;
              ">
                        PO #/Proforma #
                    </th>
                    <td
                        style="
                width: 70%;
                padding: 8px;
                text-align: left;
                border: 1px solid #ddd;
              ">
                        242322
                    </td>
                </tr>
            </table>
        </div>

        <table class="details-table" style="width: 100%; margin-top: 20px; border-collapse: collapse">
            <thead>
                <tr>
                    <th
                        style="
                border: 1px solid #ddd;
                padding: 8px;
                text-align: center;
                background-color: rgb(221, 238, 240);
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
                border: 1px solid #ddd;
                padding: 8px;
                text-align: center;
                background-color: rgb(221, 238, 240);
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

        <footer
            style="
          margin-top: 3rem;
          display: flex;
          justify-content: space-between;
          align-items: flex-start;
        ">
            <div style="flex: 1">
                <p style="font-size: 14px; margin: 5px 0">
                    42619 Windflower Drive,Ashbum,VA 20148
                </p>
            </div>
            <div style="text-align: right">
                <p style="font-size: 14px; margin: 5px 0">
                    p.703.738.2834; F:703.842.8668
                </p>
            </div>
        </footer>
    </div>
</body>

</html>
