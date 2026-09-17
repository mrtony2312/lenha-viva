<?php

namespace App\Console\Commands;

use App\Services\GoogleMerchantClient;
use Illuminate\Console\Command;
use Throwable;

class MerchantCreateDataSource extends Command
{
    protected $signature = 'merchant:datasource
        {--name=Naturalenha API : Display name in Merchant Center}
        {--list : List existing data sources instead of creating one}';

    protected $description = 'Create or list a Google Merchant API primary product data source';

    public function handle(GoogleMerchantClient $merchant): int
    {
        if (! $merchant->configured()) {
            $this->error('Set GOOGLE_MERCHANT_ACCOUNT_ID and place the service-account JSON at GOOGLE_MERCHANT_CREDENTIALS.');

            return self::FAILURE;
        }

        try {
            if ($this->option('list')) {
                $payload = $merchant->listDataSources();
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

            $created = $merchant->createPrimaryDataSource((string) $this->option('name'));
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
