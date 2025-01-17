<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quotation</title>
</head>

<body style="font-family: Arial, sans-serif; font-size: 12px; margin: 0; padding: 0;">
    @php
        $perPage = 10; // Number of rows per page
        $chunks = array_chunk($data['quotes'], $perPage); // Split data into chunks
        $image = base64_encode(file_get_contents(public_path('images/ffc_logo.jpeg')));
    @endphp

    <!-- Header -->
    <div style="width: 100%; display: table; margin-bottom: 5%;">
        <div style="display: table-cell; vertical-align: middle; width: 80%;">
            <img src="data:image/jpeg;base64,{{ $image }}" alt="Logo" style="max-width: 150px; height: auto;">
            <p style="font-size: 12px; margin-left:20%; margin-top:-12%;line-height:20px">
                <strong>Freight Carriers</strong><br>
                LLC 42619 Windflower Drive Ashburn,<br> VA 20148.<br>
                +1 000-000-0000
            </p>
        </div>
        <div style="display: table-cell; vertical-align: middle; text-align: right;">
            <div
                style="background-color: #d1e4f6;padding: 40px; font-size: 24px; font-weight: bold; border-radius: 8px;">
                <span style="">Quotation</span>
            </div>
        </div>
    </div>

    <!-- Customer Details -->
    <div
        style="margin-bottom: 5%; padding: 10px; padding-top:3%; background-color: #f6fafd; border: 1px solid #ddd; border-radius: 5px;">
        <p style="margin: 0; font-size: 12px;line-height:25px;text-align: left;">
            <strong>Knight-Swift Transport</strong><br>
            10700 E 40th Ave Denver, CO 80239<br>
            +1 303-371-1500
        </p>
        <div style="margin-bottom: 20px; margin-left:80%; margin-top:-15%;  background-color: #f6fafd;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <th style="padding: 5px; text-align: left; font-weight: normal;">Quote No.</th>
                    <td style="padding: 5px; text-align: left;">42602C</td>
                </tr>
                <tr>
                    <th style="padding: 5px; text-align: left; font-weight: normal;">Generated</th>
                    <td style="padding: 5px; text-align: left;">09/30/2024</td>
                </tr>
                <tr>
                    <th style="padding: 5px; text-align: left; font-weight: normal;">Validity</th>
                    <td style="padding: 5px; text-align: left;">09/30/2024</td>
                </tr>
            </table>
        </div>
    </div>

    @foreach ($chunks as $page => $chunk)
        <!-- Table Content -->
        <div style="margin-bottom: 20px;">
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                <thead>
                    <tr style="background-color: #f1f7fc;">
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Sr. No.</th>
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Port</th>
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Destination</th>
                        <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Dray + FSC</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($chunk as $index => $quote)
                        <tr>
                            <td style="border: 1px solid #ddd; padding: 8px; text-align: left;">
                                {{ $page * $perPage + $index + 1 }}</td>
                            <td style="border: 1px solid #ddd; padding: 8px; text-align: left;">{{ $quote['port'] }}
                            </td>
                            <td style="border: 1px solid #ddd; padding: 8px; text-align: left;">
                                {{ $quote['destination'] }}</td>
                            <td style="border: 1px solid #ddd; padding: 8px; text-align: left;">
                                ${{ number_format($quote['dray_fsc'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Footer -->
        <div
            style="position: absolute; bottom: 0; width: 100%; text-align: center; font-size: 12px; color: #888; border-top: 1px solid #ddd; padding: 10px 0;">
            ©2024 First Freight Carriers, LLC | All Rights Reserved
        </div>

        <!-- Page Break -->
        @if (!$loop->last)
            <div style="page-break-before: always;"></div>
        @endif
    @endforeach
</body>

</html>
