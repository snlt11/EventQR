<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $event->name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .card {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-top: 20px;
        }
        h1 {
            font-size: 28px;
            margin-bottom: 20px;
            color: #2c3e50;
        }
        .qr-code {
            text-align: center;
            margin: 20px 0;
        }
        .qr-code svg {
            max-width: 100%;
            height: auto;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 14px;
            color: #7f8c8d;
        }
        @media screen and (max-width: 480px) {
            .container {
                padding: 10px;
            }
            .card {
                padding: 20px;
            }
            h1 {
                font-size: 24px;
            }
        }
        @media screen and (min-width: 481px) and (max-width: 768px) {
            .container {
                padding: 15px;
            }
            .card {
                padding: 25px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
                <h1>Hello {{ $participant->name }},</h1>
                @if($status == 'approve')
                    <p>You have been invited to our exclusive event. We're excited to have you join us!</p>
                    <div class="qr-code">
                        <p>Your personal QR code for check-in:</p>
                        {!! $qrCodeSvg !!}
                    </div>
                    <p>Please present this QR code at the event entrance for quick and easy check-in.</p>
                    <p>We look forward to seeing you at the event!</p>
                @else
                    <p>We're sorry, but your participation has been rejected. If you have any questions, please contact the event organizer.</p>
                    <p>Thank you for your interest in our event.</p>
                @endif
        </div>
    </div>
    <div class="footer">
        <p>If you have any questions, please don't hesitate to contact us.</p>
        <p>© EventQR {{ date('Y') }} All rights reserved.</p>
    </div>
</body>
</html>
