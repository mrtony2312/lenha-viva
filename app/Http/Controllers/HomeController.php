<?php

namespace App\Http\Controllers;

use App\Repositories\LojaProduct;
use App\Support\CategoryLabels;
use Illuminate\Http\Request;

class HomeController extends Controller
{

    public function index()
    {
        $all = collect(config('loja_products', []));

        $homeCategories = collect(CategoryLabels::all())->map(function ($label, $key) use ($all) {
            $items = $all->where('category', $key)->values();
            $first = $items->first();
            $from = $items
                ->map(fn ($product) => (float) str_replace(',', '', $product['price'] ?? 0))
                ->filter(fn ($price) => $price > 0)
                ->min();

            return [
                'key' => $key,
                'label' => $label,
                'url' => CategoryLabels::route($key),
                'count' => $items->count(),
                'image' => $first['images'][0] ?? config('company.logo'),
                'from' => $from ?: null,
            ];
        })->filter(fn ($category) => $category['count'] > 0)->values();

        $byKey = $homeCategories->keyBy('key');

        $collections = collect([
            [
                'title' => 'Pellets de madeira',
                'desc' => 'Paletes e sacos para salamandras e caldeiras.',
                'url' => CategoryLabels::route('pellets-de-madeira'),
                'image' => 'wp-content/uploads/2025/10/paletes-pellets-sacos.jpg',
                'image_position' => 'center 40%',
                'from' => data_get($byKey, 'pellets-de-madeira.from'),
            ],
            [
                'title' => 'Lenha seca',
                'desc' => 'Lenha em palete, toros e madeira densificada.',
                'url' => CategoryLabels::route('madeira-de-fogo'),
                'image' => 'wp-content/uploads/2025/10/678998765434567806.webp',
                'image_position' => '80% 40%',
                'from' => data_get($byKey, 'madeira-de-fogo.from') ?? data_get($byKey, 'lenha.from'),
            ],
            [
                'title' => 'Fogões e salamandras',
                'desc' => 'Aquecimento a lenha para a sua casa.',
                'url' => CategoryLabels::route('chef-de-madeira'),
                'image' => 'wp-content/uploads/2025/10/fogao-sala-interior.jpg',
                'image_position' => 'center 35%',
                'from' => data_get($byKey, 'chef-de-madeira.from') ?? data_get($byKey, 'fogao-a-lenha.from'),
            ],
        ]);

        $onSale = $all->filter(function ($product) {
            $old = (float) str_replace(',', '', $product['old_price'] ?? 0);
            $price = (float) str_replace(',', '', $product['price'] ?? 0);

            return $old > $price && $price > 0;
        })->values();

        $dealProducts = $onSale
            ->groupBy('category')
            ->flatMap(fn ($group) => $group->take(2))
            ->values();

        if ($dealProducts->count() < 8) {
            $dealProducts = $dealProducts->concat($onSale)->unique('id')->values();
        }

        $dealProducts = $dealProducts->take(8);

        $featuredProducts = $all->where('category', 'pellets-de-madeira')->values();
        if ($featuredProducts->count() < 8) {
            $featuredProducts = $featuredProducts
                ->concat($all->where('category', '!=', 'pellets-de-madeira')->values())
                ->unique('id')
                ->values();
        }
        $featuredProducts = $featuredProducts->take(8);

        if ($dealProducts->isEmpty()) {
            $dealProducts = $featuredProducts;
        }

        $promoProducts = $dealProducts->take(4);

        return view('home', compact(
            'homeCategories',
            'collections',
            'dealProducts',
            'featuredProducts',
            'promoProducts',
        ));
    }


