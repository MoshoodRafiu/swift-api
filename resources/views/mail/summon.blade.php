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
<p>Hi {{ $data["recipient"]["username"] }},</p>
<br>
<p>{{ $data["sender"]["username"] }} has summoned you to a trade of {{ $data["trade"]["amount"] }} {{ strtoupper($data["trade"]->coin['abbr']) }}, click the link below to attend to the trade.</p>
<a href="{{ $data["link"] }}"><button>Go to trade</button></a>
<br>
<p>Regards,</p>
<p>Swifthrive</p>
<hr>
<br>
<p>If you have issues using the "Go to trade" button, copy and paste the link below in your browser.</p>
<a href="{{ $data["link"] }}">{{ $data["link"] }}</a>
</body>
</html>
