<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'shipping' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'reference';
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->shipping_first_name.' '.$this->shipping_last_name);
    }

    public function money(float|int|string|null $value): string
    {
        return format_eur($value);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending' => 'Pendente',
            'processing' => 'Em processamento',
            'shipped' => 'Enviada',
            'completed' => 'Concluída',
            'cancelled' => 'Cancelada',
            default => ucfirst((string) $this->status),
        };
    }

    public function paymentMethodLabel(): string
    {
        return match ($this->payment_method) {
            'bacs', 'transferencia', 'vorkasse' => 'Transferência bancária',
            default => (string) $this->payment_method,
        };
    }
}
