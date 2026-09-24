<?php

namespace App\Console\Commands;

use App\Services\GoogleMerchantClient;
use App\Services\GoogleMerchantService;
use Illuminate\Console\Command;
use Throwable;

class MerchantCreateDataSource extends Command
{
    protected $signature = 'merchant:datasource
        {--name=Naturalenha API : Display name in Merchant Center}
        {--list : List existing data sources}
        {--ensure : Reuse existing primary API source or create once}';

    protected $description = 'Create, list or ensure a Google Merchant API primary product data source';

    public function handle(GoogleMerchantClient $client, GoogleMerchantService $merchant): int
    {
        if (! $client->configured()) {
            $this->error('Set GOOGLE_MERCHANT_ACCOUNT_ID and OAuth (GOOGLE_CLIENT_ID/SECRET/REFRESH_TOKEN) or service-account JSON.');

            return self::FAILURE;
        }

        try {
            if ($this->option('list')) {
                $payload = $client->listDataSources();
                $rows = collect($payload['dataSources'] ?? [])->map(function (array $source) {
                    $name = (string) ($source['name'] ?? '');
                    $id = preg_replace('#^.*/dataSources/#', '', $name);

                    return [
                        $id,
                        $source['displayName'] ?? '',
                        isset($source['primaryProductDataSource']) ? 'primary' : 'other',
                    ];
                })->all();

                if ($rows === []) {
                    $this->warn('No data sources yet.');
                } else {
                    $this->table(['ID', 'Name', 'Type'], $rows);
                }

                return self::SUCCESS;
            }

            if ($this->option('ensure') || filled(config('merchant.data_source_id'))) {
                $result = $merchant->ensureDataSource((string) $this->option('name'));
                $this->info(($result['created'] ? 'Created' : 'Reusing').': '.$result['name']);
                $this->line('Add/confirm in .env:');
                $this->line('GOOGLE_MERCHANT_DATA_SOURCE_ID='.$result['id']);

                return self::SUCCESS;
            }

            $created = $client->createPrimaryDataSource((string) $this->option('name'));
            $name = (string) ($created['name'] ?? '');
            $id = preg_replace('#^.*/dataSources/#', '', $name);

            $this->info('Data source created: '.$name);
            $this->line('Add this to .env:');
            $this->line('GOOGLE_MERCHANT_DATA_SOURCE_ID='.$id);
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
