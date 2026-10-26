<?php

namespace App\Portals\Customer\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CatalogItemResource extends JsonResource
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
            ? ($this->resource->id_produk ?? $this->resource->id ?? 1)
            : ($this->resource['id_produk'] ?? $this->resource['id'] ?? 1);

        $name = $isModel
            ? ($this->resource->nama_produk ?? $this->resource->name ?? 'Gear Kamera')
            : ($this->resource['nama_produk'] ?? $this->resource['name'] ?? 'Gear Kamera');

        $tenantName = $isModel
            ? ($this->resource->tenant?->nama_toko ?? $this->resource->tenant_name ?? 'Mitra Resmi')
            : ($this->resource['tenant_name'] ?? 'Mitra Resmi');

        $categoryName = $isModel
            ? ($this->resource->category?->nama_kategori ?? $this->resource->category_name ?? 'Kamera')
            : ($this->resource['category_name'] ?? 'Kamera');

        $price = $isModel
            ? (float) ($this->resource->harga_sewa ?? 250000)
            : (float) ($this->resource['harga_sewa'] ?? 250000);

        $priceUnit = $isModel
            ? ($this->resource->price_unit ?? 'hari')
            : ($this->resource['price_unit'] ?? 'hari');

        $formattedPrice = $isModel
            ? ($this->resource->formatted_price ?? ('Rp ' . number_format($price, 0, ',', '.') . ' / ' . $priceUnit))
            : ($this->resource['formatted_price'] ?? ('Rp ' . number_format($price, 0, ',', '.') . ' / ' . $priceUnit));

        $image = $isModel
            ? ($this->resource->primary_image ?? $this->resource->thumbnail_url ?? '/images/gear/placeholder.jpg')
            : ($this->resource['image'] ?? $this->resource['thumbnail_url'] ?? '/images/gear/placeholder.jpg');

        $rating = $isModel
            ? (float) ($this->resource->rating ?? 4.9)
            : (float) ($this->resource['rating'] ?? 4.9);

        $reviews = $isModel
            ? (int) ($this->resource->review_count ?? 24)
            : (int) ($this->resource['review_count'] ?? $this->resource['reviews'] ?? 24);

        $stock = $isModel
            ? (int) ($this->resource->total_stok_unit ?? 1)
            : (int) ($this->resource['total_stok_unit'] ?? $this->resource['stock'] ?? 1);

        $isAvailable = $stock > 0;

        return [
            'id' => $id,
            'name' => $name,
            'image' => $image,
            'tenant_name' => $tenantName,
            'category_name' => $categoryName,
            'price' => $price,
            'price_unit' => $priceUnit,
            'formatted_price' => $formattedPrice,
            'rating' => number_format($rating, 1, '.', ''),
            'review_count' => $reviews,
            'rating_text' => number_format($rating, 1, '.', '') . " ({$reviews} ulasan)",
            'stock' => $stock,
            'is_available' => $isAvailable,
            'status_label' => $isAvailable ? 'Tersedia' : 'Habis Disewa',
            'status_style' => $isAvailable
                ? ['bg' => '#dcfce7', 'text' => '#16a34a', 'label' => 'Tersedia']
                : ['bg' => '#ffdad6', 'text' => '#ba1a1a', 'label' => 'Habis'],
        ];
    }
}
