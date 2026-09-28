<?php

namespace App\Console\Commands;

use App\Support\MerchantCatalog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;

class MerchantFeedCheck extends Command
{
    protected $signature = 'merchant:feed-check
        {--live : Fetch the public production feed URL}
        {--write= : Optional path to write generated XML}';

    protected $description = 'Validate the Google Merchant RSS feed (item count, HTTPS URLs, required attributes)';

    public function handle(): int
    {
        if ($this->option('live')) {
            return $this->checkLive();
        }

        $base = rtrim((string) (config('merchant.public_base_url') ?: config('app.url')), '/');
        if (! str_starts_with($base, 'https://')) {
            $base = 'https://naturalenha.com';
        }
        URL::forceRootUrl($base);
        URL::forceScheme('https');

        $products = MerchantCatalog::eligibleProducts();
        $this->info('Eligible catalogue products: '.$products->count());

        $missing = 0;
        $badUrl = 0;
        $ids = [];

        foreach ($products as $product) {
            $input = MerchantCatalog::toProductInput($product);
            if ($input === null) {
                $missing++;
                continue;
            }

            $id = $input['offerId'];
            $ids[] = $id;
            $attr = $input['productAttributes'];

            foreach (['title', 'description', 'link', 'imageLink', 'brand', 'price'] as $field) {
                if (empty($attr[$field])) {
                    $this->error("Missing {$field} on {$id}");
                    $missing++;
                }
            }

            foreach (['link', 'imageLink'] as $urlField) {
                $url = (string) ($attr[$urlField] ?? '');
                if ($url !== '' && ! str_starts_with($url, 'https://')) {
                    $this->error("Non-HTTPS {$urlField} on {$id}: {$url}");
                    $badUrl++;
                }
                if (str_contains($url, 'localhost') || str_contains($url, '127.0.0.1')) {
                    $this->error("Localhost URL on {$id}: {$url}");
                    $badUrl++;
                }
            }
        }

        $dupes = collect($ids)->duplicates()->values();
        if ($dupes->isNotEmpty()) {
            $this->error('Duplicate offer IDs: '.$dupes->implode(', '));
        }

        $xml = app(\App\Http\Controllers\FeedController::class)
            ->googleMerchant()
            ->getContent();

        $itemCount = substr_count((string) $xml, '<item>');
        $this->line('Generated <item> count: '.$itemCount);
        $this->line('Stable IDs sample: '.collect($ids)->take(5)->implode(', '));

        if ($path = $this->option('write')) {
            file_put_contents($path, $xml);
            $this->info('Wrote '.$path);
        }

        // Anti 0-product guarantee checks
        if ($itemCount < 1) {
            $this->error('FAIL: feed would publish 0 products (Google would wipe the catalogue).');

            return self::FAILURE;
        }

        if ($itemCount !== $products->count()) {
            $this->warn("Item count ({$itemCount}) differs from eligible ({$products->count()}).");
        }

        if ($badUrl > 0 || $missing > 0 || $dupes->isNotEmpty()) {
            $this->error("Issues: missing={$missing} bad_url={$badUrl} dupes={$dupes->count()}");

            return self::FAILURE;
        }

        $this->info('Feed validation OK — safe for Merchant Center scheduled fetch.');
        $this->line('Register this URL as PRIMARY scheduled feed:');
        $this->line('  '.$base.'/feed/google-merchant.xml');

        return self::SUCCESS;
    }

    private function checkLive(): int
    {
        $url = 'https://naturalenha.com/feed/google-merchant.xml';
        $this->line('Fetching '.$url);

        $response = Http::timeout(60)->get($url);
        if (! $response->successful()) {
            $this->error('HTTP '.$response->status());

            return self::FAILURE;
        }

        $body = $response->body();
        $items = substr_count($body, '<item>');
        $hasNs = str_contains($body, 'xmlns:g="http://base.google.com/ns/1.0"');
        $localhost = str_contains($body, 'localhost') || str_contains($body, '127.0.0.1');

        $this->line('HTTP '.$response->status());
        $this->line('Items: '.$items);
        $this->line('xmlns:g: '.($hasNs ? 'yes' : 'NO'));
        $this->line('Header X-Merchant-Feed-Items: '.($response->header('X-Merchant-Feed-Items') ?: 'n/a'));

        if ($localhost) {
            $this->error('Feed contains localhost URLs');

            return self::FAILURE;
        }

        if (! $hasNs || $items < 1) {
            $this->error('Live feed invalid');

            return self::FAILURE;
        }

        $this->info('Live feed OK');

        return self::SUCCESS;
    }
}
