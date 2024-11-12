<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .header,
        .footer {
            text-align: center;
        }

        .quote-details,
        .customer-details,
        .table-container {
            margin: 20px 0;
        }

        .table-container {
            width: 100%;
            border-collapse: collapse;
        }

        .table-container th,
        .table-container td {
            border: 1px solid #ddd;
            padding: 8px;
        }

        .table-container th {
            background-color: #f2f2f2;
        }

        .disclaimer {
            font-size: 10px;
            margin-top: 20px;
        }
    </style>
</head>

<body>

    <div class="header">
        <img src="path/to/logo.png" alt="Company Logo" style="width:100px;">
        <h2>First Freight Carriers</h2>
        <p>LLC 42619 Windflower Drive Ashburn, VA 20148.<br>+1 000-000-0000</p>
    </div>

    <h3>Quotation</h3>

    <div class="customer-details">
        <strong>Knight-Swift Transport</strong><br>
        10700 E 40th Ave Denver, CO 80239<br>
        +1 303-371-1500
    </div>

    <div class="quote-details">
        <p>Quote No. <strong>42602C</strong><br>
            Expires <strong>Sep 30, 2024</strong><br>
            Generated <strong>Sep 30, 2024</strong></p>
    </div>

    <table class="table-container">
        <thead>
            <tr>
                <th>Sr. No.</th>
                <th>Port</th>
                <th>Destination</th>
                <th>Dray + FSC</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data['quotes'] as $quote)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $quote['port'] }}</td>
                    <td>{{ $quote['destination'] }}</td>
                    <td>${{ number_format($quote['dray_fsc'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="disclaimer">
        <p><strong>Disclaimer:</strong><br>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod
            tempor incididunt ut labore et dolore magna aliqua.</p>
    </div>

    <div class="footer">
        <p>©2024 First Freight Carriers, LLC | All Rights Reserved</p>
    </div>

</body>

</html>
