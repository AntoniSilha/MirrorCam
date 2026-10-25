<?php

namespace App\Portals\Customer\Resources;

use App\Modules\BookingAndOrdering\Models\TenantBooking;
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

        $status = $isModel ? $this->resource->status : ($this->resource['status'] ?? 'booking_aktif');
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

        /** @var TenantBooking|null $tenant */
        $tenant = $isModel ? $this->resource->tenant : null;

        $tenantName = $tenant !== null
            ? $tenant->name
            : ($this->resource['tenant_name'] ?? 'Indonesia Galery');

        $tenantLocation = $tenant !== null
            ? ($tenant->location ?? 'Kebon Jeruk')
            : ($this->resource['tenant_location'] ?? 'Kebon Jeruk');

        $tenantPhone = $tenant !== null
            ? ($tenant->phone ?? '6281234567890')
            : ($this->resource['tenant_phone'] ?? '6281234567890');

        $startDate = $isModel
            ? ($this->resource->start_date ? $this->resource->start_date->translatedFormat('d M Y') : '24 Okt 2025')
            : ($this->resource['start_date'] ?? '24 Okt 2025');

        $endDate = $isModel
            ? ($this->resource->end_date ? $this->resource->end_date->translatedFormat('d M Y') : '27 Okt 2025')
            : ($this->resource['end_date'] ?? '27 Okt 2025');

        $returnDeadline = $isModel
            ? ($this->resource->return_deadline_note ?? "{$endDate} (Maks. 18:00)")
            : ($this->resource['return_deadline'] ?? "{$endDate} (Maks. 18:00)");

        $totalAmount = $isModel
            ? ($this->resource->total_amount ?? 2400000)
            : ($this->resource['total_tagihan'] ?? 2400000);

        $formattedTotal = $isModel
            ? ($this->resource->formatted_total ?? 'Rp '.number_format((float) $totalAmount, 0, ',', '.'))
            : ($this->resource['formatted_total'] ?? 'Rp '.number_format((float) $totalAmount, 0, ',', '.'));

        return [
            'id' => $isModel ? $this->resource->id : ($this->resource['id'] ?? 1),
            'booking_code' => $isModel ? ($this->resource->booking_code ?? 'MC-001') : ($this->resource['booking_code'] ?? 'MC-001'),
            'product_name' => $isModel ? $this->resource->product_name : ($this->resource['product_name'] ?? 'Sony FX3 Cinema Line + FE 24–70mm GM II'),
            'gear_image' => $isModel ? ($this->resource->gear_image ?? '/images/gear/sony-fx3.jpg') : ($this->resource['gear_image'] ?? '/images/gear/sony-fx3.jpg'),
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
