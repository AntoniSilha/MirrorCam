@extends('layouts.landing')

@section('title', 'MirrorCam — Platform Sewa Kamera, Lensa & Studio Lintas Tenant')

@section('content')
    <!-- 1. Hero Banner + Filter Search Component -->
    <x-home.hero-search
        :tenants="$tenants"
        :categories="$categories"
        :searchParams="$searchParams ?? []"
    />

    <!-- 2. Kategori Peralatan Populer (Dynamic Loop) -->
    <x-home.category-list
        :categories="$categories"
    />

    <!-- 3. Mitra Resmi Tenant MirrorCam (Dynamic Loop) -->
    <x-home.tenant-partners
        :tenants="$tenants"
    />

    <!-- 4. Rekomendasi Gear Terverifikasi (Dynamic Loop) -->
    <x-home.product-grid
        :featuredProducts="$featuredProducts"
        :hasSearch="$hasSearch ?? false"
    />

    <!-- 5. Sewa Lintas Tenant dalam 4 Langkah -->
    <x-home.how-it-works />

    <!-- 6. Komitmen Keamanan & FAQ (2-Column Grid matching UI Mockup) -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-12 items-start">
            <!-- Left: Security & Inventory Commitments -->
            <x-home.security-commitments />

            <!-- Right: Frequently Asked Questions (FAQ Accordion) -->
            <x-home.faq-accordion />
        </div>
    </section>

    <!-- 7. Tenant Call-to-Action Banner -->
    <x-home.tenant-cta />
@endsection
