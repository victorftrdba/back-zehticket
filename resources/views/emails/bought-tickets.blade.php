<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Tickets comprados pelo usuário</title>
</head>

<body>
    <div style="font-family: Roboto">
        <div style="text-align:center">
            <h2>Olá! Agradecemos a aquisição dos ingressos. <br/> Aproveite ao máximo seu evento!</h2>
            @foreach ($codes as $code)
                <div style="margin-bottom:15px;">
                    <div><b>QRCode</b></div>
                    <?php
                        $qrCodeAsPng = (string) QrCode::format('png')->margin(0)->size(200)->generate($code->code);
                    ?>
                    <img src="{!! $message->embedData($qrCodeAsPng, 'QrCode.png', 'image/png')!!}" style="margin-bottom:5px;">
                    <div><b>INGRESSO</b></div>
                    <div style="margin-bottom:5px;"><b>{{ $code->ticket->description }} | {{ $code->code }}</b></div>
                    <div><b>EVENTO</b></div>
                    <div style="margin-bottom:5px;"><b>{{ $code->event->title }}</b></div>
                    <div><b>DATA DO EVENTO</b></div>
                    <div><b>{{ \Carbon\Carbon::parse($code->event->date)->format('d/m/y') }}</b></div>
                </div>
            @endforeach
        </div>
    </div>
</body>

</html>
