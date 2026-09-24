<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoogleMerchantProduct extends Model
{
    protected $table = 'google_merchant_products';

    protected $fillable = [
        'product_id',
        'offer_id',
        'google_product_name',
        'data_source_name',
        'sync_status',
        'last_synced_at',
        'last_error',
        'attempts',
        'last_issues',
        'operation',
        'duration_ms',
    ];

    protected function casts(): array
    {
        return [
            'last_synced_at' => 'datetime',
            'last_issues' => 'array',
            'attempts' => 'integer',
            'duration_ms' => 'integer',
        ];
    }

    public const STATUS_PENDING = 'pending';
    public const STATUS_SYNCED = 'synced';
    public const STATUS_ERROR = 'error';
    public const STATUS_DELETED = 'deleted';
}
