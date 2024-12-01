@php
    // Check if orderDetails exists, and then access the related orderContainerDetails
    $orderDetails = $data['orderDetails'] ?? null;

    $bl = $orderDetails[0]['master_bl'] ?? '';
    $seal = $orderDetails[0]['seal'] ?? '';
    $lfd = $orderDetails[0]['last_free_day'] ?? '';
    $weight = $orderDetails[0]['weight'] ?? '';
    $pallets = $orderDetails[0]['pallets'] ?? '';
    // If orderDetails exists, get the orderContainerDetails for each orderDetails entry
    // $orderContainerDetails1 = $orderDetails
    //     ? $orderDetails->map(function ($orderDetail) {
    //         return $orderDetail->orderContainerDetails;
    //     })
    //     : null;

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
    }
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Order</title>
    <style>
        body {
            font-family: Verdana, Geneva, Tahoma, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }

        .delivery-order {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: #ffffff;
        }

        header {
            width: 100%;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        header .header-left {
            float: left;
        }

        header .header-left img {
            width: 120px;
        }

        header .header-right {
            float: right;
            text-align: right;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table th,
        table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            word-wrap: break-word;
            overflow-wrap: anywhere;
        }

        table th {
            background-color: rgb(221, 238, 240);
            font-weight: bold;
        }

        .highlight {
            background: black;
            color: white;
            text-align: center;
            padding: 10px;
            font-weight: bold;
            margin-top: 20px;
        }

        .details-section {
            margin-top: 20px;
        }

        .details-section p {
            margin: 0;
        }

        footer {
            margin-top: 0px;
            /* border-top: 1px solid #ddd; */
            padding-top: 0px;
        }

        .clearfix {
            clear: both;
        }
    </style>
</head>
{{-- <p>
    <pre>{{ json_encode($orderContainerDetails, JSON_PRETTY_PRINT) }}</pre>
</p> --}}

<body>
    <div class="delivery-order">
        <header>
            <div class="header-left">
                <img src="https://via.placeholder.com/120" alt="FFC Logo">
                <h1>Delivery Order</h1>
            </div>
            <div class="header-right">
                <p><strong>SCAC:</strong> {{ $scac }}</p>
                <p><strong>USDOT #:</strong> {{ $usdot }}</p>
                <p><strong>MC #:</strong> {{ $mc }}</p>
                <p><a href="https://www.firstfreightcarriers.com">www.firstfreightcarriers.com</a></p>
                <p><strong> Ocean Freight | Customs Filing | Trucking | Transload | Warehousing</strong></p>
            </div>
            <div class="clearfix"></div>
        </header>

        <section class="details-section">
            <p>Please have your driver carry a printout of this DO and take the receiving warehouse personnel's name,
                signature, and delivery date/time as the POD.</p>
            <p style="display: flex; justify-content: space-between; font-size: 14px; margin: 5px 0;">
                <span><strong>Trucker:</strong> Retrieving data. Wait a few seconds and try to cut or copy again.</span>
                <span style="float:right"><strong>Date:</strong>
                    {{ \Carbon\Carbon::parse($data['received_date'])->format('m-d-Y') }}</span>
            </p>
            <p style="font-size: 14px; margin: 5px 0">
                <strong>Email:</strong> Dispatch@firstfreightcarriers.com
            </p>
        </section>

        <table>
            <thead>
                <tr>
                    <th>CONTAINER #</th>
                    <th>BL</th>
                    <th>PO</th>
                    <th>CPO</th>
                    <th>Seal</th>
                    <th>LFD</th>
                    <th>Weight (lbs)</th>
                    <th>Size</th>
                    <th># Of Pallets</th>
                </tr>
            </thead>
            <tbody>
                {{-- @foreach ($orderContainerDetails1[0] as $containerDetails) --}}
                @foreach ($orderContainerDetails as $containerDetails)
                    <tr>
                        <td>{{ $containerDetails['container_no'] }}</td>
                        <td>{{ $bl }}</td>
                        <td>{{ $containerDetails['po'] }}</td>
                        <td>{{ $containerDetails['cpo'] }}</td>
                        <td>{{ $seal }}</td>
                        <td>{{ $lfd }}</td>
                        <td>{{ $weight }}</td>
                        <td>{{ $containerDetails['container_size'] }}</td>
                        <td>{{ $pallets }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table>
            <thead>
                <tr>
                    <th style="text-align: center;">Deliver To</th>
                    <td colspan="3">{{ $data['address']['company_name'] }}</td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="text-align: center;"><strong>Delivery Appt Date</strong></td>
                    <td>{{ \Carbon\Carbon::parse($data['received_date'])->format('m-d') }}</td>
                    <td>Time:</td>
                    <td>07:00</td>
                </tr>
            </tbody>
        </table>

        <div class="highlight">PLEASE CONTACT FFC TO SCHEDULE DELIVERY</div>

        <table>
            <thead>
                <tr>
                    <th>Freight Location</th>
                    <td>{{ $orderDetails[0]['freight_location'] }}</td>
                    <th>Firms Code</th>
                    <td>{{ $orderDetails[0]['firm_code'] }}</td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th>Vessel/Voyage</th>
                    <td>{{ $orderDetails[0]['vessel_voyage'] }}</td>
                    <th>Commodity</th>
                    <td>{{ $orderDetails[0]['commodity'] }}</td>
                </tr>
                <tr>
                    <th>ETA</th>
                    <td>{{ $orderDetails[0]['eta'] }}</td>
                </tr>
            </tbody>
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
            padding: 0.5rem;
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
                    <span style="border-bottom: 1px solid #000;display: inline-block;width: 100px;">&nbsp;</span>
                    <strong style="margin-left:30px">Arrived at:</strong>
                    <span style="border-bottom: 1px solid #000;display: inline-block;width: 100px;">&nbsp;</span>
                    <strong style="margin-left:30px">Departed:</strong>
                    <span style="border-bottom: 1px solid #000;display: inline-block;width: 100px;">&nbsp;</span>
                </div>

                <div style="margin-top:30px">
                    <strong>Signature:</strong>
                    <span style="border-bottom: 1px solid #000;display: inline-block;width: 100px;">&nbsp;</span>
                    <strong style="margin-left:85px">Name:</strong>
                    <span style="border-bottom: 1px solid #000;display: inline-block;width: 100px;">&nbsp;</span>
                    <strong style="margin-left:55px">Date:</strong>
                    <span style="border-bottom: 1px solid #000;display: inline-block;width: 100px;">&nbsp;</span>
                </div>
            </div>

        </div>

        <footer>
            <div>
                <p>
                    <span>42619 Windflower Drive, Ashburn, VA 20148</span>
                    <span style="float:right">p.703.738.2834; F:703.842.8668</span>

                </p>
            </div>

        </footer>
    </div>
</body>

</html>
