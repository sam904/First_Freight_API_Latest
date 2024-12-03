@php
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
{{-- <p>
    <pre>{{ json_encode($orderContainerDetails, JSON_PRETTY_PRINT) }}</pre>
</p> --}}

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ISF Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
</head>

<body style="font-family: Arial, sans-serif; line-height: 1.5">
    <div style="padding: 20px; margin: 0px 50px">
        <!-- Header Section -->
        <div style="border-bottom: 1px solid #000000">
            <div style="display: flex; align-items: center">
                <!-- Logo Section -->
                <div style="flex: 1">
                    <img src="../assests/2c17c7_7916ccc68c1a478abd1027f013a9a64a~mv2 1 (1).png" alt="FFC Logo"
                        style="width: 100 px; height: 100px" />
                    <!-- Replace with your logo -->
                </div>

                <!-- Title Section -->
                <div style="flex: 3; text-align: center">
                    <h2 style="margin: 0; font-size: 18px; font-weight: bold">
                        FIRST FREIGHT CARRIERS - ISF Form
                    </h2>
                    <p style="margin: 0; font-size: 12px">
                        The Importer Security Filing (ISF) became effective 1/26/09.
                    </p>
                </div>

                <!-- Date Section -->
                <div style="flex: 1; text-align: right">
                    <p style="margin: 0; font-size: 12px">
                        <!-- <strong>Today's Date:</strong> <span>10-09-2024</span> -->
                    </p>
                </div>
            </div>
        </div>
        <!-- Red Note Section -->
        <div style="text-align: center; margin-top: 10px">
            <p style="color: red; font-size: 12px; padding: 30px">
                ** The following requested information must be accurately completed
                and <br />returned no later than 48 hours before the container is
                loaded on the vessel **
            </p>
        </div>

        <div class="row">
            <div class="col-6 col-lg-6">

                <table style="width: 100%;border-collapse: collapse;margin-top: 10px;table-layout: fixed;">
                    <!-- First Row -->
                    <tr>
                        <td
                            style="
                  border: 1px solid #000000;
                  background-color: #1976d20f;
                  text-align: center;
                  padding: 8px;
                  font-weight: bold;
                ">
                            Master B/L Number
                        </td>
                        <td style="border: 1px solid #000000; padding: 8px">{{ $master_bl }}</td>
                    </tr>
                    <!-- Second Row -->
                    <tr>
                        <td
                            style="
                  border: 1px solid #000000;
                  background-color: #1976d20f;
                  text-align: center;
                  padding: 8px;
                  font-weight: bold;
                ">
                            House B/L Number
                        </td>
                        <td style="border: 1px solid #000000; padding: 8px">{{ $house_bl }}</td>
                    </tr>
                </table>

                <table
                    style="
              width: 100%;
              border-collapse: collapse;
              margin-top: 10px;
              table-layout: fixed;
              margin-top: 20px;
            ">
                    <!-- First Row -->
                    <tr>
                        <td
                            style="
                  border: 1px solid #000000;
                  background-color: #1976d20f;
                  text-align: center;
                  padding: 20px;
                  font-weight: bold;
                ">
                            Container Number/s
                        </td>
                        <td style="border: 1px solid #000000; padding: 0px">
                            @foreach ($orderContainerDetails[0] as $item)
                                <div style="border-bottom: 1px solid #000000; padding: 10px;">
                                    {{ $item['container_no'] }}
                                </div>
                            @endforeach
                        </td>
                    </tr>
                </table>

            </div>
            <div class="col-6 col-lg-6">
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
                  border: 1px solid #000000;
                  background-color: #1976d20f;
                  text-align: center;
                  padding: 8px;
                  font-weight: bold;
                ">
                            Vessel Loaded Date
                        </td>
                        <td style="border: 1px solid #000000; padding: 8px">{{ $vessel_loaded_date }}</td>
                    </tr>
                    <!-- Second Row -->
                    <tr>
                        <td
                            style="
                  border: 1px solid #000000;
                  background-color: #1976d20f;
                  text-align: center;
                  padding: 8px;
                  font-weight: bold;
                ">
                            Mother Vessel Date
                        </td>
                        <td style="border: 1px solid #000000; padding: 8px">{{ $mother_vessel_date }}</td>
                    </tr>
                    <tr>
                        <td
                            style="
                  border: 1px solid #000000;
                  background-color: #1976d20f;
                  text-align: center;
                  padding: 8px;
                  font-weight: bold;
                ">
                            Import PO#
                        </td>
                        <td style="border: 1px solid #000000; padding: 0px">
                            @foreach ($orderContainerDetails[0] as $item)
                                <div style="border-bottom: 1px solid #000000; padding: 10px;">
                                    {{ $item['po'] }}
                                </div>
                            @endforeach
                        </td>
                    </tr>

                </table>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-6 col-6">
                <div style="
              background-color: black;
              color: white;
              text-align: left;
              margin-top: 30px;
              padding: 8px;
              font-size: 14px;
            "
                    colspan="2">
                    Shipper
                </div>
                <!-- Shipper Section -->
                <table
                    style="
              width: 100%;
              border-collapse: collapse;
              margin-top: 10px;
              table-layout: fixed;
            ">
                    <tbody>
                        <!-- First Row -->
                        <tr>
                            <td
                                style="
                    border: 1px solid #000000;
                    background-color: #1976d20f;
                    text-align: center;
                    padding: 8px;
                    font-weight: bold;
                  ">
                                Name
                            </td>
                            <td style="border: 1px solid #000000; padding: 8px">{{ $shipper }}</td>
                        </tr>
                        <!-- Second Row -->
                        <tr>
                            <td
                                style="
                    border: 1px solid #000000;
                    background-color: #1976d20f;
                    text-align: center;
                    padding: 8px;
                    font-weight: bold;
                  ">
                                Address
                            </td>
                            <td style="border: 1px solid #000000; padding: 8px">{{ $shipper_address }}</td>
                        </tr>
                        <tr>
                            <td
                                style="
                    border: 1px solid #000000;
                    background-color: #1976d20f;
                    text-align: center;
                    padding: 8px;
                    font-weight: bold;
                  ">
                                City, State, Zip
                            </td>
                            <td style="border: 1px solid #000000; padding: 8px">?</td>
                        </tr>
                        <tr>
                            <td
                                style="
                    border: 1px solid #000000;
                    background-color: #1976d20f;
                    text-align: center;
                    padding: 8px;
                    font-weight: bold;
                  ">
                                Country
                            </td>
                            <td style="border: 1px solid #000000; padding: 8px">?</td>
                        </tr>
                    </tbody>
                </table>
                <table
                    style="
              width: 100%;
              border-collapse: collapse;
              margin-top: 10px;
              table-layout: fixed;
              margin-top: 39px;
            ">
                    <!-- First Row -->
                    <tr>
                        <td
                            style="
                  border: 1px solid #000000;
                  background-color: #1976d20f;
                  text-align: center;
                  padding: 8px;
                  font-weight: bold;
                ">
                            Master B/L Number
                        </td>
                        <td style="border: 1px solid #000000; padding: 8px">{{ $master_bl }}</td>
                    </tr>
                    <tr>
                        <td
                            style="
                  border: 1px solid #000000;
                  background-color: #1976d20f;
                  text-align: center;
                  padding: 8px;
                  font-weight: bold;
                ">
                            House B/L Number
                        </td>
                        <td style="border: 1px solid #000000; padding: 8px">{{ $house_bl }}</td>
                    </tr>
                </table>

                <div style="
              background-color: black;
              color: white;
              text-align: left;
              margin-top: 10px;
              padding: 8px;
              font-size: 14px;
            "
                    colspan="2">
                    Manufacturer/Supplier
                </div>
                <!--Manufacturer/Supplier -->
                <table
                    style="
              width: 100%;
              border-collapse: collapse;
              margin-top: 10px;
              table-layout: fixed;
            ">
                    <tbody>
                        <!-- First Row -->
                        <tr>
                            <td
                                style="
                    border: 1px solid #000000;
                    background-color: #1976d20f;
                    text-align: center;
                    padding: 8px;
                    font-weight: bold;
                  ">
                                Name
                            </td>
                            <td style="border: 1px solid #000000; padding: 8px">{{ $manufacturer }}</td>
                        </tr>
                        <!-- Second Row -->
                        <tr>
                            <td
                                style="
                    border: 1px solid #000000;
                    background-color: #1976d20f;
                    text-align: center;
                    padding: 8px;
                    font-weight: bold;
                  ">
                                Address
                            </td>
                            <td style="border: 1px solid #000000; padding: 8px">{{ $manufacturer_address }}</td>
                        </tr>
                        <tr>
                            <td
                                style="
                    border: 1px solid #000000;
                    background-color: #1976d20f;
                    text-align: center;
                    padding: 8px;
                    font-weight: bold;
                  ">
                                City, State, Zip
                            </td>
                            <td style="border: 1px solid #000000; padding: 8px">?</td>
                        </tr>
                        <tr>
                            <td
                                style="
                    border: 1px solid #000000;
                    background-color: #1976d20f;
                    text-align: center;
                    padding: 8px;
                    font-weight: bold;
                  ">
                                Country
                            </td>
                            <td style="border: 1px solid #000000; padding: 8px">?</td>
                        </tr>
                    </tbody>
                </table>

                <div style="
              background-color: black;
              color: white;
              text-align: left;
              margin-top: 10px;
              padding: 8px;
              font-size: 14px;
            "
                    colspan="2">
                    Consolidator
                </div>
                <!-- Consolidator -->
                <table
                    style="
              width: 100%;
              border-collapse: collapse;
              margin-top: 10px;
              table-layout: fixed;
            ">
                    <tbody>
                        <!-- First Row -->
                        <tr>
                            <td
                                style="
                    border: 1px solid #000000;
                    background-color: #1976d20f;
                    text-align: center;
                    padding: 8px;
                    font-weight: bold;
                  ">
                                Name
                            </td>
                            <td style="border: 1px solid #000000; padding: 8px">{{ $consolidator }}</td>
                        </tr>
                        <!-- Second Row -->
                        <tr>
                            <td
                                style="
                    border: 1px solid #000000;
                    background-color: #1976d20f;
                    text-align: center;
                    padding: 8px;
                    font-weight: bold;
                  ">
                                Address
                            </td>
                            <td style="border: 1px solid #000000; padding: 8px">{{ $consolidator_address }}</td>
                        </tr>
                        <tr>
                            <td
                                style="
                    border: 1px solid #000000;
                    background-color: #1976d20f;
                    text-align: center;
                    padding: 8px;
                    font-weight: bold;
                  ">
                                City, State, Zip
                            </td>
                            <td style="border: 1px solid #000000; padding: 8px">?</td>
                        </tr>
                        <tr>
                            <td
                                style="
                    border: 1px solid #000000;
                    background-color: #1976d20f;
                    text-align: center;
                    padding: 8px;
                    font-weight: bold;
                  ">
                                Country
                            </td>
                            <td style="border: 1px solid #000000; padding: 8px">?</td>
                        </tr>
                    </tbody>
                </table>
                <table
                    style="
              width: 100%;
              border-collapse: collapse;
              margin-top: 10px;
              table-layout: fixed;
              margin-top: 39px;
            ">
                    <!-- First Row -->
                    <tr>
                        <td
                            style="
                  border: 1px solid #000000;
                  background-color: #1976d20f;
                  text-align: center;
                  padding: 8px;
                  font-weight: bold;
                ">
                            Port of Loading
                        </td>
                        <td style="border: 1px solid #000000; padding: 8px">{{ $port_of_loading }}</td>
                    </tr>
                    <tr>
                        <td
                            style="
                  border: 1px solid #000000;
                  background-color: #1976d20f;
                  text-align: center;
                  padding: 8px;
                  font-weight: bold;
                ">
                            Port of Discharge
                        </td>
                        <td style="border: 1px solid #000000; padding: 8px">{{ $port_of_discharge }}</td>
                    </tr>
                    <tr>
                        <td
                            style="
                  border: 1px solid #000000;
                  background-color: #1976d20f;
                  text-align: center;
                  padding: 8px;
                  font-weight: bold;
                ">
                            ETD
                        </td>
                        <td style="border: 1px solid #000000; padding: 8px">{{ $etd }}</td>
                    </tr>
                    <tr>
                        <td
                            style="
                  border: 1px solid #000000;
                  background-color: #1976d20f;
                  text-align: center;
                  padding: 8px;
                  font-weight: bold;
                ">
                            ETA
                        </td>
                        <td style="border: 1px solid #000000; padding: 8px">{{ $eta }}</td>
                    </tr>
                </table>
                <table
                    style="
              width: 100%;
              border-collapse: collapse;
              margin-top: 10px;
              table-layout: fixed;
              margin-top: 39px;
            ">
                    <!-- First Row -->
                    <tr>
                        <td
                            style="
                  border: 1px solid #000000;
                  background-color: #1976d20f;
                  text-align: center;
                  padding: 8px;
                  font-weight: bold;
                ">
                            This Document was sent to the Broker to be filed on
                        </td>
                        <td style="border: 1px solid #000000; padding: 8px"></td>
                    </tr>
                </table>
            </div>

            <div class="col-6 col-lg-6">
                <!-- Buyer Section -->
                <div style="
              background-color: black;
              color: white;
              text-align: left;
              margin-top: 30px;
              padding: 8px;
              font-size: 14px;
            "
                    colspan="2">
                    Buyer
                </div>
                <table
                    style="
              width: 100%;
              border-collapse: collapse;
              margin-top: 10px;
              table-layout: fixed;
            ">
                    <tbody>
                        <!-- First Row -->
                        <tr>
                            <td
                                style="
                    border: 1px solid #000000;
                    background-color: #1976d20f;
                    text-align: center;
                    padding: 8px;
                    font-weight: bold;
                  ">
                                Name
                            </td>
                            <td style="border: 1px solid #000000; padding: 8px">{{ $buyer }}</td>
                        </tr>
                        <!-- Second Row -->
                        <tr>
                            <td
                                style="
                    border: 1px solid #000000;
                    background-color: #1976d20f;
                    text-align: center;
                    padding: 8px;
                    font-weight: bold;
                  ">
                                Address
                            </td>
                            <td style="border: 1px solid #000000; padding: 8px">{{ $buyer_address }}</td>
                        </tr>
                        <tr>
                            <td
                                style="
                    border: 1px solid #000000;
                    background-color: #1976d20f;
                    text-align: center;
                    padding: 8px;
                    font-weight: bold;
                  ">
                                City, State, Zip
                            </td>
                            <td style="border: 1px solid #000000; padding: 8px">?</td>
                        </tr>
                        <tr>
                            <td
                                style="
                    border: 1px solid #000000;
                    background-color: #1976d20f;
                    text-align: center;
                    padding: 8px;
                    font-weight: bold;
                  ">
                                Country
                            </td>
                            <td style="border: 1px solid #000000; padding: 8px">?</td>
                        </tr>
                    </tbody>
                </table>

                <div style="
              background-color: black;
              color: white;
              text-align: left;
              margin-top: 30px;
              padding: 8px;
              font-size: 14px;
            "
                    colspan="2">
                    Consignee
                </div>
                <table
                    style="
              width: 100%;
              border-collapse: collapse;
              margin-top: 10px;
              table-layout: fixed;
            ">
                    <tbody>
                        <!-- First Row -->
                        <tr>
                            <td
                                style="
                    border: 1px solid #000000;
                    background-color: #1976d20f;
                    text-align: center;
                    padding: 8px;
                    font-weight: bold;
                  ">
                                Name
                            </td>
                            <td style="border: 1px solid #000000; padding: 8px">{{ $consignee }}</td>
                        </tr>
                        <!-- Second Row -->
                        <tr>
                            <td
                                style="
                    border: 1px solid #000000;
                    background-color: #1976d20f;
                    text-align: center;
                    padding: 8px;
                    font-weight: bold;
                  ">
                                Address
                            </td>
                            <td style="border: 1px solid #000000; padding: 8px">{{ $consignee_address }}</td>
                        </tr>
                        <tr>
                            <td
                                style="
                    border: 1px solid #000000;
                    background-color: #1976d20f;
                    text-align: center;
                    padding: 8px;
                    font-weight: bold;
                  ">
                                City, State, Zip
                            </td>
                            <td style="border: 1px solid #000000; padding: 8px">?</td>
                        </tr>
                        <tr>
                            <td
                                style="
                    border: 1px solid #000000;
                    background-color: #1976d20f;
                    text-align: center;
                    padding: 8px;
                    font-weight: bold;
                  ">
                                Country
                            </td>
                            <td style="border: 1px solid #000000; padding: 8px">?</td>
                        </tr>
                    </tbody>
                </table>
                <div style="
              background-color: black;
              color: white;
              text-align: left;
              margin-top: 30px;
              padding: 8px;
              font-size: 14px;
            "
                    colspan="2">
                    Ship to Party
                </div>
                <table
                    style="
              width: 100%;
              border-collapse: collapse;
              margin-top: 10px;
              table-layout: fixed;
            ">
                    <tbody>
                        <!-- First Row -->
                        <tr>
                            <td
                                style="
                    border: 1px solid #000000;
                    background-color: #1976d20f;
                    text-align: center;
                    padding: 8px;
                    font-weight: bold;
                  ">
                                Name
                            </td>
                            <td style="border: 1px solid #000000; padding: 8px">{{ $ship_to_party }}</td>
                        </tr>
                        <!-- Second Row -->
                        <tr>
                            <td
                                style="
                    border: 1px solid #000000;
                    background-color: #1976d20f;
                    text-align: center;
                    padding: 8px;
                    font-weight: bold;
                  ">
                                Address
                            </td>
                            <td style="border: 1px solid #000000; padding: 8px">{{ $ship_to_party_address }}</td>
                        </tr>
                        <tr>
                            <td
                                style="
                    border: 1px solid #000000;
                    background-color: #1976d20f;
                    text-align: center;
                    padding: 8px;
                    font-weight: bold;
                  ">
                                City, State, Zip
                            </td>
                            <td style="border: 1px solid #000000; padding: 8px">?</td>
                        </tr>
                        <tr>
                            <td
                                style="
                    border: 1px solid #000000;
                    background-color: #1976d20f;
                    text-align: center;
                    padding: 8px;
                    font-weight: bold;
                  ">
                                Country
                            </td>
                            <td style="border: 1px solid #000000; padding: 8px">?</td>
                        </tr>
                    </tbody>
                </table>

                <div style="
              background-color: black;
              color: white;
              text-align: left;
              margin-top: 30px;
              padding: 8px;
              font-size: 14px;
            "
                    colspan="2">
                    Harmonized Tarriff Schedule Numbers
                </div>

                <table
                    style="
              width: 100%;
              border-collapse: collapse;
              margin-top: 10px;
              table-layout: fixed;
              margin-top: 20px;
            ">
                    <!-- First Row -->
                    <tr>
                        <td
                            style="
                  border: 1px solid #000000;
                  background-color: #1976d20f;
                  text-align: center;
                  padding: 20px;
                  font-weight: bold;
                ">
                            *Product and Parts*
                        </td>
                        <td style="border: 1px solid #000000; padding: 0px">
                            @foreach ($orderHormonize[0] as $item)
                                <div style="border-bottom: 1px solid #000000; padding: 10px;">
                                    {{ $item['tarriff_schedule_number'] }}
                                </div>
                            @endforeach
                        </td>
                    </tr>
                </table>
                <p style="font-size: 12px; color: rgb(9, 8, 8); margin-top: 20px">
                    *** HTS CODES PROVIDED FOR ISF FILING PURPOSES ONLY ***
                </p>
            </div>
        </div>
    </div>
</body>

</html>
