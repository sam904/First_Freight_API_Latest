@php
$image = base64_encode(file_get_contents(public_path('images/ffc_logo.jpeg')));
$orderDetails = $data['orderDetails'] ?? null;
$master_bl = $orderDetails[0]['master_bl'] ?? '';
$house_bl = $orderDetails[0]['house_bl'] ?? '';
$vessel_loaded_date = $orderDetails[0]['vessel_loaded_date'] ?? '';
$mother_vessel_date = $orderDetails[0]['mother_vessel_date'] ?? '';
$etd = $orderDetails[0]['etd'] ?? '';
$eta = $orderDetails[0]['eta'] ?? '';
$shipper = $orderDetails[0]['shipper'] ?? '';
$shipper_address = $orderDetails[0]['shipper_address'] ?? '';
$buyer = $orderDetails[0]['buyer'] ?? '';
$buyer_address = $orderDetails[0]['buyer_address'] ?? '';
$consignee = $orderDetails[0]['consignee'] ?? '';
$consignee_address = $orderDetails[0]['consignee_address'] ?? '';
$manufacturer = $orderDetails[0]['manufacturer'] ?? '';
$manufacturer_address = $orderDetails[0]['manufacturer_address'] ?? '';
$ship_to_party = $orderDetails[0]['ship_to_party'] ?? '';
$ship_to_party_address = $orderDetails[0]['ship_to_party_address'] ?? '';
$consolidator = $orderDetails[0]['consolidator'] ?? '';
$consolidator_address = $orderDetails[0]['consolidator_address'] ?? '';
$importer_record_name = $orderDetails[0]['importer_of_record_name'] ?? '';
$importer_record_number = $orderDetails[0]['importer_of_record_number'] ?? '';
$received_date = \Carbon\Carbon::parse($data['received_date'])->format('m-d-Y');
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

$orderHormonize = $orderDetails
    ? $orderDetails->map(function ($orderDetail) {
        return $orderDetail->orderHormonizeDetails;
    })
    : null;

foreach ($orderDeliveries[0] as $delivery) {
    $port_of_loading = $delivery['portOfLoading']['name'];
    $port_of_discharge = $delivery['portOfDischarge']['name'];
    //     $usdot = $delivery['vendor']['us_dot_number'];
    //     $transit_time = $delivery['transit_time'];
    //     $empty_pick_up_cutoff_date = $delivery['empty_pick_up_cutoff_date'];
    //     $transhipment_port = $delivery['transhipmentPort']['name'];
    //     $ignate_cutoff_date = '?';
}

