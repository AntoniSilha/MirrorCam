<?php

namespace App\Services;

use App\Models\Produk;
use Illuminate\Support\Collection;

class CartService
{
    private const SESSION_KEY = 'mirrorcam_cart';

    /**
     * Get cart items and remove entries whose product no longer exists.
     *
     * @return Collection<string, array<string, mixed>>
     */
    public function items(): Collection
    {
        $items = collect(session(self::SESSION_KEY, []))
            ->filter(fn ($item) => is_array($item) && ! empty($item['id_produk']));

        if ($items->isEmpty()) {
            return $items;
        }

        $existingProductIds = Produk::whereIn('id_produk', $items->pluck('id_produk')->unique()->all())
            ->pluck('id_produk')
            ->map(fn ($id) => (int) $id)
            ->all();

        $validItems = $items->filter(
            fn ($item) => in_array((int) $item['id_produk'], $existingProductIds, true)
        );

        if ($validItems->count() !== $items->count()) {
            session([self::SESSION_KEY => $validItems->all()]);
        }

        return $validItems;
    }

    /**
     * Total quantity of items currently in the cart.
     */
    public function count(): int
    {
        return (int) $this->items()->sum(
            fn ($item) => max(1, (int) ($item['qty'] ?? 1))
        );
    }
}
