<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap"
          rel="stylesheet">
    <link href='https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css'
          rel='stylesheet'>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.4.1/css/all.css"/>

    <style>
        *,
        ::after,
        ::before {
            -webkit-box-sizing: border-box;
            box-sizing: border-box;
        }

        body {
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
            font-family: "Poppins", sans-serif;
            color: #333;
            line-height: 1.6;
        }

        .email-container {
            width: 100%;
            max-width: 600px;
            margin: 30px auto;
            background: #ffffff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .email-header {
            text-align: center;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }

        .email-header img {
            height: 80px;
            width: auto;
        }

        .email-content {
            font-size: 15px;
            color: #555;
        }

        .order-button {
            display: inline-block;
            background-color: #000000;
            color: white !important;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 6px;
            font-size: 15px;
            margin: 15px 0;
            font-weight: 500;
            text-align: center;
        }

        .order-button:hover {
            background-color: #333333;
        }

        ul {
            padding-left: 20px;
            margin: 20px 0;
        }

        ul li {
            margin-bottom: 12px;
            line-height: 1.5;
        }

        .order-details {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }

        .email-footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            font-size: 13px;
            color: #999;
        }

        .social-links {
            margin: 15px 0;
        }

        .social-links a {
            color: #000;
            margin: 0 8px;
            font-size: 18px;
        }
    </style>
</head>

<body>
<div class="email-container">
    <div class="email-header">
        <img src="https://res.cloudinary.com/dyfnpakfq/image/upload/v1737653056/NnZbENb4_pgjaw2.jpg" alt="Tee Print London Logo" style="border-radius: 8px;">
        <h2 style="margin-bottom: 0; color: #000;">New Order Received</h2>
    </div>

    <div class="email-content">
        <p>Hello {{ $mailData['customer_first_name'] }} {{ $mailData['customer_last_name'] }},</p>
        
        <p>Thank you for your order with Tee Print London! We're excited to create your custom designs.</p>
        
        <div class="order-details">
            <h3 style="margin-top: 0; color: #000;">Order #{{ $mailData['order_number'] }}</h3>
            <ul>
                <li><strong>Name:</strong> {{ $mailData['customer_first_name'] }} {{ $mailData['customer_last_name'] }}</li>
                <li><strong>Email:</strong> {{ $mailData['customer_email'] }}</li>
                <li><strong>Phone:</strong> {{ $mailData['customer_phone'] }}</li>
                <li><strong>Order Total:</strong> £{{ number_format($mailData['total'], 2) }}</li>
            </ul>
        </div>

        <!-- <p>We'll send you another email once your order has been processed and shipped. You can expect your items within 3-5 business days.</p>
        
        <p style="text-align: center;">
            <a href="{{ $mailData['order_link'] }}" class="order-button">View Your Order</a>
        </p> -->
        
        <p>If you have any questions about your order, please reply to this email or contact us at <a href="mailto:hello@teeprintlondon.co.uk">hello@teeprintlondon.co.uk</a>.</p>
        
        <p>Best regards,<br>The Tee Print London Team</p>
    </div>

    <div class="email-footer">
        <p>&copy; 2024 Tee Print London. All rights reserved.<br></p>
    </div>
</div>
</body>
</html>