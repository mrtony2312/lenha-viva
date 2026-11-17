<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <title>Comprovativo de transferência</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333; line-height: 1.5;">
<p>O cliente enviou um comprovativo de transferência para a encomenda <strong>{{ $order->reference }}</strong>.</p>
<p><strong>Cliente:</strong> {{ $order->full_name }} ({{ $order->email }})</p>
<p><strong>Montante:</strong> {{ format_eur($order->total) }}</p>
<p>O ficheiro está em anexo.</p>
</body>
</html>
