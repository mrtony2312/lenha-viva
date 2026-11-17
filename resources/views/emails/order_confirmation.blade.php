<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <title>Confirmação da sua encomenda</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #f7f4ef; color: #1b3022; padding: 24px 20px 16px; text-align: center; }
        .header h2 { margin: 0; font-size: 18px; }
        .content { padding: 24px; background: #f9f9f9; }
        .box { background: #fff; padding: 16px; border-radius: 6px; margin-bottom: 16px; }
        .bank { border-left: 4px solid #1b3022; }
        table { width: 100%; border-collapse: collapse; margin: 12px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f5f5f5; }
        .footer { text-align: center; margin-top: 24px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h2>Confirmação da encomenda {{ $order->reference }}</h2>
    </div>
    <div class="content">
        <p>Olá {{ $order->shipping_first_name }},</p>
        <p>Obrigado pela sua encomenda. O pagamento é por <strong>transferência bancária</strong>. O envio inicia-se após a confirmação do pagamento.</p>

        <div class="box bank">
            <h3>Dados bancários</h3>
            <p><strong>Titular:</strong> {{ $bank['account_holder'] ?: '—' }}</p>
            @if ($bank['bank_name'])
                <p><strong>Banco:</strong> {{ $bank['bank_name'] }}</p>
            @endif
            <p><strong>IBAN:</strong> {{ format_iban($bank['iban']) ?: '—' }}</p>
            @if ($bank['bic'])
                <p><strong>BIC:</strong> {{ $bank['bic'] }}</p>
            @endif
            <p><strong>Montante:</strong> {{ format_eur($order->total) }}</p>
            <p><strong>Referência / descritivo:</strong> {{ $order->reference }}</p>
            @if ($bank['note'])
                <p>{{ $bank['note'] }}</p>
            @endif
            <p>Após a transferência, pode enviar o comprovativo na página de confirmação da encomenda.</p>
        </div>

        <div class="box">
            <p><strong>Referência:</strong> {{ $order->reference }}</p>
            <p><strong>Data:</strong> {{ $order->created_at?->format('d/m/Y H:i') }}</p>
            <p><strong>Email:</strong> {{ $order->email }}</p>
            <p><strong>Pagamento:</strong> {{ $order->paymentMethodLabel() }}</p>
        </div>

        <h3>Produtos</h3>
        <table>
            <thead>
            <tr>
                <th>Produto</th>
                <th>Qtd</th>
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
            </tbody>
        </table>
        <p><strong>Total:</strong> {{ format_eur($order->total) }}</p>

        <h3>Morada de envio</h3>
        <div class="box">
            <p>{{ $order->shipping_first_name }} {{ $order->shipping_last_name }}</p>
            <p>{{ $order->shipping_address_1 }}</p>
            @if ($order->shipping_address_2)<p>{{ $order->shipping_address_2 }}</p>@endif
            <p>{{ $order->shipping_postcode }} {{ $order->shipping_city }}</p>
            <p>{{ $order->shipping_country }}</p>
        </div>

        <p>Obrigado por escolher a Naturalenha.</p>
    </div>
    <div class="footer">
        <p>{{ config('company.legal_name') }} · NIF {{ config('company.nif') }}</p>
        <p>{{ config('company.email') }} · {{ config('company.phone') }}</p>
    </div>
</div>
</body>
</html>
