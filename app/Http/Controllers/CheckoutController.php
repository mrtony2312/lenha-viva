<?php

namespace App\Http\Controllers;

use App\Mail\AdminOrderNotification;
use App\Mail\OrderConfirmation;
use App\Mail\PaymentProofSubmittedMail;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\SiteSetting;
use App\Support\CategoryLabels;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    protected $pays;

    public function __construct()
    {
        $this->pays = config('countries');
    }

    public function show()
    {
        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect()->route('carrinho')->with('error', 'O seu carrinho está vazio.');
        }

        $unavailable = $this->unavailableCartItems($cart);
        if ($unavailable !== []) {
            return redirect()->route('carrinho')->with(
                'error',
                'Um ou mais produtos do carrinho já não estão disponíveis. Atualize o carrinho e tente novamente.'
            );
        }

        $totalItems = 0;
        $totalPrice = 0.00;

        foreach ($cart as $item) {
            $itemQuantity = (int) ($item['quantity'] ?? 0);
            $itemPrice = $this->cleanPrice($item['price'] ?? 0);

            $totalItems += $itemQuantity;
            $totalPrice += ($itemPrice * $itemQuantity);
        }

        $checkoutCountries = collect($this->pays)->only(['PT'])->all();

        return view('checkout', [
            'cart' => $cart,
            'totalItems' => $totalItems,
            'totalPrice' => $totalPrice,
            'formattedTotalPrice' => number_format($totalPrice, 2, ',', ' '),
            'isEmpty' => empty($cart),
            'pays' => $checkoutCountries,
        ]);
    }

    public function confirmation(Order $order): View
    {
        $order->load('items');

        return view('checkout.confirmation', [
            'order' => $order,
            'bank' => SiteSetting::bankAccount(),
            'relatedProducts' => $this->relatedProductsForOrder($order),
        ]);
    }

    /**
     * Products from the same categories as the ordered items (Omniva-style related block).
     *
     * @return Collection<int, array<string, mixed>>
     */
    private function relatedProductsForOrder(Order $order): Collection
    {
        $catalog = collect(config('loja_products'));
        $orderedSlugs = $order->items->pluck('slug')->filter()->all();
        $orderedIds = $order->items->pluck('product_id')->filter()->all();

        $categoryKeys = $catalog
            ->filter(function (array $product) use ($orderedSlugs, $orderedIds) {
                $slugMatch = in_array($product['slug'] ?? null, $orderedSlugs, true);
                $idMatch = in_array($product['id'] ?? null, $orderedIds, true);

                return $slugMatch || $idMatch;
            })
            ->flatMap(function (array $product) {
                $internal = CategoryLabels::normalizeInternal($product['category'] ?? null)
                    ?? (string) ($product['category'] ?? '');

                return CategoryLabels::productKeysFor($internal);
            })
            ->unique()
            ->values()
            ->all();

        if ($categoryKeys === []) {
            return collect();
        }

        return $catalog
            ->filter(fn (array $p) => in_array($p['category'] ?? null, $categoryKeys, true))
            ->reject(fn (array $p) => in_array($p['slug'] ?? null, $orderedSlugs, true)
                || in_array($p['id'] ?? null, $orderedIds, true))
            ->filter(fn (array $p) => $this->cleanPrice($p['price'] ?? 0) > 0)
            ->take(8)
            ->values();
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'shipping_method' => 'required|string|max:255',
            'payment_method' => 'required|string|in:bacs',
            'order_notes' => 'nullable|string|max:1000',
            'terms_checkbox' => 'accepted',
            'email' => 'required|email',
            'shipping-country' => 'required|string|in:PT',
            'shipping-first_name' => 'required|string|max:255',
            'shipping-last_name' => 'required|string|max:255',
            'shipping-address_1' => 'required|string|max:500',
            'shipping-address_2' => 'nullable|string|max:500',
            'shipping-city' => 'required|string|max:255',
            'shipping-postcode' => 'required|string|max:20',
            'shipping-phone' => 'nullable|string|max:20',
            'billing-country' => 'required|string|in:PT',
            'billing-first_name' => 'required|string|max:255',
            'billing-last_name' => 'required|string|max:255',
            'billing-address_1' => 'required|string|max:500',
            'billing-address_2' => 'nullable|string|max:500',
            'billing-city' => 'required|string|max:255',
            'billing-postcode' => 'required|string|max:20',
            'billing-phone' => 'nullable|string|max:20',
        ], [
            'terms_checkbox.accepted' => 'Deve aceitar as condições gerais de venda e a política de privacidade.',
            'payment_method.in' => 'Apenas transferência bancária está disponível.',
            'shipping-country.in' => 'As entregas são feitas apenas em Portugal.',
            'billing-country.in' => 'A faturação deve ser em Portugal.',
        ]);

        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect()->route('carrinho')->with('error', 'O seu carrinho está vazio.');
        }

        $unavailable = $this->unavailableCartItems($cart);
        if ($unavailable !== []) {
            return redirect()->route('carrinho')->with(
                'error',
                'Um ou mais produtos do carrinho já não estão disponíveis. Atualize o carrinho e tente novamente.'
            );
        }

        $totalItems = 0;
        $totalPrice = 0.00;

        foreach ($cart as $item) {
            $itemQuantity = (int) ($item['quantity'] ?? 0);
            $itemPrice = $this->cleanPrice($item['price'] ?? 0);
            $totalItems += $itemQuantity;
            $totalPrice += ($itemPrice * $itemQuantity);
        }

        $order = Order::create([
            'reference' => 'NE-'.strtoupper(Str::random(8)),
            'status' => 'pending',
            'payment_method' => 'bacs',
            'shipping_method' => $validated['shipping_method'],
            'email' => $validated['email'],
            'shipping_first_name' => $validated['shipping-first_name'],
            'shipping_last_name' => $validated['shipping-last_name'],
            'shipping_address_1' => $validated['shipping-address_1'],
            'shipping_address_2' => $validated['shipping-address_2'] ?? null,
            'shipping_city' => $validated['shipping-city'],
            'shipping_postcode' => $validated['shipping-postcode'],
            'shipping_country' => $validated['shipping-country'],
            'shipping_phone' => $validated['shipping-phone'] ?? null,
            'billing_first_name' => $validated['billing-first_name'],
            'billing_last_name' => $validated['billing-last_name'],
            'billing_address_1' => $validated['billing-address_1'],
            'billing_address_2' => $validated['billing-address_2'] ?? null,
            'billing_city' => $validated['billing-city'],
            'billing_postcode' => $validated['billing-postcode'],
            'billing_country' => $validated['billing-country'],
            'billing_phone' => $validated['billing-phone'] ?? null,
            'notes' => $validated['order_notes'] ?? null,
            'subtotal' => $totalPrice,
            'shipping' => 0,
            'total' => $totalPrice,
            'currency' => 'EUR',
        ]);

        foreach ($cart as $productId => $item) {
            $price = $this->cleanPrice($item['price'] ?? 0);
            $qty = (int) ($item['quantity'] ?? 0);

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => is_numeric($productId) ? (int) $productId : ($item['id'] ?? null),
                'name' => (string) ($item['title'] ?? $item['name'] ?? 'Produto'),
                'slug' => $item['slug'] ?? null,
                'price' => $price,
                'quantity' => $qty,
                'line_total' => $price * $qty,
            ]);
        }

        session()->forget('cart');
        $order->load('items');
        $this->sendOrderMails($order);

        return redirect()->route('checkout.confirmation', $order->reference)
            ->with('success', 'A sua encomenda foi recebida com sucesso!');
    }

    public function uploadPaymentProof(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'payment_proof' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf,webp', 'max:8192'],
        ], [
            'payment_proof.required' => 'Selecione o comprovativo de transferência.',
            'payment_proof.file' => 'O comprovativo deve ser um ficheiro.',
            'payment_proof.mimes' => 'Formatos permitidos: JPG, PNG, WEBP ou PDF.',
            'payment_proof.max' => 'O ficheiro pode ter no máximo 8 MB.',
        ]);

        $previousPath = $order->payment_proof_path;
        $proofPath = $data['payment_proof']->store('payment-proofs', 'local');

        $order->update([
            'payment_proof_path' => $proofPath,
        ]);

        if (is_string($previousPath) && $previousPath !== '' && $previousPath !== $proofPath) {
            Storage::disk('local')->delete($previousPath);
        }

        $order->refresh()->load('items');

        try {
            Mail::to(config('mail.admin.address'), config('mail.admin.name'))
                ->send(new PaymentProofSubmittedMail($order));
        } catch (\Throwable $e) {
            Log::error('Email de comprovativo ao admin falhou', [
                'order' => $order->reference,
                'error' => $e->getMessage(),
            ]);
        }

        return redirect()
            ->route('checkout.confirmation', $order->reference)
            ->with('status', 'O seu comprovativo de transferência foi enviado com sucesso.');
    }

    private function sendOrderMails(Order $order): void
    {
        try {
            Mail::to($order->email, $order->full_name)
                ->send(new OrderConfirmation($order));
        } catch (\Throwable $e) {
            Log::error('Email de confirmação ao cliente falhou', [
                'order' => $order->reference,
                'error' => $e->getMessage(),
            ]);
        }

        try {
            Mail::to(config('mail.admin.address'), config('mail.admin.name'))
                ->send(new AdminOrderNotification($order));
        } catch (\Throwable $e) {
            Log::error('Email de nova encomenda ao admin falhou', [
                'order' => $order->reference,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @param  array<int|string, array<string, mixed>>  $cart
     * @return list<int|string>
     */
    private function unavailableCartItems(array $cart): array
    {
        $catalog = collect(config('loja_products', []))->keyBy('id');
        $unavailable = [];

        foreach ($cart as $productId => $item) {
            $live = $catalog->get((int) $productId) ?? $catalog->get((string) $productId);
            if (! $live || empty($live['in_stock']) || $this->cleanPrice($live['price'] ?? 0) <= 0) {
                $unavailable[] = $productId;
            }
        }

        return $unavailable;
    }

    private function cleanPrice($price): float
    {
        if (is_numeric($price)) {
            return (float) $price;
        }

        if (empty($price)) {
            return 0.0;
        }

        if (is_string($price)) {
            $price = str_replace(',', '', $price);
            $price = preg_replace('/[^\d.]/', '', $price);

            return (float) $price;
        }

        return 0.0;
    }
}
