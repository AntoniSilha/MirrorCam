<?php

namespace App\Portals\Customer\Resources;

use App\Models\DetailPemesananTenant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $isModel = ! is_array($this->resource);

        if ($this->resource instanceof DetailPemesananTenant) {
            $id = $this->resource->id_pemesanan;
            $bookingCode = $this->resource->sub_order_eksternal ?? "MC-" . str_pad((string) $id, 4, '0', STR_PAD_LEFT);
            $productName = $this->resource->product_name;
            $gearImage = $this->resource->gear_image;
            $tenantName = $this->resource->tenant_name;
            $tenantLocation = $this->resource->tenant_location;
            $tenantDisplay = $this->resource->tenant_display;
            $tenantPhone = $this->resource->tenant?->whatsapp_resmi ?? '6281234567890';
            $startDate = $this->resource->start_date;
            $endDate = $this->resource->end_date;
            $returnDeadline = $this->resource->return_deadline;
            $totalAmount = (float) $this->resource->total_harga;
            $formattedTotal = $this->resource->formatted_total;
            $status = strtolower(str_replace(' ', '_', $this->resource->status_sewa ?? 'sedang_disewa'));
            $statusLabel = $this->resource->status_label;
            $statusStyle = $this->resource->status_style;

            return [
                'id' => $id,
                'booking_code' => $bookingCode,
                'product_name' => $productName,
                'gear_image' => $gearImage,
                'tenant_name' => $tenantName,
                'tenant_location' => $tenantLocation,
                'tenant_display' => $tenantDisplay,
                'status' => $status,
                'status_label' => $statusLabel,
                'status_style' => $statusStyle,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'return_deadline' => $returnDeadline,
                'total_tagihan' => $totalAmount,
                'formatted_total' => $formattedTotal,
                'tenant_phone' => $tenantPhone,
            ];
        }

        $status = $isModel ? ($this->resource->status ?? 'booking_aktif') : ($this->resource['status'] ?? 'booking_aktif');
        $statusStyle = $isModel
            ? ($this->resource->status_style ?? [
                'bg' => '#dcfce7',
                'text' => '#16a34a',
                'border' => '#bbf7d0',
                'label' => 'Booking Aktif',
            ])
            : ($this->resource['status_style'] ?? [
                'bg' => '#dcfce7',
                'text' => '#16a34a',
                'border' => '#bbf7d0',
                'label' => 'Booking Aktif',
            ]);

        $tenant = $isModel ? ($this->resource->tenant ?? null) : null;

        $tenantName = $tenant !== null
            ? ($tenant->name ?? $tenant->nama_toko ?? 'Indonesia Battery')
            : ($this->resource['tenant_name'] ?? 'Indonesia Battery');

        $tenantLocation = $tenant !== null
            ? ($tenant->location ?? $tenant->kota ?? 'Banyuwangi')
            : ($this->resource['tenant_location'] ?? 'Banyuwangi');

        $tenantPhone = $tenant !== null
            ? ($tenant->phone ?? $tenant->whatsapp_resmi ?? '6281234567890')
            : ($this->resource['tenant_phone'] ?? '6281234567890');

        $startDate = $isModel
            ? ($this->resource->start_date ? (is_string($this->resource->start_date) ? $this->resource->start_date : $this->resource->start_date->translatedFormat('d M Y')) : '24 Okt 2025')
            : ($this->resource['start_date'] ?? '24 Okt 2025');

        $endDate = $isModel
            ? ($this->resource->end_date ? (is_string($this->resource->end_date) ? $this->resource->end_date : $this->resource->end_date->translatedFormat('d M Y')) : '27 Okt 2025')
            : ($this->resource['end_date'] ?? '27 Okt 2025');

        $returnDeadline = $isModel
            ? ($this->resource->return_deadline_note ?? $this->resource->return_deadline ?? "{$endDate} (Maks. 18:00)")
            : ($this->resource['return_deadline'] ?? "{$endDate} (Maks. 18:00)");

        $totalAmount = $isModel
            ? ($this->resource->total_amount ?? $this->resource->total_harga ?? 2400000)
            : ($this->resource['total_tagihan'] ?? $this->resource['total_harga'] ?? 2400000);

        $formattedTotal = $isModel
            ? ($this->resource->formatted_total ?? 'Rp '.number_format((float) $totalAmount, 0, ',', '.'))
            : ($this->resource['formatted_total'] ?? 'Rp '.number_format((float) $totalAmount, 0, ',', '.'));

        return [
            'id' => $isModel ? ($this->resource->id ?? $this->resource->id_pemesanan ?? 1) : ($this->resource['id'] ?? 1),
            'booking_code' => $isModel ? ($this->resource->booking_code ?? $this->resource->sub_order_eksternal ?? 'MC-001') : ($this->resource['booking_code'] ?? 'MC-001'),
            'product_name' => $isModel ? ($this->resource->product_name ?? 'Sony FX3 Cinema Line + FE 24–70mm GM II') : ($this->resource['product_name'] ?? 'Sony FX3 Cinema Line + FE 24–70mm GM II'),
            'gear_image' => $isModel ? ($this->resource->gear_image ?? '/images/gear/placeholder.jpg') : ($this->resource['gear_image'] ?? '/images/gear/placeholder.jpg'),
            'tenant_name' => $tenantName,
            'tenant_location' => $tenantLocation,
            'tenant_display' => "{$tenantName}, {$tenantLocation}",
            'status' => $status,
            'status_label' => $isModel ? ($this->resource->status_label ?? 'Booking Aktif') : ($this->resource['status_label'] ?? 'Booking Aktif'),
            'status_style' => $statusStyle,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'return_deadline' => $returnDeadline,
            'total_tagihan' => $totalAmount,
            'formatted_total' => $formattedTotal,
            'tenant_phone' => $tenantPhone,
        ];
    }
}
