<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <title>Nova encomenda</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 640px; margin: 0 auto; padding: 20px; }
        .box { background: #f8f8f8; padding: 14px; border-radius: 6px; margin-bottom: 14px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    </style>
</head>
<body>
<div class="container">
    <h2>Nova encomenda {{ $order->reference }}</h2>
    <div class="box">
        <p><strong>Cliente:</strong> {{ $order->full_name }}</p>
        <p><strong>Email:</strong> {{ $order->email }}</p>
        <p><strong>Telefone:</strong> {{ $order->shipping_phone ?: '—' }}</p>
        <p><strong>Total:</strong> {{ format_eur($order->total) }}</p>
        <p><strong>Pagamento:</strong> {{ $order->paymentMethodLabel() }}</p>
        <p><strong>Data:</strong> {{ $order->created_at?->format('d/m/Y H:i') }}</p>
    </div>

    <h3>Envio</h3>
    <div class="box">
        <p>{{ $order->shipping_address_1 }}</p>
        @if ($order->shipping_address_2)<p>{{ $order->shipping_address_2 }}</p>@endif
        <p>{{ $order->shipping_postcode }} {{ $order->shipping_city }} · {{ $order->shipping_country }}</p>
    </div>

    <h3>Faturação</h3>
    <div class="box">
        <p>{{ $order->billing_first_name }} {{ $order->billing_last_name }}</p>
        <p>{{ $order->billing_address_1 }}</p>
        @if ($order->billing_address_2)<p>{{ $order->billing_address_2 }}</p>@endif
        <p>{{ $order->billing_postcode }} {{ $order->billing_city }} · {{ $order->billing_country }}</p>
    </div>

    <h3>Produtos</h3>
    <table>
        <thead>
        <tr><th>Produto</th><th>Qtd</th><th>Preço</th><th>Total</th></tr>
        </thead>
        <tbody>
        @foreach ($order->items as $item)
            <tr>
                <td>{{ $item->name }}</td>
                <td>{{ $item->quantity }}</td>
                <td>{{ format_eur($item->price) }}</td>
                <td>{{ format_eur($item->line_total) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    @if ($order->notes)
        <p><strong>Notas:</strong> {{ $order->notes }}</p>
    @endif

    <p>Conta bancária apresentada ao cliente: {{ $bank['account_holder'] }} · {{ format_iban($bank['iban']) }}</p>
</div>
</body>
</html>
