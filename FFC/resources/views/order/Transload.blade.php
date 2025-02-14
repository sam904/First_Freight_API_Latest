@php
$receivedDate = \Carbon\Carbon::parse($data['received_date'])->format('m-d-Y') ?? null;

// Check if orderDetails exists, and then access the related orderContainerDetails
$orderDetails = $data['orderDetails'] ?? null;

$bl = $orderDetails[0]['master_bl'] ?? null;
$seal = $orderDetails[0]['seal'] ?? null;
$lfd = $orderDetails[0]['last_free_day'] ?? null;
$weight = $orderDetails[0]['weight'] ?? null;
$pallets = $orderDetails[0]['pallets'] ?? null;
$freightLocation = $orderDetails[0]['freight_location'] ?? null;
$firmCode = $orderDetails[0]['firm_code'] ?? null;
$vesselVoyage = $orderDetails[0]['vessel_voyage'] ?? null;
$commodity = $orderDetails[0]['commodity'] ?? null;
$eta = $orderDetails[0]['eta'] ?? null;
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
    $mode = $delivery['mode'] ?? null;
    $pickedUpDate = \Carbon\Carbon::parse($delivery['pickedUpDate'])->format('m-d-Y') ?? null;
    $scac = $delivery['vendor']['scac_number'] ?? null;
    $mc = $delivery['vendor']['mc_number'] ?? null;
    $usdot = $delivery['vendor']['us_dot_number'] ?? null;
    $schedule_date_time = $delivery['schedule_date'] ?? null;
    if ($schedule_date_time) {
        $ScheduleDate = \Carbon\Carbon::parse($schedule_date_time)->toDateString(); // Extracts date (YYYY-MM-DD)
        $ScheduleTime = \Carbon\Carbon::parse($schedule_date_time)->toTimeString(); // Extracts time (HH:MM:SS)
    } else {
        $date = null;
        $time = null;
    }
}