    public function quickView($id)
    {
        $allProducts = collect(config('loja_products', []));
        $product = $allProducts->firstWhere('id', $id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Produto não encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'product' => $product
        ]);
    }
    public function loja()
    {
        // Récupérer tous les filtres depuis la requête
        $filters = [
            'category' => request('product_cat'),
            'min_price' => (float) request('min_price', 110),
            'max_price' => (float) request('max_price', 2997),
            'in_stock' => request('stock') == '0' ? true : false,
            'search' => request('s'),
            'orderby' => request('orderby', 'menu_order'),
            'page' => request('page', 1),
            'product_visibility' => request('product_visibility'),
            'stock' => request('stock'),
            'colors' => request('colors', []),
        ];



        // Appliquer les filtres et obtenir les produits
        $query = LojaProduct::query();

        // Appliquer tous les filtres
        $query->applyFilters($filters);

        // Paginer les résultats
        $lojaProducts = $query->paginate(12);

        // Passer les valeurs actuelles pour les inputs
        $currentFilters = [
            'min_price' => $filters['min_price'],
            'max_price' => $filters['max_price'],
            'product_cat' => $filters['category'],
            'stock' => request('stock'),
            'orderby' => $filters['orderby'],
            's' => $filters['search']
        ];


      //  dd($lojaProducts);
        return view('loja', compact('lojaProducts', 'filters', 'currentFilters'));
    }

    public function carrinho()
    {
        $cart = session()->get('cart', []);

        // Calculer les totaux
        $totalItems = 0;
        $totalPrice = 0.00;

        foreach ($cart as $item) {
            $itemQuantity = (int) ($item['quantity'] ?? 0);
            $itemPrice = $this->cleanPrice($item['price'] ?? 0);

            $totalItems += $itemQuantity;
            $totalPrice += ($itemPrice * $itemQuantity);
        }

        // Formater avec 3 décimales
        $formattedTotalPrice = $this->formatPrice($totalPrice);

        // Récupérer les produits pour la section panier vide
        $allProducts = collect(config('loja_products', []));
        $newProducts = $allProducts->take(4)->map(function($product) {
            $product['price'] = $this->cleanPrice($product['price'] ?? 0);
            $product['old_price'] = isset($product['old_price']) ? $this->cleanPrice($product['old_price']) : null;
            return $product;
        });


      //  dd($cart);
        return view('carrinho', [
            'cart' => $cart,
            'totalItems' => $totalItems,
            'totalPrice' => $totalPrice,
            'formattedTotalPrice' => $formattedTotalPrice,
            'isEmpty' => empty($cart),
            'newProducts' => $newProducts
        ]);



    }
    private function getNewProducts()
    {
        $allProducts = collect(config('loja_products', []));
        return $allProducts->take(4)->map(function($product) {
            $product['price'] = $this->cleanPrice($product['price'] ?? 0);
            $product['old_price'] = isset($product['old_price']) ? $this->cleanPrice($product['old_price']) : null;
            return $product;
        });
    }

    /**
     * Afficher la page détaillée d'un produit
     */
    public function show($slug)
    {
        // Récupérer le produit par son slug
        $allProducts = collect(config('loja_products'));
        $product = $allProducts->firstWhere('slug', $slug);



        // Slugs were translated back to Portuguese: keep the old ones alive with a 301.
        if (!$product) {
            $newSlug = config('product_slug_redirects')[$slug] ?? null;

            if ($newSlug) {
                return redirect()->route('product.show', ['slug' => $newSlug], 301);
            }

            abort(404);
        }

        // Do not present a product without a price as a buyable offer.
        $sellablePrice = $this->cleanPrice($product['price'] ?? 0);
        if ($sellablePrice <= 0) {
            abort(404);
        }

        // Récupérer les produits de la même catégorie (pour la section "Produits liés")
        $relatedProducts = $allProducts
            ->where('category', $product['category'])
            ->where('id', '!=', $product['id'])
            ->filter(fn ($p) => $this->cleanPrice($p['price'] ?? 0) > 0)
            ->take(4);

        // Récupérer les produits précédent et suivant
        $allProductsSorted = $allProducts->values();
        $currentIndex = $allProductsSorted->search(function ($item) use ($product) {
            return $item['id'] == $product['id'];
        });

        $prevProduct = null;
        $nextProduct = null;

        if ($currentIndex > 0) {
            $prevProduct = $allProductsSorted[$currentIndex - 1];
        }

        if ($currentIndex < $allProductsSorted->count() - 1) {
            $nextProduct = $allProductsSorted[$currentIndex + 1];
        }

        // Formater les prix
        $formatPrice = function ($price) {
            return number_format(floatval($price), 2, ',', ' ');
        };

        //dd($relatedProducts);



        // Passer les données à la vue
        return view('products.show', compact(
            'product',
            'relatedProducts',
            'prevProduct',
            'nextProduct',
            'formatPrice'
        ));
    }

    private function formatPrice($price)
    {
        return number_format($price, 3, ',', ' ');
    }


    /**
     * Afficher les produits par catégorie
     */
    public function category($category)
    {
        $internal = \App\Support\CategoryLabels::fromUrlSlug($category);

        if (! $internal) {
            abort(404);
        }

        // Canonical Portuguese slug if an old internal key was used on the new path
        $canonical = \App\Support\CategoryLabels::urlSlug($internal);
        if ($canonical !== $category) {
            return redirect()->route('category', ['category' => $canonical], 301);
        }

        $filters = [
            'category' => $internal,
            'min_price' => (float) request('min_price', 110),
            'max_price' => (float) request('max_price', 2997),
            'in_stock' => request('stock') == '0' ? true : false,
            'search' => request('s'),
            'orderby' => request('orderby', 'menu_order'),
            'page' => request('page', 1),
            'product_visibility' => request('product_visibility'),
            'stock' => request('stock'),
            'colors' => request('colors', []),
        ];



        // Appliquer les filtres et obtenir les produits
        $query = LojaProduct::query();

        // Appliquer tous les filtres
        $query->applyFilters($filters);

        // Paginer les résultats
        $lojaProducts = $query->paginate(12);

        // Passer les valeurs actuelles pour les inputs
        $currentFilters = [
            'min_price' => $filters['min_price'],
            'max_price' => $filters['max_price'],
            'product_cat' => $filters['category'],
            'stock' => request('stock'),
            'orderby' => $filters['orderby'],
            's' => $filters['search']
        ];

        $categoryName = \App\Support\CategoryLabels::label($internal);
        $categorySeo = \App\Support\Seo::category($internal);

        return view('category', compact('lojaProducts', 'filters', 'currentFilters', 'categoryName', 'categorySeo'));
    }

    public function addToCart(Request $request)
    {
        try {
            $productId = $request->input('product_id');
            $quantity = $request->input('quantity', 1);

            // Valider l'ID du produit
            if (!$productId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Falta o ID do produto.'
                ], 400);
            }

            // Valider la quantité
            $quantity = (int) $quantity;
            if ($quantity < 1) {
                $quantity = 1;
            }

            // Récupérer le produit depuis la configuration
            $allProducts = collect(config('loja_products', []));

            if ($allProducts->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Não há nenhum produto disponível.'
                ], 404);
            }

            // Chercher le produit par ID
            $product = null;
            foreach ($allProducts as $prod) {
                if (isset($prod['id']) && $prod['id'] == $productId) {
                    $product = $prod;
                    break;
                }
            }

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produto não encontrado com o ID: ' . $productId
                ], 404);
            }

            if (empty($product['in_stock'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este produto está esgotado.',
                ], 422);
            }

            // Nettoyer et valider le prix
            $price = $this->cleanPrice($product['price'] ?? 0);

            if ($price <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este produto não está disponível para compra online.',
                ], 422);
            }

            // Préparer les données du produit
            $productData = [
                'id' => $productId,
                'title' => $product['title'] ?? 'Produto sem nome',
                'price' => $price,
                'quantity' => $quantity,
                'image' => $product['images'][0] ?? ($product['image'] ?? null),
                'slug' => $product['slug'] ?? 'producto-' . $productId,
                'old_price' => $product['old_price'],
                'short_description' => $product['short_description'],


            ];

            // Récupérer le panier depuis la session
            $cart = session()->get('cart', []);

            // Ajouter ou mettre à jour le produit dans le panier
            if (isset($cart[$productId])) {
                $cart[$productId]['quantity'] += $quantity;
            } else {
                $cart[$productId] = $productData;
            }

            // Sauvegarder dans la session
            session()->put('cart', $cart);

            // Calculer les totaux
            $totalItems = 0;
            $totalPrice = 0;

            foreach ($cart as $item) {
                $itemQuantity = (int) ($item['quantity'] ?? 0);
                $itemPrice = $this->cleanPrice($item['price'] ?? 0);

                $totalItems += $itemQuantity;
                $totalPrice += ($itemPrice * $itemQuantity);
            }

            return response()->json([
                'success' => true,
                'message' => 'Produto adicionado ao carrinho!',
                'cart' => $cart,
                'totalItems' => $totalItems,
                'totalPrice' => number_format($totalPrice, 2, '.', ''),
                'product' => $cart[$productId]
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur dans addToCart: ' . $e->getMessage());
            \Log::error('Trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Ocorreu um erro ao adicionar o produto ao carrinho.'
            ], 500);
        }
    }



    public function getCartContent()
    {
        try {
            $cart = session()->get('cart', []);
            $totalItems = 0;
            $totalPrice = 0.00;

            foreach ($cart as $item) {
                $itemQuantity = (int) ($item['quantity'] ?? 0);
                $itemPrice = $this->cleanPrice($item['price'] ?? 0);

                $totalItems += $itemQuantity;
                $totalPrice += ($itemPrice * $itemQuantity);
            }

            return response()->json([
                'success' => true,
                'cart' => $cart,
                'totalItems' => $totalItems,
                'totalPrice' => $totalPrice,
                'formattedTotalPrice' => $this->formatPrice($totalPrice)
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur dans getCartContent: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'cart' => [],
                'totalItems' => 0,
                'totalPrice' => 0.00
            ]);
        }
    }
    public function debugProducts()
    {
        $products = config('loja_products', []);

        echo "<pre>";
        echo "Nombre de produits: " . count($products) . "\n\n";

        foreach ($products as $index => $product) {
            echo "Produit #{$index}:\n";
            echo "ID: " . ($product['id'] ?? 'N/A') . "\n";
            echo "Titre: " . ($product['title'] ?? 'N/A') . "\n";
            echo "Prix: " . ($product['price'] ?? 'N/A') . " (Type: " . gettype($product['price'] ?? 'N/A') . ")\n";
            echo "Prix nettoyé: " . $this->cleanPrice($product['price'] ?? 0) . "\n";
            echo "---\n";
        }

        exit();
    }


    public function updateCart(Request $request)
    {
        try {
            $productId = $request->input('product_id');
            $quantity = (int) $request->input('quantity', 1);

            $cart = session()->get('cart', []);

            if (isset($cart[$productId])) {
                if ($quantity < 1) {
                    unset($cart[$productId]);
                } else {
                    $cart[$productId]['quantity'] = $quantity;
                }

                session()->put('cart', $cart);

                $totalItems = 0;
                $totalPrice = 0.00;

                foreach ($cart as $item) {
                    $itemQuantity = (int) ($item['quantity'] ?? 0);
                    $itemPrice = $this->cleanPrice($item['price'] ?? 0);

                    $totalItems += $itemQuantity;
                    $totalPrice += ($itemPrice * $itemQuantity);
                }

                return response()->json([
                    'success' => true,
                    'cart' => $cart,
                    'totalItems' => $totalItems,
                    'totalPrice' => $totalPrice,
                    'formattedTotalPrice' => $this->formatPrice($totalPrice)
                ]);
            }

            return response()->json(['success' => false, 'message' => 'Produto não encontrado'], 404);

        } catch (\Exception $e) {
            \Log::error('Erreur updateCart: ' . $e->getMessage());
            return response()->json(['success' => false], 500);
        }
    }

    public function removeFromCart(Request $request)
    {
        try {
            $productId = $request->input('product_id');
            $cart = session()->get('cart', []);

            if (isset($cart[$productId])) {
                unset($cart[$productId]);
                session()->put('cart', $cart);

                $totalItems = 0;
                $totalPrice = 0.00;

                foreach ($cart as $item) {
                    $itemQuantity = (int) ($item['quantity'] ?? 0);
                    $itemPrice = $this->cleanPrice($item['price'] ?? 0);

                    $totalItems += $itemQuantity;
                    $totalPrice += ($itemPrice * $itemQuantity);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Produto removido do carrinho',
                    'cart' => $cart,
                    'totalItems' => $totalItems,
                    'totalPrice' => $totalPrice,
                    'formattedTotalPrice' => $this->formatPrice($totalPrice)
                ]);
            }

            return response()->json(['success' => false, 'message' => 'Produto não encontrado'], 404);

        } catch (\Exception $e) {
            \Log::error('Erreur removeFromCart: ' . $e->getMessage());
            return response()->json(['success' => false], 500);
        }
    }
    private function cleanPrice($price)
    {
        if (is_numeric($price)) {
            return (float) $price;
        }

        if (empty($price)) {
            return 0.000;
        }

        if (is_string($price)) {
            // Price strings use ',' as a thousands separator and '.' as the decimal separator
            // (e.g. "2,499.00"). Strip the thousands separator before casting.
            $price = str_replace(',', '', $price);
            $price = preg_replace('/[^\d.]/', '', $price);
            return (float) $price;
        }

        return 0.000;
    }


    public function clearCart(Request $request)
    {
        session()->forget('cart');
        return response()->json(['success' => true]);
    }


    /**
     * Ajouter un produit à la liste de souhaits
     */
    public function addToWishlist($productId)
    {
        $allProducts = collect(config('loja_products'));
        $product = $allProducts->firstWhere('id', $productId);

        if (!$product) {
            return redirect()->back()->with('error', 'Produto não encontrado.');
        }

        // Logique pour ajouter à la liste de souhaits
        $wishlist = session()->get('wishlist', []);

        if (!in_array($productId, $wishlist)) {
            $wishlist[] = $productId;
            session()->put('wishlist', $wishlist);

            return redirect()->back()->with('success', 'Produto adicionado à sua lista de desejos!');
        }

        return redirect()->back()->with('info', 'O produto já está na sua lista de desejos.');
    }


    public function getMiniCartHtml()
    {
        try {
            $cart = session()->get('cart', []);
            $totalItems = 0;
            $totalPrice = 0.00;

            foreach ($cart as $item) {
                $itemQuantity = (int) ($item['quantity'] ?? 0);
                $itemPrice = $this->cleanPrice($item['price'] ?? 0);

                $totalItems += $itemQuantity;
                $totalPrice += ($itemPrice * $itemQuantity);
            }

            // Formater le prix avec 3 décimales
            $formattedTotalPrice = number_format($totalPrice, 3, ',', ' ');

            // Générer le HTML du mini-panier (desktop et mobile)
            $desktopHtml = '';
            $mobileHtml = '';

            if (empty($cart)) {
                // ============ VERSION DESKTOP (vide) ============
                $desktopHtml = '
            <div class="mcart-border">
                <ul class="cart_empty">
                    <li><span>O seu carrinho está vazio</span></li>
                    <li class="total">
                        <a class="button wc-continue" href="' . route('loja') . '">
                            Continuar a comprar
                            <i class="tb-icon tb-icon-angle-right"></i>
                        </a>
                    </li>
                </ul>
                <div class="clearfix"></div>
            </div>';

                // ============ VERSION MOBILE (vide) ============
                $mobileHtml = '
            <div class="mcart-border">
                <ul class="cart_empty">
                    <li><span>O seu carrinho está vazio</span></li>
                    <li class="total">
                        <a class="button wc-continue" href="' . route('loja') . '">
                            Continuar a comprar
                            <i class="tb-icon tb-icon-angle-right"></i>
                        </a>
                    </li>
                </ul>
                <div class="clearfix"></div>
            </div>';
            } else {
                // ============ VERSION DESKTOP (avec produits) ============
                $desktopProductsHtml = '';
                foreach ($cart as $productId => $item) {
                    $itemPrice = $this->cleanPrice($item['price'] ?? 0);
                    $itemQuantity = (int) ($item['quantity'] ?? 0);
                    $itemTotal = $itemPrice * $itemQuantity;
                    $formattedItemPrice = number_format($itemPrice, 3, ',', ' ');
                    $formattedItemTotal = number_format($itemTotal, 3, ',', ' ');

                    $desktopProductsHtml .= '
                <li class="mini-cart-item mini_cart_item">
                    <div class="product-image">
                        <a class="image" href="' . route('product.show', ['slug' => $item['slug'] ?? '']) . '">
                            <img width="100" height="100"
                                 src="' . (!empty($item['image']) ? asset($item['image']) : 'https://via.placeholder.com/100') . '"
                                 class="attachment-woocommerce_gallery_thumbnail size-woocommerce_gallery_thumbnail"
                                 alt="' . htmlspecialchars($item['title'] ?? 'Produto') . '"
                                 decoding="async">
                        </a>
                    </div>
                    <div class="product-details">
                        <a class="product-name" href="' . route('product.show', ['slug' => $item['slug'] ?? '']) . '">
                            <span>' . htmlspecialchars($item['title'] ?? 'Produto') . '</span>
                        </a>
                        <div class="group">
                            <div class="quantity-wrap">
                                <div class="quantity">
                                    <label class="screen-reader-text" for="quantity_desktop_' . $productId . '">
                                        Quantidade de ' . htmlspecialchars($item['title'] ?? 'Produto') . '
                                    </label>
                                    <span class="box">
                                        <div class="quantity-selector">
                                            <button type="button" class="quantity-minus" data-product-id="' . $productId . '">−
                                            </button>
                                            <input type="number" class="quantity-input"
                                               data-product-id="' . $productId . '"
                                               value="' . $itemQuantity . '"
                                                aria-label="Quantidade do produto"
                                              >

                                            <button type="button" class="quantity-plus" data-product-id="' . $productId . '">＋
                                            </button>
                                        </div>
                                    </span>
                                </div>
                            </div>
                            <span class="woocommerce-Price-amount amount">
                                <bdi>' . $formattedItemPrice . '&nbsp;
                                <span class="woocommerce-Price-currencySymbol">€</span></bdi>
                            </span>
                        </div>
                        <a role="button" href="javascript:void(0);"
                           class="remove mini-cart-remove"
                           data-product-id="' . $productId . '"
                           data-cart-type="desktop"
                           aria-label="Remover ' . htmlspecialchars($item['title'] ?? 'Produto') . ' do carrinho">
                            <i class="tb-icon tb-icon-trash"></i>
                        </a>
                    </div>
                </li>';
                }

                $desktopHtml = '
            <div class="mcart-border">
                <ul class="cart_list product_list_widget p-0">' . $desktopProductsHtml . '</ul>
                <div class="group-button">
                    <p class="total">
                        <strong>Subtotal:</strong>
                        <span class="woocommerce-Price-amount amount">
                            <bdi>' . $formattedTotalPrice . '&nbsp;
                            <span class="woocommerce-Price-currencySymbol">€</span></bdi>
                        </span>
                    </p>
                    <p class="buttons">
                        <a href="' . route('carrinho') . '" class="button view-cart">Ver carrinho</a>
                        <a href="'. route('checkout'). '" class="button checkout">Finalizar compra</a>
                    </p>
                </div>
                <div class="clearfix"></div>
            </div>';

                // ============ VERSION MOBILE (avec produits) ============
                $mobileProductsHtml = '';
                foreach ($cart as $productId => $item) {
                    $itemPrice = $this->cleanPrice($item['price'] ?? 0);
                    $itemQuantity = (int) ($item['quantity'] ?? 0);
                    $itemTotal = $itemPrice * $itemQuantity;
                    $formattedItemPrice = number_format($itemPrice, 3, ',', ' ');
                    $formattedItemTotal = number_format($itemTotal, 3, ',', ' ');

                    $mobileProductsHtml .= '
                <li class="mini-cart-item mini_cart_item">
                    <div class="product-image">
                        <a class="image" href="' . route('product.show', ['slug' => $item['slug'] ?? '']) . '">
                            <img width="100" height="100"
                                 src="' . (!empty($item['image']) ? asset($item['image']) : 'https://via.placeholder.com/100') . '"
                                 class="attachment-woocommerce_gallery_thumbnail size-woocommerce_gallery_thumbnail"
                                 alt="' . htmlspecialchars($item['title'] ?? 'Produto') . '"
                                 decoding="async">
                        </a>
                    </div>
                    <div class="product-details">
                        <a class="product-name" href="' . route('product.show', ['slug' => $item['slug'] ?? '']) . '">
                            <span>' . htmlspecialchars($item['title'] ?? 'Produto') . '</span>
                        </a>
                        <div class="group">
                            <div class="quantity-wrap">
                                <div class="quantity">
                                    <label class="screen-reader-text" for="quantity_mobile_' . $productId . '">
                                        Quantidade de ' . htmlspecialchars($item['title'] ?? 'Produto') . '
                                    </label>
                                    <span class="box">
                                         <div class="quantity-selector">
                                            <button type="button" class="quantity-minus" data-product-id="' . $productId . '">−
                                            </button>
                                            <input type="number" class="quantity-input"
                                               data-product-id="' . $productId . '"
                                               value="' . $itemQuantity . '"
                                                aria-label="Quantidade do produto"
                                              >

                                            <button type="button" class="quantity-plus" data-product-id="' . $productId . '">＋
                                            </button>
                                        </div>
                                    </span>
                                </div>
                            </div>
                            <span class="woocommerce-Price-amount amount">
                                <bdi>' . $formattedItemPrice . '&nbsp;
                                <span class="woocommerce-Price-currencySymbol">€</span></bdi>
                            </span>
                        </div>
                        <a role="button" href="javascript:void(0);"
                           class="remove mini-cart-remove"
                           data-product-id="' . $productId . '"
                           data-cart-type="mobile"
                           aria-label="Remover ' . htmlspecialchars($item['title'] ?? 'Produto') . ' do carrinho">
                            <i class="tb-icon tb-icon-trash"></i>
                        </a>
                    </div>
                </li>';
                }

                $mobileHtml = '
            <div class="mcart-border">
                <ul class="cart_list product_list_widget p-0" >' . $mobileProductsHtml . '</ul>
                <div class="group-button">
                    <p class="total">
                        <strong>Subtotal:</strong>
                        <span class="woocommerce-Price-amount amount">
                            <bdi>' . $formattedTotalPrice . '&nbsp;
                            <span class="woocommerce-Price-currencySymbol">€</span></bdi>
                        </span>
                    </p>
                    <p class="buttons">
                        <a href="' . route('carrinho') . '" class="button view-cart">Ver carrinho</a>
                        <a href="'. route('checkout'). '" class="button checkout">Finalizar compra</a>
                    </p>
                </div>
                <div class="clearfix"></div>
            </div>';
            }

            return response()->json([
                'success' => true,
                'desktop_html' => $desktopHtml,
                'mobile_html' => $mobileHtml,
                'totalItems' => $totalItems,
                'totalPrice' => $totalPrice,
                'formattedTotalPrice' => $formattedTotalPrice,
                'isEmpty' => empty($cart)
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur getMiniCartHtml: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'desktop_html' => '<div class="mcart-border"><p>Erro ao carregar o carrinho</p></div>',
                'mobile_html' => '<div class="mcart-border"><p>Erro ao carregar o carrinho</p></div>'
            ]);
        }
    }
    public function listaDeDesejos()
    {
        $wishlist = session()->get('wishlist', []);
        $allProducts = collect(config('loja_products', []));

        $wishlistProducts = [];
        foreach (array_keys($wishlist) as $productId) {
            $product = $allProducts->firstWhere('id', $productId);
            if ($product) {
                $wishlistProducts[] = $product;
            }
        }

        return view('lista-de-desejos', [
            'wishlistProducts' => $wishlistProducts,
        ]);
    }




    public function sobreNos()
    {
        return view('pages.sobre-nos');
    }

    public function avisosLegais()
    {
        return view('pages.avisos-legais');
    }

    public function contacto()
    {
        return view('pages.contacto');
    }

    public function politicaDePrivacidade()
    {
        return view('pages.politica-de-privacidade');
    }

    public function condicoesGeraisGeVendaCgv()
    {
        return view('pages.condicoes-gerais-de-venda-cgv');
    }

    public function termosCondicoesGeraisDeUtilizacaoTcg()
    {
        return view('pages.termos-e-condicoes-gerais-de-utilizacao-tcg');
    }

    public function politicaDeEntrega()
    {
        return view('pages.politica-de-entrega');
    }

    public function politicaDeReembolso()
    {
        return view('pages.politica-de-reembolso');
    }

    public function politicaDePagamento()
    {
        return view('pages.politica-de-pagamento');
    }

    public function mapaDoSite()
    {
        $products = collect(config('loja_products', []))
            ->filter(function ($p) {
                if (empty($p['slug'])) {
                    return false;
                }

                $price = (float) str_replace([',', ' '], '', (string) ($p['price'] ?? 0));

                return $price > 0;
            })
            ->unique(fn ($p) => $p['canonical_slug'] ?? $p['slug'])
            ->sortBy('title')
            ->values();

        return view('pages.mapa-do-site', compact('products'));
    }

    public function finalizacaoDeCompra()
    {
        return view('finalizacao-de-compra');
    }

}
