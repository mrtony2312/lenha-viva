<?php

use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\WishlistController;
use App\Support\CategoryLabels;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Portuguese URLs
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/loja', [HomeController::class, 'loja'])->name('loja');
Route::get('/carrinho', [HomeController::class, 'carrinho'])->name('carrinho');
Route::get('/lista-de-desejos', [HomeController::class, 'listaDeDesejos'])->name('lista-de-desejos');

Route::get('/produto/vista-rapida/{id}', [HomeController::class, 'quickView'])->name('product.quickview');
Route::get('/produto/{slug}', [HomeController::class, 'show'])->name('product.show');
Route::get('/categoria/{category}', [HomeController::class, 'category'])->name('category');

Route::get('/feed/google-merchant.xml', [FeedController::class, 'googleMerchant'])->name('feed.google-merchant');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');

Route::get('/sobre-nos', [HomeController::class, 'sobreNos'])->name('sobre-nos');
Route::get('/avisos-legais', [HomeController::class, 'avisosLegais'])->name('avisos-legais');
Route::get('/contacto', [HomeController::class, 'contacto'])->name('contacto');
Route::get('/politica-de-privacidade', [HomeController::class, 'politicaDePrivacidade'])->name('politica-de-privacidade');
Route::get('/condicoes-gerais-de-venda', [HomeController::class, 'condicoesGeraisGeVendaCgv'])->name('condicoes-gerais-de-venda-cgv');
Route::get('/termos-e-condicoes-de-utilizacao', [HomeController::class, 'termosCondicoesGeraisDeUtilizacaoTcg'])->name('termos-e-condicoes-gerais-de-utilizacao-tcg');
Route::get('/politica-de-entrega', [HomeController::class, 'politicaDeEntrega'])->name('politicaDeEntrega');
Route::get('/politica-de-reembolso', [HomeController::class, 'politicaDeReembolso'])->name('politicaDeReembolso');
Route::get('/politica-de-pagamento', [HomeController::class, 'politicaDePagamento'])->name('politicaDePagamento');
Route::get('/mapa-do-site', [HomeController::class, 'mapaDoSite'])->name('mapa-do-site');

Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

Route::post('/carrinho/adicionar', [HomeController::class, 'addToCart'])->name('cart.add');
Route::get('/carrinho/conteudo', [HomeController::class, 'getCartContent'])->name('cart.content');
Route::post('/carrinho/atualizar', [HomeController::class, 'updateCart'])->name('cart.update');
Route::post('/carrinho/remover', [HomeController::class, 'removeFromCart'])->name('cart.remove');
Route::post('/carrinho/limpar', [HomeController::class, 'clearCart'])->name('cart.clear');
Route::get('/carrinho/mini-cart-html', [HomeController::class, 'getMiniCartHtml'])->name('cart.mini.html');

Route::get('/finalizacao-de-compra', [CheckoutController::class, 'show'])->name('checkout');
Route::get('/finalizacao-de-compra/confirmacao', [CheckoutController::class, 'confirmation'])->name('checkout.confirmation');

Route::prefix('lista-desejos')->group(function () {
    Route::get('/', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/adicionar', [WishlistController::class, 'add'])->name('wishlist.add');
    Route::post('/remover', [WishlistController::class, 'remove'])->name('wishlist.remove');
});

Route::get('/refresh-csrf-token', function () {
    return response()->json([
        'token' => csrf_token(),
    ]);
})->middleware('web')->name('refresh');

/*
|--------------------------------------------------------------------------
| 301 redirects — old Spanish / English paths → Portuguese
|--------------------------------------------------------------------------
*/

$permanentRedirects = [
    '/tienda' => '/loja',
    '/carrito' => '/carrinho',
    '/lista-de-deseos' => '/lista-de-desejos',
    '/lista-deseos' => '/lista-desejos',
    '/sobre-nosotros' => '/sobre-nos',
    '/avisos-legales' => '/avisos-legais',
    '/politica-de-privacidad' => '/politica-de-privacidade',
    '/condiciones-generales-de-venta' => '/condicoes-gerais-de-venda',
    '/condicoes-gerais-de-venda-cgv' => '/condicoes-gerais-de-venda',
    '/terminos-y-condiciones-de-uso' => '/termos-e-condicoes-de-utilizacao',
    '/termos-e-condicoes-gerais-de-utilizacao-tcg' => '/termos-e-condicoes-de-utilizacao',
    '/politica-de-pago' => '/politica-de-pagamento',
    '/finalizacion-de-compra' => '/finalizacao-de-compra',
    '/finalizacion-de-compra/confirmacion' => '/finalizacao-de-compra/confirmacao',
];

foreach ($permanentRedirects as $from => $to) {
    Route::permanentRedirect($from, $to);
}

Route::permanentRedirect('/producto/vista-rapida/{id}', '/produto/vista-rapida/{id}');
Route::permanentRedirect('/product/quick-view/{id}', '/produto/vista-rapida/{id}');
Route::permanentRedirect('/producto/{slug}', '/produto/{slug}');
Route::permanentRedirect('/product/{slug}', '/produto/{slug}');

Route::get('/category/product-category/{category}', function (string $category) {
    $internal = CategoryLabels::fromUrlSlug($category) ?? $category;

    return redirect('/categoria/'.CategoryLabels::urlSlug($internal), 301);
});

/*
| Legacy Spanish AJAX endpoints. A POST body does not survive a redirect, so
| these keep pointing at the same controller actions instead.
*/
Route::post('/carrito/anadir', [HomeController::class, 'addToCart']);
Route::get('/carrito/contenido', [HomeController::class, 'getCartContent']);
Route::post('/carrito/actualizar', [HomeController::class, 'updateCart']);
Route::post('/carrito/eliminar', [HomeController::class, 'removeFromCart']);
Route::post('/carrito/vaciar', [HomeController::class, 'clearCart']);
Route::get('/carrito/mini-carrito-html', [HomeController::class, 'getMiniCartHtml']);
Route::post('/lista-deseos/anadir', [WishlistController::class, 'add']);
Route::post('/lista-deseos/eliminar', [WishlistController::class, 'remove']);
