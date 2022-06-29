<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Tickets comprados pelo usuário</title>
</head>

<body>
    <div>
        @foreach ($codes as $code)
            <div style="margin-bottom:15px;">
                <div>QRCode:
                    <img src="data:image/png;base64, {!! base64_encode(
                    QrCode::format('png')->size(100)->generate($code->code)) !!}" />
                </div>
                <div>Ingresso: <b>{{ $code->ticket->description }} | {{ $code->code }}</b></div>
                <div>Evento: <b>{{ $code->event->title }}</b></div>
                <div>Data do Evento: <b>{{ \Carbon\Carbon::parse($code->event->date)->format('d/m/y') }}</b></div>
            </div>
        @endforeach
    </div>
</body>

</html>
