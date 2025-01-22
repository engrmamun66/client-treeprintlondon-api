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
            background-color: #f2f3f8;
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
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .email-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .email-header img {
            width: 100px;
        }

        .email-content {
            font-size: 15px;
        }

        .reset-button {
            display: inline-block;
            background-color: #2be613;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 15px;
            margin: 10px 0;
        }

        .reset-button:hover {
            background-color: #37C923;
        }

        ul {
            padding-left: 20px;
        }

        ul li {
            margin-bottom: 10px;
        }

        .email-footer {
            text-align: center;
            margin-top: 20px;
        }

        .email-footer p {
            font-size: 12px;
            color: #999;
        }
    </style>
</head>

<body>
<div class="email-container">
    <div class="email-header">
        <img src="https://uc80b437a491c790b5ce059245c4.previews.dropboxusercontent.com/p/thumb/AChqrCDYIp_8LEOpGMFPbu82K58jKQZIhGsISzYHJquEGnMmaF1Ni8-WuNTkgFS3qeLlmjEpHVa-mqk2XqzvLm-1U8JVaaSFw7upNOHpyg0qZ2-Hs9etwnCAcmXHZnWKTcpwBB2Fxvi539CV_4E8QXPBEYQbr5ZMnEe1PyGwTLpI753-nxTYF8mCXsqPNDUyAimeD-Jeessfg6QxjCoIDk-r-tajvk8MNHPmU8leDxuuepzwUyxrGh3cd3CSdYSIASC9lwM4DhWblGPXiAj0-VNzJbJiMdSTKpiusYA1PTFWbyLAyDsGMwgf2cJseYgMMOwouBg8GpnfjHXwCul1L0HNCaD00g2Pz6qgD8uT7KKC0pDjp5_cFR851hI3SuKckvwlnybZBKDL9dwHLMCqAE5S/p.jpeg?is_prewarmed=true" alt="Tee Print Logo">
    </div>

    <div class="email-content">
    <p>Hello Admin,</p>

        <p>A new quotation has been submitted by <strong>{{ $mailData['full_name'] }}</strong>. Below are the details:</p>
        <ul>
            <li><strong>Customer Name:</strong> {{ $mailData['full_name'] }}</li>
            <li><strong>Email:</strong> {{ $mailData['email'] }}</li>
            <li><strong>Phone:</strong> {{ $mailData['phone'] }}</li>
        </ul>

        <p>Best regards,</p>
        <p>The Tee Print London Team</p>
    </div>

    <div class="email-footer">
        <p>&copy; 2024 IONIC Rentals. All rights reserved.</p>
    </div>
</div>

</body>

</html>