$image = base64_encode(file_get_contents(public_path('images/ffc_logo.jpeg')));
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
    <div class="delivery-order" style="max-width:'100%'; margin: 0 auto; padding: 10px">
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

        <section class="order-info" style="margin-top: 20px">
            <p style="font-size: 12px; margin: 0">
                Please have your driver carry a printout of this DO and take the
                receiving warehouse personnel's name, signature, and delivery
                date/time as the POD.
            </p>
            <div
                style="
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
          ">
                <div style="flex: 1">
                    <p style="font-size: 12px; margin: 5px 0">
                        <strong>Trucker:</strong> Retrieving data. Wait a few seconds and
                        try to cut or copy again.
                    </p>
                </div>
                <div style="text-align: right">
                    <p style="font-size: 12px; margin: 5px 0">
                        <strong>Date:</strong> {{ $receivedDate }}
                    </p>
                </div>
            </div>
        </section>

        <section class="contact" style="margin-top: 5px; border-radius: 5px">
            <p class="highlight" style="padding: 5px; text-align: center">
                Email: Dispatch@firstfreightcarriers.com
            </p>
        </section>

        <table class="details-table" style="width: 100%; margin-top: 5px; border-collapse: collapse">
            <thead>
                <tr style="background-color: #1976D20F">
                    <th
                        style="border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 12px !important; font-weight: 600;">
                        CONTAINER #
                    </th>
                    <th
                        style="border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 12px !important; font-weight: 600;">
                        BL
                    </th>
                    <th
                        style="border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 12px !important; font-weight: 600;">
                        PO
                    </th>
                    <th
                        style="border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 12px !important; font-weight: 600;">
                        CPO
                    </th>
                    <th
                        style="border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 12px !important; font-weight: 600;">
                        Seal
                    </th>
                    <th
                        style="border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 12px !important; font-weight: 600;">
                        LFD
                    </th>
                    <th
                        style="border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 12px !important; font-weight: 600;">
                        Weight (lbs)
                    </th>
                    <th
                        style="border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 12px !important; font-weight: 600;">
                        Size
                    </th>
                    <th
                        style="border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 12px !important; font-weight: 600;">
                        # Of Pallets
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach ($orderContainerDetails as $containerDetails)
                    <tr>
                        <td style="border: 1px solid #ddd; padding: 8px">{{ $containerDetails['container_no'] ?? null }}
                        </td>
                        <td style="border: 1px solid #ddd; padding: 8px">{{ $bl }}</td>
                        <td style="border: 1px solid #ddd; padding: 8px">{{ $containerDetails['po'] ?? null }}</td>
                        <td style="border: 1px solid #ddd; padding: 8px">{{ $containerDetails['cpo'] ?? null }}</td>
                        <td style="border: 1px solid #ddd; padding: 8px">{{ $seal }}</td>
                        <td style="border: 1px solid #ddd; padding: 8px">{{ $lfd }}</td>
                        <td style="border: 1px solid #ddd; padding: 8px">{{ $weight }}</td>
                        <td style="border: 1px solid #ddd; padding: 8px">
                            {{ $containerDetails['container_size'] ?? null }}</td>
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
                background-color: #1976D20F;
                padding: 8px;
                text-align: center;
              ">
                        <strong>Deliver To:</strong>
                    </th>
                    <td colspan="3"
                        style="border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 12px !important;">
                        <p style="margin: 0">{{ $data['address']['company_name'] ?? null }}</p>
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
                background-color: #1976D20F;
                padding: 8px;
                text-align: center;
              ">
                        <strong>Delivery Appt Date:</strong>
                    </td>
                    <td style="border: 1px solid #ddd; padding: 8px; text-align: center">
                        {{ \Carbon\Carbon::parse($ScheduleDate)->format('m-d') ?? null }}
                    </td>
                    <td
                        style="
                border: 1px solid #ddd;
                padding: 8px;
                font-weight: bold;
                text-align: center;
                background-color: #1976D20F;
              ">
                        Time:
                    </td>
                    <td style="border: 1px solid #ddd; padding: 8px; text-align: center">
                    {{ $ScheduleTime ?? null }}
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
                  background-color: #1976D20F;
                ">
                            <strong>Freight Location:</strong>
                        </th>
                        <td style="border: 1px solid #ddd; padding: 8px">
                            {{ $freightLocation }}
                        </td>
                        <th rowspan="3" style="
                                                                  border: 1px solid #ddd;
                                                                  padding: 8px;
                                                                  text-align: left;
                                                                  background-color: #1976D20F;
                                                                ">
                            <strong>Commodity:</strong>
                        </th>
                        <td style="border: 1px solid #ddd; padding: 8px">
                            {{ $commodity }}
                        </td>
                        {{-- <th
                            style="
                  border: 1px solid #ddd;
                  padding: 8px;
                  text-align: left;
                  background-color: #1976D20F;
                ">
                            <strong>Firms Code:</strong>
                        </th>
                        <td style="border: 1px solid #ddd; padding: 8px">{{ $firmCode }}</td> --}}
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th style="
                                          border: 1px solid #ddd;
                                          padding: 8px;
                                          text-align: left;
                                          background-color: #1976D20F;
                                        ">
                            <strong>Mode Of Transport:</strong>
                        </th>
                        <td style="border: 1px solid #ddd; padding: 8px">
                            {{ $mode }}
                        </td>
                        {{-- <th
                            style="
                  border: 1px solid #ddd;
                  padding: 8px;
                  text-align: left;
                  background-color: #1976D20F;
                ">
                            <strong>Vessel/Voyage:</strong>
                        </th>
                        <td style="border: 1px solid #ddd; padding: 8px">
                            {{ $vesselVoyage }}
                        </td> --}}
                        {{-- <th
                            style="
                  border: 1px solid #ddd;
                  padding: 8px;
                  text-align: left;
                  background-color: #1976D20F;
                ">
                            <strong>Commodity:</strong>
                        </th>
                        <td style="border: 1px solid #ddd; padding: 8px">
                            {{ $commodity }}
                        </td> --}}
                    </tr>
                    <tr>
                        <th style="border: 1px solid #ddd;padding: 8px;text-align: left;background-color: #1976D20F;">
                            <strong>Pick Up Date/Time:</strong>
                        </th>
                        <td style="border: 1px solid #ddd; padding: 8px">{{ $pickedUpDate }}</td>
                        {{-- <th
                            style="
                  border: 1px solid #ddd;
                  padding: 8px;
                  text-align: left;
                  background-color: #1976D20F;
                ">
                            <strong>ETA:</strong>
                        </th>
                        <td style="border: 1px solid #ddd; padding: 8px">{{ $eta }}</td> --}}
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
                background-color: #1976D20F;
                padding: 8px;
                text-align: center;
              ">
                        The driver should carry a copy of this BL and an ID to pick up the
                        cargo.
                    </th>
                </tr>
                <tr>
                    <th
                        style="
                border: 1px solid #ddd;
                background-color: #1976D20F;
                padding: 8px;
                text-align: center;
              ">
                        FFC will pay the assigned trucker once the job is completed.
                    </th>
                </tr>
                <tr>
                    <th
                        style="
                border: 1px solid #ddd;
                background-color: #1976D20F;
                padding: 8px;
                text-align: center;
              ">
                        The assigned trucker takes responsibility to pay the party
                        completing this job.
                    </th>
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
                        <strong>Bill To:</strong>
                    </th>
                    <td colspan="2"
                        style="border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 12px !important;">
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
                    <td colspan="2"
                        style="border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 12px !important;">
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

        <table style="width: 100%; margin-top: 10px; border: #dddddd;   border-collapse: collapse;">
            <thead>
                <tr>
                    <th colspan="3"
                        style="background-color: #1976D20F; border: 1px solid #ddd; font-weight: bold; text-align: center; padding: 10px;">
                        RECEIVING WAREHOUSE CONFIRMATION
                    </th>
                </tr>
            </thead>
            <tbody style="border: 1px solid #ddd;">
                <tr>
                    <td colspan="3" style="padding: 10px; font-weight: 600;">
                        Cargo is received in good condition.
                    </td>
                </tr>
                <tr>
                    <td style="text-align: center; padding: 10px;">
                        Appointment time: <span
                            style="display: inline-block; border-bottom: 1px solid #000; width: 150px;"></span>
                    </td>
                    <td style="text-align: center; padding: 10px;">
                        Arrived at: <span
                            style="display: inline-block; border-bottom: 1px solid #000; width: 150px;"></span>
                    </td>
                    <td style="text-align: center; padding: 10px;">
                        Departed: <span
                            style="display: inline-block; border-bottom: 1px solid #000; width: 150px;"></span>
                    </td>
                </tr>
                <tr>
                    <td style="text-align: center; padding: 7px;">
                        <span style="display: inline-block; border-bottom: 1px solid #000; width: 150px;"></span>
                    </td>
                    <td style="text-align: center; padding: 7px;">
                        <span style="display: inline-block; border-bottom: 1px solid #000; width: 150px;"></span>
                    </td>
                    <td style="text-align: center; padding: 7px;">
                        <span style="display: inline-block; border-bottom: 1px solid #000; width: 150px;"></span>
                    </td>
                </tr>
                <tr>
                    <td style="text-align: center; padding: 5px;">
                        <span style="font-weight: bold;">Signature</span>
                    </td>
                    <td style="text-align: center; padding: 5px;">
                        <span style="font-weight: bold;">Name</span>
                    </td>
                    <td style="text-align: center; padding: 5px;">
                        <span style="font-weight: bold;">Date</span>
                    </td>
                </tr>
            </tbody>
        </table>


        {{-- <footer style="display: flex;justify-content: space-between;align-items: flex-end;margin-top: 3px;">
            <div style="flex: 1">
                <p style="font-size: 10px; margin: 5px 0; font-weight: 600;">
                    42619 Windflower Drive,Ashbum,VA 20148
                </p>
            </div>
            <div style="text-align: right">
                <p style="font-size: 10px; margin: 5px 0; font-weight: 600;">
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
