<?php

namespace App\Portals\Customer\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            ? ($this->resource->reviews_avg_rating_nilai ?? null)
            : ($this->resource['reviews_avg_rating_nilai'] ?? $this->resource['rating'] ?? null);
        $reviewCount = $isModel
            ? (int) ($this->resource->reviews_count ?? 0)
            : (int) ($this->resource['reviews_count'] ?? 0);

        $unitReady = $isModel
            ? (int) ($this->resource->products_count ?? $this->resource->unit_ready ?? 0)
            : (int) ($this->resource['unit_ready'] ?? $this->resource['products_count'] ?? 0);

        $location = $isModel
            ? ($this->resource->kota ?? $this->resource->location ?? 'Indonesia')
            : ($this->resource['kota'] ?? $this->resource['location'] ?? 'Indonesia');

        $statusMitra = $isModel
            ? strtolower((string) ($this->resource->status_mitra ?? ''))
            : strtolower((string) ($this->resource['status_mitra'] ?? ''));
        $isOfficial = $statusMitra === 'verified';

        $badge = $isOfficial ? 'OFFICIAL PARTNER' : 'BELUM TERVERIFIKASI';

        // Derive initials for logo fallback
        $words = explode(' ', trim($name));
        $initials = count($words) >= 2
            ? strtoupper(substr($words[0], 0, 1).substr($words[1], 0, 1))
            : strtoupper(substr($name, 0, 2));

        return [
            'id' => $id,
            'name' => $name,
            'subtitle' => $subtitle,
            'logo' => $logo,
            'initials' => $initials,
            'bio' => $bio,
            'rating' => $reviewCount > 0 && $rating !== null ? number_format((float) $rating, 1, '.', '') : null,
            'review_count' => $reviewCount,
            'unit_ready' => $unitReady,
            'unit_ready_text' => "{$unitReady} Unit Ready",
            'location' => $location,
            'badge' => $badge,
            'is_official' => $isOfficial,
            'whatsapp' => $isModel ? ($this->resource->whatsapp_resmi ?? '') : ($this->resource['whatsapp_resmi'] ?? ''),
        ];
    }
}
