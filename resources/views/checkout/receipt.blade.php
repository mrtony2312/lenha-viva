<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="utf-8">
    <title>Recibo {{ $order->reference }} — Naturalenha</title>
    <style>
        body { font-family: Georgia, 'Times New Roman', serif; color: #1a1a1a; margin: 32px; line-height: 1.45; }
        h1 { font-size: 22px; margin: 0 0 4px; }
        .muted { color: #555; font-size: 14px; margin: 0 0 24px; }
        table { width: 100%; border-collapse: collapse; margin: 16px 0; }
        th, td { text-align: left; padding: 8px 6px; border-bottom: 1px solid #e5e5e5; font-size: 14px; }
        th { font-size: 12px; text-transform: uppercase; letter-spacing: .04em; color: #666; }
        .total td { font-weight: 700; border-top: 2px solid #1a1a1a; }
        .box { border: 1px solid #e5e5e5; border-radius: 8px; padding: 14px 16px; margin: 18px 0; background: #fafafa; }
        .box h2 { font-size: 15px; margin: 0 0 8px; }
        .grid { display: grid; gap: 6px; font-size: 14px; }
        @media print { body { margin: 12mm; } }
    </style>
</head>
<body>
    <h1>Recibo de encomenda</h1>
    <p class="muted">Naturalenha · {{ $order->reference }} · {{ $order->created_at?->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</p>

    <div class="box">
        <h2>Cliente</h2>
        <div class="grid">
            <div>{{ $order->shipping_first_name }} {{ $order->shipping_last_name }}</div>
            <div>{{ $order->email }}</div>
            <div>{{ $order->shipping_address_1 }}@if($order->shipping_address_2), {{ $order->shipping_address_2 }}@endif</div>
            <div>{{ $order->shipping_postcode }} {{ $order->shipping_city }} · {{ $order->shipping_country }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Produto</th>
                <th>Qtd.</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
                <tr>
                    <td>{{ $item->name }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ format_eur($item->line_total) }}</td>
                </tr>
            @endforeach
            <tr>
                <td>Subtotal</td>
                <td></td>
                <td>{{ format_eur($order->subtotal) }}</td>
            </tr>
            <tr>
                <td>Envio (Portugal Continental)</td>
                <td></td>
                <td>Grátis</td>
            </tr>
            <tr class="total">
                <td>Total</td>
                <td></td>
                <td>{{ format_eur($order->total) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="box">
        <h2>Pagamento por transferência bancária</h2>
        <div class="grid">
            <div>Método: {{ $order->paymentMethodLabel() }}</div>
            <div>Titular: {{ $bank['account_holder'] ?: '—' }}</div>
            <div>IBAN: {{ format_iban($bank['iban']) ?: '—' }}</div>
            @if (!empty($bank['bic']))
                <div>BIC: {{ $bank['bic'] }}</div>
            @endif
            <div>Montante: {{ format_eur($order->total) }}</div>
            <div>Referência: {{ $order->reference }}</div>
            <div>Estado: aguarda validação do pagamento</div>
        </div>
    </div>

    <p class="muted">Este documento é um recibo de encomenda. O envio inicia-se após a confirmação do pagamento.</p>
</body>
</html>
