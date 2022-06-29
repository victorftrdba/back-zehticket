<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Tickets comprados pelo usuário</title>
</head>

<body>
    <style>
        * {
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>

    @foreach ($codes as $code)
        @dd($code)
    @endforeach
</body>

</html>
