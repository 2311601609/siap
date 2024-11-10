<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Notification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background-color: #ffffff;
            max-width: 400px;
            width: 100%;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            padding: 20px;
            text-align: center;
        }
        .header {
            font-size: 24px;
            font-weight: bold;
            color: #333333;
            margin-bottom: 20px;
        }
        .message {
            font-size: 16px;
            color: #666666;
            margin-bottom: 20px;
        }
        .otp-box {
            display: inline-block;
            background-color: #e6f7ff;
            color: #007bff;
            font-size: 36px;
            font-weight: bold;
            padding: 10px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .footer {
            font-size: 14px;
            color: #999999;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">Kode OTP SIAP Versi 2</div>
        <div class="message">Gunakan kode OTP berikut untuk melanjutkan proses otentikasi dan otorisasi Anda.</div>
        <div class="otp-box">{{ $otp }}</div>
        <div class="footer">Kode ini berlaku selama {{ $lifetime }} detik.<br>(Jika Anda melakukan login ulang, kode ini tidak berlaku lagi.)</div>
    </div>
</body>
</html>
