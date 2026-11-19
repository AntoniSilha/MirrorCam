<?php

namespace App\Modules\TenantManagement\Concerns;

use Livewire\Attributes\Computed;

trait BuildsRevenueChart
{
    /**
     * Build SVG coordinates for the revenue trend chart.
     *
     * @return array<string, mixed>
     */
    #[Computed]
    public function chart(): array
    {
        $labels = $this->dashboard['chart']['labels'] ?: ['', ''];
        $values = array_map('floatval', $this->dashboard['chart']['values'] ?: [0, 0]);

        if (count($values) < 2) {
            $values = [0, 0];
            $labels = ['', ''];
        }

        $maxValue = max($values);
        $axisStep = 10_000_000;
        $axisMax = max($axisStep, (int) (ceil($maxValue / $axisStep) * $axisStep));

        $width = 640.0;
        $height = 250.0;
        $left = 56.0;
        $right = 24.0;
        $top = 16.0;
        $bottom = 30.0;
        $plotWidth = $width - $left - $right;
        $plotHeight = $height - $top - $bottom;
        $count = count($values);

        $points = [];

        foreach ($values as $index => $value) {
            $x = $left + ($index / ($count - 1)) * $plotWidth;
            $y = $top + (1 - min(1, max(0, $value / $axisMax))) * $plotHeight;

            $points[] = [
                'x' => round($x, 2),
                'y' => round($y, 2),
                'label' => $labels[$index] ?? '',
                'value' => $value,
            ];
        }

        $linePath = $this->smoothPath($points);
        $lastPoint = $points[$count - 1];
        $baseline = $height - $bottom;
        $areaPath = $linePath.' L '.$lastPoint['x'].','.$baseline.' L '.$points[0]['x'].','.$baseline.' Z';

        $grid = [];

        for ($index = 0; $index <= 3; $index++) {
            $fraction = $index / 3;
            $grid[] = [
                'y' => round($top + $fraction * $plotHeight, 2),
                'label' => $this->compactRupiah($axisMax * (1 - $fraction)),
            ];
        }

        $tooltipText = $this->rupiah($lastPoint['value']);
        $tooltipWidth = max(72, strlen($tooltipText) * 6.1 + 16);

        return [
            'line_path' => $linePath,
            'area_path' => $areaPath,
            'points' => $points,
            'grid' => $grid,
            'last' => $lastPoint,
            'plot' => [
                'left' => $left,
                'right' => $width - $right,
                'top' => $top,
                'bottom' => $baseline,
            ],
            'tooltip' => [
                'x' => max($left, min($width - $tooltipWidth - 4, $lastPoint['x'] - $tooltipWidth - 8)),
                'y' => max(2, $lastPoint['y'] - 30),
                'w' => $tooltipWidth,
                'label' => $tooltipText,
            ],
        ];
    }

    public function rupiah(float|int|string|null $value): string
    {
        return 'Rp '.number_format((float) $value, 0, ',', '.');
    }

    public function compactRupiah(float|int|string|null $value): string
    {
        $amount = (float) $value;

        if ($amount >= 1_000_000_000) {
            return 'Rp '.rtrim(rtrim(number_format($amount / 1_000_000_000, 1, ',', '.'), '0'), ',').' M';
        }

        if ($amount >= 1_000_000) {
            return 'Rp '.rtrim(rtrim(number_format($amount / 1_000_000, 1, ',', '.'), '0'), ',').' Jt';
        }

        if ($amount >= 1_000) {
            return 'Rp '.number_format($amount / 1_000, 0, ',', '.').' Rb';
        }

        return 'Rp '.number_format($amount, 0, ',', '.');
    }

    public function statusBadgeClasses(string $status): string
    {
        $normalized = strtolower($status);

        return match (true) {
            str_contains($normalized, 'selesai') => 'border-[#fde68a] bg-[#fef3c7] text-[#a16207]',
            str_contains($normalized, 'batal') => 'border-[#fecaca] bg-[#fee2e2] text-[#b91c1c]',
            str_contains($normalized, 'disewa'), str_contains($normalized, 'aktif') => 'border-[#bbf7d0] bg-[#dcfce7] text-[#15803d]',
            str_contains($normalized, 'habis') => 'border-[#fecaca] bg-[#fee2e2] text-[#b91c1c]',
            default => 'border-[#d1d5db] bg-[#f3f4f6] text-[#374151]',
        };
    }

    /**
     * @param  array<int, array<string, mixed>>  $points
     */
    protected function smoothPath(array $points): string
    {
        $count = count($points);

        if ($count === 0) {
            return '';
        }

        $path = 'M '.$points[0]['x'].','.$points[0]['y'];

        for ($index = 0; $index < $count - 1; $index++) {
            $previous = $points[max(0, $index - 1)];
            $current = $points[$index];
            $next = $points[$index + 1];
            $after = $points[min($count - 1, $index + 2)];

            $controlOneX = $current['x'] + ($next['x'] - $previous['x']) / 6;
            $controlOneY = $current['y'] + ($next['y'] - $previous['y']) / 6;
            $controlTwoX = $next['x'] - ($after['x'] - $current['x']) / 6;
            $controlTwoY = $next['y'] - ($after['y'] - $current['y']) / 6;

            $path .= ' C '.round($controlOneX, 2).','.round($controlOneY, 2)
                .' '.round($controlTwoX, 2).','.round($controlTwoY, 2)
                .' '.$next['x'].','.$next['y'];
        }

        return $path;
    }
}