@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ISF Form</title>
</head>
<style>
    @page {
        size: A4;
        margin: 2mm;
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
      margin-bottom: 2rem;
      padding: 0;
      background-color: #f9f9f9;
    ">
    <div style="margin: 0 auto; padding: 10px; max-width: 100%">
        <!-- Header Section -->
        <div style="border-bottom: 1px solid #ddd">
            <table style="width: 100%; border-collapse: collapse; margin: 0; padding: 0;">
                <tr>
                    <!-- Logo Section -->
                    <td>
                        <img src="data:image/jpeg;base64,{{ $image }}" alt="FFC Logo" />
                        <!-- Replace with your logo -->
                    </td>
                    <!-- Title Section -->
                    <td style="text-align: center; flex: 3;">
                        <h2 style="margin: 0; font-size: 12px; font-weight: bold;">
                            FIRST FREIGHT CARRIERS - ISF Form
                        </h2>
                        <p style="margin: 0; font-size: 12px;">
                            The Importer Security Filing (ISF) became effective 1/26/09.
                        </p>
                    </td>
                    <!-- Additional Section -->
                    <td style="width: 85px; height: 61px;">
                    </td>
                </tr>
            </table>
        </div>
        <table style="width: 100%; border-collapse: collapse; margin: 0; padding: 0;">
            <tr>
                <td style="text-align: right; flex: 1;">
                    <p style="margin-top: 1rem; font-size: 12px;">
                        <span style="background-color: #1976D20F; padding: 8px;"><strong>Today's Date:</strong>
                            <span>{{ $received_date }}</span></span>
                    </p>
                </td>
            </tr>
        </table>
        <!-- Red Note Section -->
        <div style="text-align: center; margin-top: 5px">
            <p style="color: red; font-size: 10px; padding: 5px">
                ** The following requested information must be accurately completed
                and returned no later than 48 hours before the container is
                loaded on the vessel **
            </p>
        </div>
    </div>
    <div
        style="
        display: flex;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 20px;
        margin: 0 auto;
        max-width: 100%;
      ">
        <div style="flex: 1; min-width: 280px">
            <table
                style="
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            table-layout: fixed;
          ">
                <!-- First Row -->
                <tr>
                    <td
                        style="
                border: 1px solid #ddd;
                background-color: #1976D20F;
                text-align: center;
                padding: 8px;
                font-weight: bold;
              ">
                        Master B/L Number
                    </td>
                    <td style="border: 1px solid #ddd; padding: 8px">{{ $master_bl }}</td>
                </tr>
                <!-- Second Row -->
                <tr>
                    <td
                        style="
                border: 1px solid #ddd;
                background-color: #1976D20F;
                text-align: center;
                padding: 8px;
                font-weight: bold;
              ">
                        House B/L Number
                    </td>
                    <td style="border: 1px solid #ddd; padding: 8px">{{ $house_bl }}</td>
                </tr>
            </table>
            <table
                style="
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            table-layout: fixed;
            /* margin-top: 30px; */
          ">
                <!-- First Row -->
                <tr>
                    <td
                        style="
                border: 1px solid #ddd;
                background-color: #1976D20F;
                text-align: center;
                padding: 8px;
                font-weight: bold;
              ">
                        Container Number/s
                    </td>
                    <td style="border: 1px solid #ddd; padding: 0px">
                        @foreach ($orderContainerDetails[0] as $item)
                            <div style="border-bottom: 1px solid #ddd; padding: 10px;">
                                {{ $item['container_no'] }}
                            </div>
                        @endforeach
                    </td>
                </tr>

            </table>
            <table
                style="
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            table-layout: fixed;
          ">
                <!-- First Row -->
                <tr>
                    <td
                        style="
                border: 1px solid #ddd;
                color: #f9f9f9;
                background-color: black;
                text-align: left;
                padding: 8px;
                font-weight: bold;
              ">
                        Shipper
                    </td>
                </tr>
            </table>
            <table
                style="
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            table-layout: fixed;
          ">
                <!-- First Row -->
                <tr>
                    <td
                        style="
                border: 1px solid #ddd;
                background-color: #1976D20F;
                text-align: center;
                padding: 8px;
                font-weight: bold;
              ">
                        Name
                    </td>
                    <td style="border: 1px solid #ddd; padding: 8px">{{ $shipper }}</td>
                </tr>
                <!-- Second Row -->
                <tr>
                    <td
                        style="
                border: 1px solid #ddd;
                background-color: #1976D20F;
                text-align: center;
                padding: 8px;
                font-weight: bold;
              ">
                        Address
                    </td>
                    <td style="border: 1px solid #ddd; padding: 8px">{{ $shipper_address }}</td>
                </tr>
                {{-- <tr>
                    <td
                        style=" border: 1px solid #ddd; background-color: #1976D20F; text-align: center; padding: 8px; font-weight: bold; ">
                        City, State, Zip
                    </td>
                    <td style="border: 1px solid #ddd; padding: 8px"></td>
                </tr>
                <tr>
                    <td
                        style=" border: 1px solid #ddd; background-color: #1976D20F; text-align: center; padding: 8px; font-weight: bold; ">
                        Country
                    </td>
                    <td style="border: 1px solid #ddd; padding: 8px"></td>
                </tr> --}}
            </table>

            <table
                style="
          width: 100%;
          border-collapse: collapse;
          margin-top: 10px;
          table-layout: fixed;
        ">
                <tr style="border: 1px solid #ddd; padding: 8px">
                    <td
                        style=" border: 1px solid #ddd; background-color: #1976D20F; text-align: center; padding: 8px; font-weight: bold;  ">
                        Importer of Record Name
                    </td>
                    <td style="border: 1px solid #ddd; padding: 8px">{{ $importer_record_name}}</td>
                </tr>
                <tr>
                    <td
                        style=" border: 1px solid #ddd; background-color: #1976D20F; text-align: center; padding: 8px; font-weight: bold;">
                        Importer of record Number
                    </td>
                    <td style="border: 1px solid #ddd; padding: 8px">{{$importer_record_number}}</td>
                </tr>
            </table>
            <table
                style="
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            table-layout: fixed;
          ">
                <!-- First Row -->
                <tr>
                    <td
                        style="
                border: 1px solid #ddd;
                color: #f9f9f9;
                background-color: black;
                text-align: left;
                padding: 8px;
                font-weight: bold;
              ">
                        Manufacturer/Supplier
                    </td>
                </tr>
            </table>
            <table
                style="
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            table-layout: fixed;
          ">
                <!-- First Row -->
                <tr>
                    <td
                        style="
                border: 1px solid #ddd;
                background-color: #1976D20F;
                text-align: center;
                padding: 8px;
                font-weight: bold;
              ">
                        Name
                    </td>
                    <td style="border: 1px solid #ddd; padding: 8px">{{ $manufacturer }}</td>
                </tr>
                <!-- Second Row -->
                <tr>
                    <td
                        style="
                border: 1px solid #ddd;
                background-color: #1976D20F;
                text-align: center;
                padding: 8px;
                font-weight: bold;
              ">
                        Address
                    </td>
                    <td style="border: 1px solid #ddd; padding: 8px">{{ $manufacturer_address }}</td>
                </tr>
                {{-- <tr>
                    <td
                        style=" border: 1px solid #ddd; background-color: #1976D20F; text-align: center; padding: 8px; font-weight: bold; ">
                        City, State, Zip
                    </td>
                    <td style="border: 1px solid #ddd; padding: 8px"></td>
                </tr>
                <tr>
                    <td
                        style=" border: 1px solid #ddd; background-color: #1976D20F; text-align: center; padding: 8px; font-weight: bold; ">
                        Country
                    </td>
                    <td style="border: 1px solid #ddd; padding: 8px"></td>
                </tr> --}}
            </table>
            </table>
            <table
                style="
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
        table-layout: fixed;
      ">
                <!-- First Row -->
                <tr>
                    <td
                        style="
            border: 1px solid #ddd;
            color: #f9f9f9;
            background-color: black;
            text-align: left;
            padding: 8px;
            font-weight: bold;
          ">
                        Consolidator
                    </td>
                </tr>
            </table>
            <table
                style="
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
        table-layout: fixed;
      ">
                <!-- First Row -->
                <tr>
                    <td
                        style="
            border: 1px solid #ddd;
            background-color: #1976D20F;
            text-align: center;
            padding: 8px;
            font-weight: bold;
          ">
                        Name
                    </td>
                    <td style="border: 1px solid #ddd; padding: 8px">{{ $consolidator }}</td>
                </tr>
                <!-- Second Row -->
                <tr>
                    <td
                        style="
            border: 1px solid #ddd;
            background-color: #1976D20F;
            text-align: center;
            padding: 8px;
            font-weight: bold;
          ">
                        Address
                    </td>
                    <td style="border: 1px solid #ddd; padding: 8px">{{ $consolidator_address }}</td>
                </tr>
                {{-- <tr>
                    <td
                        style="
            border: 1px solid #ddd;
            background-color: #1976D20F;
            text-align: center;
            padding: 8px;
            font-weight: bold;
          ">
                        City, State, Zip
                    </td>
                    <td style="border: 1px solid #ddd; padding: 8px"></td>
                </tr>
                <tr>
                    <td
                        style="
            border: 1px solid #ddd;
            background-color: #1976D20F;
            text-align: center;
            padding: 8px;
            font-weight: bold;
          ">
                        Country
                    </td>
                    <td style="border: 1px solid #ddd; padding: 8px"></td>
                </tr> --}}
            </table>
            <table
                style="
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
        table-layout: fixed;
      ">
                <!-- First Row -->
                <tr>
                    <td
                        style="
            border: 1px solid #ddd;
            background-color: #1976D20F;
            text-align: center;
            padding: 8px;
            font-weight: bold;
          ">
                        Port of Loading
                    </td>
                    <td style="border: 1px solid #ddd; padding: 8px">{{ $port_of_loading }}</td>
                </tr>
                <!-- Second Row -->
                <tr>
                    <td
                        style="
            border: 1px solid #ddd;
            background-color: #1976D20F;
            text-align: center;
            padding: 8px;
            font-weight: bold;
          ">
                        Port of Discharge
                    </td>
                    <td style="border: 1px solid #ddd; padding: 8px">{{ $port_of_discharge }}</td>
                </tr>
                <td
                    style="
            border: 1px solid #ddd;
            background-color: #1976D20F;
            text-align: center;
            padding: 8px;
            font-weight: bold;
          ">
                    ETD
                </td>
                <td style="border: 1px solid #ddd; padding: 8px">{{ $etd }}</td>
                </tr>
                <td
                    style="
            border: 1px solid #ddd;
            background-color: #1976D20F;
            text-align: center;
            padding: 8px;
            font-weight: bold;
          ">
                    ETA
                </td>
                <td style="border: 1px solid #ddd; padding: 8px">{{ $eta }}</td>
                </tr>
            </table>
            <table
                style="
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
        table-layout: fixed;
      ">
                <!-- First Row -->
                <tr>
                    <td
                        style="
            border: 1px solid #ddd;
            background-color: #1976D20F;
            text-align: center;
            padding: 8px;
            font-weight: bold;
          ">
                        This Document was sent to</br>
                        the Broker to be filed on

                    </td>
                    <td style="border: 1px solid #ddd; padding: 8px"></td>
                </tr>
            </table>
        </div>
        <div style="flex: 1; min-width: 280px">
            <table
                style="
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            table-layout: fixed;
          ">
                <!-- First Row -->
                <tr>
                    <td
                        style="
                border: 1px solid #ddd;
                background-color: #1976D20F;
                text-align: center;
                padding: 8px;
                font-weight: bold;
              ">
                        Vessel Loaded Date
                    </td>
                    <td style="border: 1px solid #ddd; padding: 8px">{{ $vessel_loaded_date }}</td>
                </tr>
                <!-- Second Row -->
                <tr>
                    <td
                        style="
                border: 1px solid #ddd;
                background-color: #1976D20F;
                text-align: center;
                padding: 8px;
                font-weight: bold;
              ">
                        Mother Vessel Date
                    </td>
                    <td style="border: 1px solid #ddd; padding: 8px">{{ $mother_vessel_date }}</td>
                </tr>
                <tr>
                    <td
                        style="
                border: 1px solid #ddd;
                background-color: #1976D20F;
                text-align: center;
                padding: 8px;
                font-weight: bold;
              ">
                        Import PO#
                    </td>
                    <td style="border: 1px solid #ddd; padding: 0px">
                        @foreach ($orderContainerDetails[0] as $item)
                            <div style="border-bottom: 1px solid #ddd; padding: 10px;">
                                {{ $item['po'] ?? '' }}
                            </div>
                        @endforeach
                    </td>
                </tr>
            </table>
            <table style="width: 100%; border-collapse: collapse; margin-top: 10px; table-layout: fixed; ">
                {{-- <tr>
                    <td
                        style="border: 1px solid #ddd; color: #f9f9f9; background-color: black; text-align: left; padding: 8px; font-weight: bold; ">
                        Buyer
                    </td>
                </tr> --}}
            </table>
            <table style="width: 100%; border-collapse: collapse; margin-top: 10px; table-layout: fixed; ">
                <tr>
                    <td
                        style="border: 1px solid #ddd; color: #f9f9f9; background-color: black; text-align: left; padding: 8px; font-weight: bold; ">
                        Buyer
                    </td>
                </tr>
            </table>
            <table style="width: 100%; border-collapse: collapse; margin-top: 10px; table-layout: fixed;">
                <!-- First Row -->
                <tr>
                    <td
                        style="border: 1px solid #ddd; background-color: #1976D20F; text-align: center; padding: 8px; font-weight: bold; ">
                        Name
                    </td>
                    <td style="border: 1px solid #ddd; padding: 8px">{{ $buyer }}</td>
                </tr>
                <tr>
                    <td
                        style="border: 1px solid #ddd; background-color: #1976D20F; text-align: center; padding: 8px; font-weight: bold; ">
                        Address
                    </td>
                    <td style="border: 1px solid #ddd; padding: 8px">{{ $buyer_address }}</td>
                </tr>

            </table>
            <table style="width: 100%; border-collapse: collapse; margin-top: 10px; table-layout: fixed; ">
                <!-- First Row -->
                <tr>
                    <td
                        style="border: 1px solid #ddd; color: #f9f9f9; background-color: black; text-align: left; padding: 8px; font-weight: bold;">
                        Consignee
                    </td>
                </tr>
            </table>
            <table style="width: 100%;border-collapse: collapse; margin-top: 10px;table-layout: fixed; ">
                <!-- First Row -->
                <tr>
                    <td
                        style="border: 1px solid #ddd;  background-color: #1976D20F; text-align: center;padding: 8px; font-weight: bold; ">
                        Name
                    </td>
                    <td style="border: 1px solid #ddd; padding: 8px">{{ $consignee }}</td>
                </tr>
                <!-- Second Row -->
                <tr>
                    <td
                        style="border: 1px solid #ddd; background-color: #1976D20F; text-align: center; padding: 8px; font-weight: bold; ">
                        Address
                    </td>
                    <td style="border: 1px solid #ddd; padding: 8px">{{ $consignee_address }}</td>
                </tr>
                {{-- <tr>
                    <td
                        style="
            border: 1px solid #ddd;
            background-color: #1976D20F;
            text-align: center;
            padding: 8px;
            font-weight: bold;
          ">
                        City, State, Zip
                    </td>
                    <td style="border: 1px solid #ddd; padding: 8px"></td>
                </tr> 
                <tr>
                    <td
                        style="
            border: 1px solid #ddd;
            background-color: #1976D20F;
            text-align: center;
            padding: 8px;
            font-weight: bold;
          ">
                        Country
                    </td>
                    <td style="border: 1px solid #ddd; padding: 8px"></td>
                </tr> --}}
                {{-- <tr>
                    <td
                        style="
            border: 1px solid #ddd;
            background-color: #1976D20F;
            text-align: center;
            padding: 8px;
            font-weight: bold;
          ">
                        Country of Origin
                    </td>
                    <td style="border: 1px solid #ddd; padding: 8px"></td>
                </tr> --}}
            </table>
            <table style=" width: 100%; border-collapse: collapse; margin-top: 10px; table-layout: fixed; ">
                <!-- First Row -->
                <tr>
                    <td
                        style=" border: 1px solid #ddd; color: #f9f9f9; background-color: black; text-align: left; padding: 8px; font-weight: bold;">
                        Ship to Party
                    </td>
                </tr>
            </table>
            <table style=" width: 100%; border-collapse: collapse; margin-top: 10px; table-layout: fixed; ">
                <!-- First Row -->
                <tr>
                    <td
                        style=" border: 1px solid #ddd; background-color: #1976D20F; text-align: center; padding: 8px; font-weight: bold;">
                        Name
                    </td>
                    <td style="border: 1px solid #ddd; padding: 8px">{{ $ship_to_party }}</td>
                </tr>
                <tr>
                    <td
                        style="border: 1px solid #ddd; background-color: #1976D20F; text-align: center; padding: 8px; font-weight: bold; ">
                        Address
                    </td>
                    <td style="border: 1px solid #ddd; padding: 8px">{{ $ship_to_party_address }}</td>
                </tr>
                {{-- <tr>
                    <td
                        style="
            border: 1px solid #ddd;
            background-color: #1976D20F;
            text-align: center;
            padding: 8px;
            font-weight: bold;
          ">
                        City, State, Zip
                    </td>
                    <td style="border: 1px solid #ddd; padding: 8px"></td>
                </tr> 
                <tr>
                    <td
                        style="
            border: 1px solid #ddd;
            background-color: #1976D20F;
            text-align: center;
            padding: 8px;
            font-weight: bold;
          ">
                        Country
                    </td>
                    <td style="border: 1px solid #ddd; padding: 8px"></td>
                </tr> --}}
            </table>
            <table style="width: 100%; border-collapse: collapse; margin-top: 10px; table-layout: fixed; ">
                <tr>
                    <td
                        style="border: 1px solid #ddd; color: #f9f9f9; background-color: black; text-align: left; padding: 8px; font-weight: bold; ">
                        Harmonized Tarriff Schedule Numbers
                    </td>
                </tr>
            </table>
            <table style=" width: 100%;  border-collapse: collapse;  margin-top: 10px; table-layout: fixed; ">
                <tr>
                    <th style="padding: 8px; text-align: left; background-color: #1976D20F;  border: 1px solid #ddd; ">
                        *Product and Parts*
                    </th>
                    <td style="width: 80%;padding: 0px;text-align: center;border: 1px solid #ddd;">
                        @foreach ($orderHormonize[0] as $item)
                            <div style="border-bottom: 1px solid #ddd; padding: 10px;">
                                {{ $item['tarriff_schedule_number'] }}
                            </div>
                        @endforeach
                    </td>
                </tr>
            </table>
            <p style="font-size: 12px; color: rgb(9, 8, 8); margin-top: 20px;font-weight: bold">
                *** HTS CODES PROVIDED FORISF FILING</br>
                PURPOSES ONLY***
            </p>

        </div>
    </div>
</body>

</html>
