<?php

namespace App\Portals\Customer\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class UserProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $this->resource;
        $name = is_object($user) && isset($user->name) ? (string) $user->name : 'Antoni Silha';

        if ($user instanceof User) {
            $initials = $user->initials();
        } else {
            $initials = Str::length($name) > 1
                ? Str::upper(Str::substr($name, 0, 1).Str::substr(explode(' ', $name)[1] ?? $name, 0, 1))
                : 'AS';
        }

        $email = is_object($user) && isset($user->email) ? (string) $user->email : '';
        $avatar = is_object($user) && isset($user->avatar) ? (string) $user->avatar : null;
        $googleId = is_object($user) && isset($user->google_id) ? (string) $user->google_id : null;
        $password = is_object($user) && isset($user->password) ? (string) $user->password : null;

        return [
            'id' => is_object($user) && isset($user->id) ? $user->id : null,
            'name' => $name,
            'email' => $email,
            'avatar' => $avatar,
            'initials' => $initials,
            'role' => 'User',
            'is_google_account' => ! empty($googleId),
            'has_password' => ! empty($password),
        ];
    }
}
