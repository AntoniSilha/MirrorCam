<?php

namespace App\Portals\Customer\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class TenantSummaryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $isModel = ! is_array($this->resource);

        $id = $isModel
            ? ($this->resource->id_tenant ?? $this->resource->id ?? 1)
            : ($this->resource['id_tenant'] ?? $this->resource['id'] ?? 1);

        $name = $isModel
            ? ($this->resource->nama_toko ?? $this->resource->name ?? 'Tenant Partner')
            : ($this->resource['nama_toko'] ?? $this->resource['name'] ?? 'Tenant Partner');

        $subtitle = $isModel
            ? ($this->resource->category_label ?? $this->resource->subtitle ?? 'Rental & Service')
            : ($this->resource['category_label'] ?? $this->resource['subtitle'] ?? 'Rental & Service');

        $logo = $isModel
            ? ($this->resource->url_logo ?? null)
            : ($this->resource['url_logo'] ?? null);

        $bio = $isModel
            ? ($this->resource->bio_deskripsi ?? $this->resource->bio ?? '')
            : ($this->resource['bio_deskripsi'] ?? $this->resource['bio'] ?? '');

        $rating = $isModel
            ? (float) ($this->resource->rating ?? 4.9)
            : (float) ($this->resource['rating'] ?? 4.9);

        $unitReady = $isModel
            ? (int) ($this->resource->unit_ready ?? 48)
            : (int) ($this->resource['unit_ready'] ?? 48);

        $location = $isModel
            ? ($this->resource->kota ?? $this->resource->location ?? 'Banyuwangi')
            : ($this->resource['kota'] ?? $this->resource['location'] ?? 'Banyuwangi');

        $badge = $isModel
            ? ($this->resource->badge_text ?? 'OFFICIAL PARTNER')
            : ($this->resource['badge_text'] ?? 'OFFICIAL PARTNER');

        $isOfficial = $isModel
            ? ($this->resource->is_official ?? true)
            : ($this->resource['is_official'] ?? true);

        // Derive initials for logo fallback
        $words = explode(' ', trim($name));
        $initials = count($words) >= 2
            ? strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1))
            : strtoupper(substr($name, 0, 2));

        return [
            'id' => $id,
            'name' => $name,
            'subtitle' => $subtitle,
            'logo' => $logo,
            'initials' => $initials,
            'bio' => $bio,
            'rating' => number_format($rating, 1, '.', ''),
            'unit_ready' => $unitReady,
            'unit_ready_text' => "{$unitReady} Unit Ready",
            'location' => $location,
            'badge' => $badge,
            'is_official' => $isOfficial,
            'whatsapp' => $isModel ? ($this->resource->whatsapp_resmi ?? '6281234567890') : ($this->resource['whatsapp_resmi'] ?? '6281234567890'),
        ];
    }
}
