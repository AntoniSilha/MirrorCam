@props(['product'])

<div class="bg-[#ffffff] border border-[#e5e5e5] rounded-[4px] p-3 flex flex-col justify-between hover:border-[#000000] hover:shadow-xs transition group">
    <div>
        <!-- Image Container with Wishlist button -->
        <div class="relative w-full aspect-[4/3] rounded-[4px] overflow-hidden bg-[#f0f0f0]">
            <img
                src="{{ $product['image'] }}"
                alt="{{ $product['name'] }}"
                loading="lazy"
                class="w-full h-full object-cover object-center group-hover:scale-105 transition duration-300"
                onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1516035069371-29a1b244cc32?auto=format&fit=crop&w=600&q=80';"
            />

            <!-- Wishlist Heart Button -->
            <button
                type="button"
                aria-label="Tambah ke favorit"
                class="absolute top-2 right-2 w-7 h-7 rounded-full bg-white/80 hover:bg-white text-gray-700 hover:text-red-500 flex items-center justify-center shadow-xs transition"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                </svg>
            </button>
        </div>

        <!-- Tenant Badge & Product Name -->
        <div class="mt-2.5">
            <span class="block text-[11px] font-medium text-gray-500 truncate">
                {{ $product['tenant_name'] }}
            </span>
            <h3 class="text-xs sm:text-sm font-bold text-[#000000] line-clamp-1 mt-0.5 group-hover:underline" title="{{ $product['name'] }}">
                {{ $product['name'] }}
            </h3>

            <!-- Rating & Review Count -->
            <div class="flex items-center gap-1 mt-1 text-[11px] text-gray-600">
                <svg class="w-3.5 h-3.5 text-amber-500 fill-amber-500 shrink-0" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                </svg>
                <span class="font-bold text-[#000000]">{{ $product['rating'] }}</span>
                <span>({{ $product['review_count'] }} ulasan)</span>
            </div>

            <!-- Price -->
            <div class="mt-2.5">
                <span class="block text-[10px] text-gray-500">Tarif Sewa:</span>
                <span class="block text-xs sm:text-sm font-extrabold text-[#000000]">
                    {{ $product['formatted_price'] }}
                </span>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="grid grid-cols-2 gap-2 mt-4 pt-2 border-t border-[#f0f0f0]">
        <!-- Detail Gear Button: Background #d9d9d9, Text #000000 -->
        <a
            href="{{ route('home', ['keyword' => $product['name']]) }}#katalog"
            class="py-1.5 px-2 bg-[#d9d9d9] hover:bg-[#c7c7c7] text-[#000000] text-center text-xs font-semibold rounded-[4px] transition"
        >
            Detail Gear
        </a>

        <!-- Sewa Button: Background #000000, Text #ffffff -->
        <a
            href="{{ route('login') }}"
            class="py-1.5 px-2 bg-[#000000] hover:bg-[#222222] text-[#ffffff] text-center text-xs font-bold rounded-[4px] transition shadow-xs"
        >
            Sewa
        </a>
    </div>
</div>
