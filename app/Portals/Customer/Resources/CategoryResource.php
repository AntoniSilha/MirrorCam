<?php

namespace App\Portals\Customer\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class CategoryResource extends JsonResource
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
            ? ($this->resource->id_kategori ?? $this->resource->id ?? 1)
            : ($this->resource['id_kategori'] ?? $this->resource['id'] ?? 1);

        $name = $isModel
            ? ($this->resource->nama_kategori ?? $this->resource->name ?? 'Kategori')
            : ($this->resource['nama_kategori'] ?? $this->resource['name'] ?? 'Kategori');

        $slug = $isModel
            ? ($this->resource->slug ?? Str::slug($name))
            : ($this->resource['slug'] ?? Str::slug($name));

        $icon = $isModel
            ? ($this->resource->icon ?? 'camera')
            : ($this->resource['icon'] ?? 'camera');

        $description = $isModel
            ? ($this->resource->deskripsi ?? '')
            : ($this->resource['deskripsi'] ?? '');

        $productCount = $isModel
            ? ($this->resource->total_produk ?? ($this->resource->products_count ?? 12))
            : ($this->resource['total_produk'] ?? ($this->resource['product_count'] ?? 12));

        return [
            'id' => $id,
            'name' => $name,
            'slug' => $slug,
            'icon' => $icon,
            'description' => $description,
            'product_count' => $productCount,
        ];
    }
}
