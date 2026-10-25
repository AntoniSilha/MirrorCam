<?php

namespace App\Modules\IdentityAndAccess\Models;

use App\Models\User as BaseUser;
use App\Modules\BookingAndOrdering\Models\Booking;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends BaseUser
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'avatar',
    ];

    /**
     * Check if user is registered via Google OAuth
     */
    public function isGoogleAccount(): bool
    {
        return ! empty($this->google_id);
    }

    /**
     * Check if user has a local password set
     */
    public function hasLocalPassword(): bool
    {
        return ! empty($this->password);
    }

    /**
     * Relation to Bookings
     *
     * @return HasMany<Booking, $this>
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'user_id');
    }
}
