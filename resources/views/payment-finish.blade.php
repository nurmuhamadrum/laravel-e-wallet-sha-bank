<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Payment Finish</title>
</head>
<body>
    @if($transaction)
        <h1>Payment Successful</h1>
        <h2>Status Order: {{ $transaction->status }}</h2>
        <h2>Order ID: {{ $transaction->id }}</h2>
    @else
        <h1>Payment Failed</h1>
        <p>There was an issue processing your payment. Please try again later.</p>
    @endif
</body>
</html>