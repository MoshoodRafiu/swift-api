<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Swifthrive</title>
</head>
<body>
    <h1>Swifthrive</h1>
    <p>Hi {{ $data["username"] }},</p>
    <p>Please click the button below to verify your email address</p>
    <a href="{{ $data["link"] }}"><button>Verify Email Address</button></a>
    <br>
    <p>Regards,</p>
    <p>Swifthrive</p>
    <hr>
    <br>
    <p>If you have issues using the "Verify Email Address" button, copy and paste the link below in your browser.</p>
    <a href="{{ $data["link"] }}">{{ $data["link"] }}</a>
</body>
</html>
