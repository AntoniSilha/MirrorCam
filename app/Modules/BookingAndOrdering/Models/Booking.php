<?php

namespace App\Modules\BookingAndOrdering\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int $tenant_id
 * @property string $product_name
 * @property string|null $gear_image
 * @property string $status
 * @property Carbon|null $start_date
 * @property Carbon|null $end_date
 * @property string|null $return_deadline_note
 * @property string|float $total_amount
 * @property string|null $booking_code
 * @property string|null $notes
 * @property-read TenantBooking|null $tenant
 * @property-read User $user
 * @property-read array<string, string> $status_style
 * @property-read string $status_label
 * @property-read string $formatted_total
 */
class Booking extends Model
{
    protected $table = 'bookings';

    protected $fillable = [
        'user_id',
        'tenant_id',
        'product_name',
        'gear_image',
        'status',
        'start_date',
        'end_date',
        'return_deadline_note',
        'total_amount',
        'booking_code',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'total_amount' => 'decimal:2',
    ];

    /**
     * Customer User relation
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Tenant relation
     *
     * @return BelongsTo<TenantBooking, $this>
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(TenantBooking::class, 'tenant_id');
    }

    /**
     * Get human readable status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'booking_aktif', 'active' => 'Booking Aktif',
            'selesai', 'completed' => 'Selesai',
            'terlambat', 'overdue' => 'Terlambat',
            'selesaikan_pembayaran', 'pending_payment' => 'Selesaikan Pembayaran',
            default => ucfirst(str_replace('_', ' ', $this->status ?? 'Booking Aktif')),
        };
    }

    /**
     * Get status badge styling matching MirrorCam Design System
     *
     * @return array<string, string>
     */
    public function getStatusStyleAttribute(): array
    {
        return match ($this->status) {
            'booking_aktif', 'active' => [
                'bg' => '#dcfce7',
                'text' => '#16a34a',
                'border' => '#bbf7d0',
                'label' => 'Booking Aktif',
            ],
            'selesai', 'completed' => [
                'bg' => '#fff700',
                'text' => '#000000',
                'border' => '#facc15',
                'label' => 'Selesai',
            ],
            'terlambat', 'overdue' => [
                'bg' => '#ffdad6',
                'text' => '#ba1a1a',
                'border' => '#fca5a5',
                'label' => 'Terlambat',
            ],
            'selesaikan_pembayaran', 'pending_payment' => [
                'bg' => '#ffedd5',
                'text' => '#c2410c',
                'border' => '#fed7aa',
                'label' => 'Selesaikan Pembayaran',
            ],
            default => [
                'bg' => '#dcfce7',
                'text' => '#16a34a',
                'border' => '#bbf7d0',
                'label' => 'Booking Aktif',
            ],
        };
    }

    /**
     * Format currency in IDR
     */
    public function getFormattedTotalAttribute(): string
    {
        return 'Rp '.number_format((float) ($this->total_amount ?? 2400000), 0, ',', '.');
    }
}
