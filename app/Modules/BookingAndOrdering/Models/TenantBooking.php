<?php

namespace App\Modules\BookingAndOrdering\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string|null $location
 * @property string|null $address
 * @property string|null $phone
 * @property string|null $whatsapp_number
 * @property int|null $user_id
 * @property-read User|null $user
 * @property-read string $display_name
 */
class TenantBooking extends Model
{
    protected $table = 'tenants';

    protected $fillable = [
        'name',
        'location',
        'address',
        'phone',
        'whatsapp_number',
        'user_id',
    ];

    /**
     * Tenant owner relation
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Tenant bookings relation
     *
     * @return HasMany<Booking, $this>
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'tenant_id');
    }

    /**
     * Get formatted display name with location
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->location ? "{$this->name}, {$this->location}" : $this->name;
    }
}
