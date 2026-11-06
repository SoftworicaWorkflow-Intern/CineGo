<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>CineGo Ticket {{ $booking->booking_code }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
            color: #161616;
            background: #ffffff;
            padding: 24px;
            width: 372px;
        }
        .brand {
            font-size: 22px;
            font-weight: bold;
            color: #E50914;
            letter-spacing: 1px;
        }
        .brand span { color: #161616; }
        .code {
            float: right;
            font-size: 13px;
            font-weight: bold;
            background: #161616;
            color: #ffffff;
            padding: 5px 9px;
            border-radius: 5px;
        }
        .movie { font-size: 21px; font-weight: bold; margin-top: 16px; color: #161616; }
        .sub { font-size: 12px; color: #555555; margin-top: 3px; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        td {
            font-size: 12px;
            padding: 7px 8px;
            border-bottom: 1px dashed #cccccc;
            vertical-align: top;
        }
        td.label { color: #777777; width: 38%; }
        td.value { font-weight: bold; color: #161616; }
        .total td { font-size: 15px; border-bottom: none; padding-top: 12px; }
        .total .value { color: #E50914; }
        .qr { text-align: center; margin-top: 18px; }
        .qr svg { width: 150px; height: 150px; }
        .qr p { font-size: 11px; color: #555555; margin-top: 7px; }
        .status {
            display: inline-block;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 3px 8px;
            border-radius: 4px;
            background: #e8f7ee;
            color: #157f3d;
        }
        .status.unpaid { background: #fdf3e0; color: #9a6a00; }
        .footer {
            margin-top: 16px;
            padding-top: 10px;
            border-top: 1px solid #dddddd;
            font-size: 10px;
            color: #888888;
            text-align: center;
        }
    </style>
</head>
<body>
    <table style="width: 100%; border: none;">
        <tr>
            <td style="border: none; padding: 0;">
                <span class="brand">Cine<span>Go</span></span>
            </td>
            <td style="border: none; padding: 0; text-align: right;">
                <span class="code">{{ $booking->booking_code }}</span>
            </td>
        </tr>
    </table>

    <p class="movie">{{ $booking->movie_title }}</p>
    <p class="sub">{{ $booking->format }}</p>

    <table>
        <tr>
            <td class="label">Date</td>
            <td class="value">{{ $booking->show_date->format('D, M j, Y') }}</td>
        </tr>
        <tr>
            <td class="label">Time</td>
            <td class="value">{{ \Illuminate\Support\Str::of($booking->start_time)->substr(0, 5) }}</td>
        </tr>
        <tr>
            <td class="label">Seats</td>
            <td class="value">{{ $booking->seat_labels() }}</td>
        </tr>
        <tr>
            <td class="label">Customer</td>
            <td class="value">{{ $booking->customer_name }}<br>{{ $booking->customer_phone }}</td>
        </tr>
        <tr>
            <td class="label">Payment</td>
            <td class="value">
                <span class="status {{ $booking->is_paid ? '' : 'unpaid' }}">
                    {{ $booking->is_paid ? 'Paid' : 'Pay at counter' }}
                </span>
            </td>
        </tr>
        <tr class="total">
            <td class="label">Total</td>
            <td class="value">Rs. {{ number_format($booking->total_amount) }}</td>
        </tr>
    </table>

    <div class="qr">
        {!! $qrSvg !!}
        <p>Scan this QR code at the entrance — one scan per ticket.</p>
    </div>

    <p class="footer">
        CineGo · Discover Movies. Book Your Seats. · support@cinego.com.np · +977-9800-000-000
    </p>
</body>
</html>
