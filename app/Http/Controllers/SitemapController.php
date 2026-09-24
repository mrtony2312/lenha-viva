<?php

namespace App\Http\Controllers;

use App\Support\CategoryLabels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SitemapController extends Controller
{
    public function index()
    {
        try {
            $xml = Cache::remember('seo.sitemap.xml.v2', now()->addMinutes(30), function () {
                return $this->buildSitemapXml();
            });
        } catch (\Throwable $e) {
            Log::error('Sitemap generation failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            Cache::forget('seo.sitemap.xml.v2');

            abort(500, 'Sitemap temporarily unavailable');
        }

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=1800',
        ]);
    }

    public function robots()
    {
        $body = view('robots', [
            'sitemap' => route('sitemap'),
        ])->render();

        return response($body, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    private function buildSitemapXml(): string
    {
        $base = rtrim((string) config('app.url'), '/');
        $now = now()->toAtomString();

        $static = [
            ['loc' => route('home'), 'priority' => '1.0', 'changefreq' => 'daily'],
            ['loc' => route('loja'), 'priority' => '0.9', 'changefreq' => 'daily'],
            ['loc' => route('sobre-nos'), 'priority' => '0.6', 'changefreq' => 'monthly'],
            ['loc' => route('contacto'), 'priority' => '0.7', 'changefreq' => 'monthly'],
            ['loc' => route('politicaDeEntrega'), 'priority' => '0.6', 'changefreq' => 'monthly'],
            ['loc' => route('politicaDePagamento'), 'priority' => '0.5', 'changefreq' => 'monthly'],
            ['loc' => route('politicaDeReembolso'), 'priority' => '0.5', 'changefreq' => 'monthly'],
            ['loc' => route('politica-de-privacidade'), 'priority' => '0.4', 'changefreq' => 'yearly'],
            ['loc' => route('condicoes-gerais-de-venda-cgv'), 'priority' => '0.4', 'changefreq' => 'yearly'],
            ['loc' => route('termos-e-condicoes-gerais-de-utilizacao-tcg'), 'priority' => '0.4', 'changefreq' => 'yearly'],
            ['loc' => route('avisos-legais'), 'priority' => '0.4', 'changefreq' => 'yearly'],
            ['loc' => route('mapa-do-site'), 'priority' => '0.3', 'changefreq' => 'monthly'],
        ];

        $categories = collect(CategoryLabels::allUrlSlugs())
            ->unique()
            ->values()
            ->map(fn (string $slug) => [
                'loc' => route('category', ['category' => $slug]),
                'priority' => '0.8',
                'changefreq' => 'weekly',
            ]);

        $products = collect(config('loja_products', []))
            ->filter(function ($p) {
                if (empty($p['slug'])) {
                    return false;
                }

                $price = (float) str_replace([',', ' '], '', (string) ($p['price'] ?? 0));

                return $price > 0;
            })
            ->map(fn ($p) => [
                'loc' => route('product.show', ['slug' => $p['canonical_slug'] ?? $p['slug']]),
                'priority' => '0.7',
                'changefreq' => 'weekly',
            ])
            ->unique('loc')
            ->values();

        $urls = collect($static)->merge($categories)->merge($products);

        $xml = view('sitemap', [
            'urls' => $urls,
            'now' => $now,
            'base' => $base,
        ])->render();

        if (trim($xml) === '') {
            throw new \RuntimeException('Empty sitemap XML');
        }

        return $xml;
    }
}
