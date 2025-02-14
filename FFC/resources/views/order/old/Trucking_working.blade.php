@php
    // Check if orderDetails exists, and then access the related orderContainerDetails
    $orderDetails = $data['orderDetails'] ?? null;

    $bl = $orderDetails[0]['master_bl'] ?? '';
    $seal = $orderDetails[0]['seal'] ?? '';
    $lfd = $orderDetails[0]['last_free_day'] ?? '';
    $weight = $orderDetails[0]['weight'] ?? '';
    $pallets = $orderDetails[0]['pallets'] ?? '';
    // If orderDetails exists, get the orderContainerDetails for each orderDetails entry
    $orderContainerDetails = $orderDetails
        ? $orderDetails->map(function ($orderDetail) {
            return $orderDetail->orderContainerDetails;
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
          border-bottom: 2px solid #000;
        ">
            {{-- <p><pre>{{ json_encode($scac, JSON_PRETTY_PRINT) }}</pre></p> --}}
            <div class="header-left">
                <img src="https://via.placeholder.com/120" alt="FFC Logo" class="logo"
                    style="width: 120px; height: auto" />
                <h1 style="font-size: 24px; margin: 0">{{ $data['customer']['company_name'] }}</h1>
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
                    Ocean Freight | Customs Filing | trucker | Transload | Warehousing
                </p>
            </div>
        </header>

        <section class="order-info" style="margin-top: 20px">
            <p style="font-size: 14px; margin: 0">
                Please have your driver carry a printout of this DO and take the
                receiving warehouse personnel's name, signature, and delivery
                date/time as the POD.
            </p>
            <br /><br />
            <div
                style="
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
          ">
                <div style="flex: 1">
                    <p style="font-size: 14px; margin: 5px 0">
                        <strong>Trucker:</strong> Retrieving data. Wait a few seconds and
                        try to cut or copy again.
                    </p>
                    <p style="font-size: 14px; margin: 5px 0">
                        <strong>Email:</strong> Dispatch@firstfreightcarriers.com
                    </p>
                </div>
                <div style="text-align: right">
                    <p style="font-size: 14px; margin: 5px 0">
                        <strong>Date:</strong> {{ $data['received_date'] }}
                    </p>
                </div>
            </div>
        </section>

        <table class="details-table" style="width: 100%; margin-top: 20px; border-collapse: collapse">
            <thead>
                <tr style="background-color: rgb(221, 238, 240)">
                    <th style="border: 1px solid #ddd; padding: 8px; text-align: left">
                        CONTAINER #
                    </th>
                    <th style="border: 1px solid #ddd; padding: 8px; text-align: left">
                        BL
                    </th>
                    <th style="border: 1px solid #ddd; padding: 8px; text-align: left">
                        PO
                    </th>
                    <th style="border: 1px solid #ddd; padding: 8px; text-align: left">
                        CPO
                    </th>
                    <th style="border: 1px solid #ddd; padding: 8px; text-align: left">
                        Seal
                    </th>
                    <th style="border: 1px solid #ddd; padding: 8px; text-align: left">
                        LFD
                    </th>
                    <th style="border: 1px solid #ddd; padding: 8px; text-align: left">
                        Weight (lbs)
                    </th>
                    <th style="border: 1px solid #ddd; padding: 8px; text-align: left">
                        Size
                    </th>
                    <th style="border: 1px solid #ddd; padding: 8px; text-align: left">
                        # Of Pallets
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach ($orderContainerDetails[0] as $containerDetails)
                    <tr>
                        <td style="border: 1px solid #ddd; padding: 8px">{{ $containerDetails['container_no'] }}</td>
                        <td style="border: 1px solid #ddd; padding: 8px">{{ $bl }}</td>
                        <td style="border: 1px solid #ddd; padding: 8px">{{ $containerDetails['po'] }}</td>
                        <td style="border: 1px solid #ddd; padding: 8px">{{ $containerDetails['cpo'] }}</td>
                        <td style="border: 1px solid #ddd; padding: 8px">{{ $seal }}</td>
                        <td style="border: 1px solid #ddd; padding: 8px">{{ $lfd }}</td>
                        <td style="border: 1px solid #ddd; padding: 8px">{{ $weight }}</td>
                        <td style="border: 1px solid #ddd; padding: 8px">{{ $containerDetails['container_size'] }}</td>
                        <td style="border: 1px solid #ddd; padding: 8px">{{ $pallets }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <table class="details-table" style="width: 100%; margin-top: 20px; border-collapse: collapse">
            <thead>
                <tr>
                    <th
                        style="
                border: 1px solid #ddd;
                background-color: rgb(221, 238, 240);
                padding: 8px;
                text-align: center;
              ">
                        <strong>Deliver To:</strong>
                    </th>
                    <td colspan="3" style="border: 1px solid #ddd; padding: 8px; text-align: left">
                        <p style="margin: 0">{{ $data['address']['company_name'] }}</p>
                        {{-- <p style="margin: 0">1111 BROADWAY AVE</p>
                        <p style="margin: 0">BRASELTON GA 30517</p> --}}
                    </td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td
                        style="
                border: 1px solid #ddd;
                background-color: rgb(221, 238, 240);
                padding: 8px;
                text-align: center;
              ">
                        <strong>Delivery Appt Date:</strong>
                    </td>
                    <td style="border: 1px solid #ddd; padding: 8px; text-align: center">
                        {{ \Carbon\Carbon::parse($data['received_date'])->format('m-d') }}

                    </td>
                    <td
                        style="
                border: 1px solid #ddd;
                padding: 8px;
                font-weight: bold;
                text-align: center;
              ">
                        Time:
                    </td>
                    <td style="border: 1px solid #ddd; padding: 8px; text-align: center">
                        07:00
                    </td>
                </tr>
            </tbody>
        </table>

        <section class="contact" style="margin-top: 20px; background-color: #ffffff; border-radius: 5px">
            <p class="highlight"
                style="
            background: black;
            color: white;
            padding: 10px;
            text-align: center;
          ">
                PLEASE CONTACT FFC TO SCHEDULE DELIVERY
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
                            <strong>Freight Location:</strong>
                        </th>
                        <td style="border: 1px solid #ddd; padding: 8px">
                            {{ $orderDetails[0]['freight_location'] }}
                        </td>
                        <th
                            style="
                  border: 1px solid #ddd;
                  padding: 8px;
                  text-align: left;
                  background-color: rgb(221, 238, 240);
                ">
                            <strong>Firms Code:</strong>
                        </th>
                        <td style="border: 1px solid #ddd; padding: 8px">
                            {{ $orderDetails[0]['firm_code'] }}
                        </td>
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
                            <strong>Vessel/Voyage:</strong>
                        </th>
                        <td style="border: 1px solid #ddd; padding: 8px">
                            {{ $orderDetails[0]['vessel_voyage'] }}
                        </td>
                        <th
                            style="
                  border: 1px solid #ddd;
                  padding: 8px;
                  text-align: left;
                  background-color: rgb(221, 238, 240);
                ">
                            <strong>Commodity:</strong>
                        </th>
                        <td style="border: 1px solid #ddd; padding: 8px">
                            {{ $orderDetails[0]['commodity'] }}
                        </td>
                    </tr>
                    <tr>
                        <th
                            style="
                  border: 1px solid #ddd;
                  padding: 8px;
                  text-align: left;
                  background-color: rgb(221, 238, 240);
                ">
                            <strong>ETA:</strong>
                        </th>
                        <td style="border: 1px solid #ddd; padding: 8px">
                            {{ $orderDetails[0]['eta'] }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </section>

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
                        <p style="margin: 0">firstfreightcarriers.com</p>
                        <p style="margin: 0">7810 N. Florida Avenue</p>
                        <p style="margin: 0">Tampa, FL 33604</p>
                    </td>
                </tr>
            </thead>
        </table>

        <table class="details-table"
            style="
          width: 100%;
          margin-top: 20px;
          border-collapse: collapse;
          margin-bottom: 2rem;
        ">
            <thead>
                <tr>
                    <th rowspan="2"
                        style="
                border: 1px solid #ddd;
                padding: 8px;
                text-align: center;
                font-weight: bold;
                background-color: rgb(221, 238, 240);
              ">
                        Email:
                    </th>
                    <td
                        style="
                border: 1px solid #ddd;
                padding: 8px;
                text-align: center;
                font-weight: bold;
                background-color: rgb(221, 238, 240);
              ">
                        Operations:
                    </td>
                    <td style="border: 1px solid #ddd; padding: 8px; text-align: left">
                        dispatch@firstfreightcarriers.com
                    </td>
                </tr>

                <tr>
                    <td
                        style="
                border: 1px solid #ddd;
                padding: 8px;
                text-align: center;
                font-weight: bold;
                background-color: rgb(221, 238, 240);
              ">
                        Accounts:
                    </td>
                    <td style="border: 1px solid #ddd; padding: 8px; text-align: left">
                        accountant@firstfreightcarriers.com
                    </td>
                </tr>
            </thead>
        </table>

        <div style="border: 1px solid #ddd; padding: 1rem; font-size: 14px">
            <div
                style="
            text-align: center;
            font-weight: bold;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #ccc;
            background-color: rgb(221, 238, 240);
          ">
                RECEIVING WAREHOUSE CONFIRMATION
            </div>
            <p style="margin: 1rem 0; font-weight: bold">
                Cargo is received in good condition.
            </p>
            <div
                style="
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
          ">
                <div>
                    <strong>Appointment time:</strong>
                    <span
                        style="
                border-bottom: 1px solid #000;
                display: inline-block;
                width: 150px;
              ">&nbsp;</span>
                </div>
                <div>
                    <strong>Arrived at:</strong>
                    <span
                        style="
                border-bottom: 1px solid #000;
                display: inline-block;
                width: 150px;
              ">&nbsp;</span>
                </div>
                <div>
                    <strong>Departed:</strong>
                    <span
                        style="
                border-bottom: 1px solid #000;
                display: inline-block;
                width: 150px;
              ">&nbsp;</span>
                </div>
            </div>
            <div style="display: flex; justify-content: space-between">
                <div style="text-align: center">
                    <span
                        style="
                border-bottom: 1px solid #000;
                display: inline-block;
                width: 150px;
              ">&nbsp;</span><br />
                    <strong>Signature</strong>
                </div>
                <div style="text-align: center">
                    <span
                        style="
                border-bottom: 1px solid #000;
                display: inline-block;
                width: 150px;
              ">&nbsp;</span><br />
                    <strong>Name</strong>
                </div>
                <div style="text-align: center">
                    <span
                        style="
                border-bottom: 1px solid #000;
                display: inline-block;
                width: 150px;
              ">&nbsp;</span><br />
                    <strong>Date</strong>
                </div>
            </div>
        </div>

        <footer
            style="
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
