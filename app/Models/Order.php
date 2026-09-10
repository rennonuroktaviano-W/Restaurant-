<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    public const TYPE_DINE_IN = 'dine_in';

    public const TYPE_TAKE_AWAY = 'take_away';

    public const TYPE_ROOM_SERVICE = 'room_service';

    public const STATUS_NEW = 'new';

    public const STATUS_ACCEPTED = 'accepted';

    public const STATUS_COOKING = 'cooking';

    public const STATUS_READY = 'ready';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    public const PAYMENT_PENDING = 'pending';

    public const PAYMENT_PAID = 'paid';

    public const PAYMENT_FAILED = 'failed';

    public const PAYMENT_EXPIRED = 'expired';

    public const PAYMENT_REFUNDED = 'refunded';

    public const PAYMENT_PARTIALLY_REFUNDED = 'partially_refunded';

    public static array $flowLabels = [
        self::STATUS_NEW => 'Menunggu',
        self::STATUS_ACCEPTED => 'Diterima',
        self::STATUS_COOKING => 'Dimasak',
        self::STATUS_READY => 'Siap Disajikan',
        self::STATUS_COMPLETED => 'Selesai',
        self::STATUS_CANCELLED => 'Dibatalkan',
    ];

    public static array $orderFlow = [
        self::STATUS_NEW => [self::STATUS_ACCEPTED, self::STATUS_CANCELLED],
        self::STATUS_ACCEPTED => [self::STATUS_COOKING, self::STATUS_CANCELLED],
        self::STATUS_COOKING => [self::STATUS_READY, self::STATUS_CANCELLED],
        self::STATUS_READY => [self::STATUS_COMPLETED, self::STATUS_CANCELLED],
        self::STATUS_COMPLETED => [],
        self::STATUS_CANCELLED => [],
    ];

    protected $fillable = [
        'order_number', 'order_type', 'area_id', 'table_id', 'room_id',
        'customer_name', 'customer_phone', 'notes',
        'subtotal', 'discount_amount', 'tax_amount', 'service_charge_amount', 'grand_total',
        'order_status', 'payment_status', 'created_by', 'idempotency_key',
        'ordered_at', 'accepted_at', 'cooking_at', 'ready_at', 'completed_at', 'cancelled_at',
        'has_kitchen_items',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'service_charge_amount' => 'decimal:2',
            'grand_total' => 'decimal:2',
            'ordered_at' => 'datetime',
            'accepted_at' => 'datetime',
            'cooking_at' => 'datetime',
            'ready_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'has_kitchen_items' => 'boolean',
        ];
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function table()
    {
        return $this->belongsTo(DiningTable::class, 'table_id');
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function refunds()
    {
        return $this->hasMany(Refund::class);
    }

    public function statusHistories()
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function tokens()
    {
        return $this->hasMany(OrderToken::class);
    }

    public function cancellation()
    {
        return $this->hasOne(OrderCancellation::class);
    }

    public function locationLabel(): string
    {
        return match ($this->order_type) {
            self::TYPE_DINE_IN => $this->table?->name ?? $this->table?->table_number ?? 'Dine-in',
            self::TYPE_ROOM_SERVICE => $this->room?->name ?? $this->room?->room_number ?? 'Room service',
            default => 'Take away',
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this->order_status, [self::STATUS_COMPLETED, self::STATUS_CANCELLED], true);
    }
}
