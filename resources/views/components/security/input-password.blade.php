@props([
    'id' => null,
    'name',
    'label',
    'placeholder' => '',
    'topLinkText' => null,
    'topLinkUrl' => null,
    'required' => false,
])

@php
    $inputId = $id ?? 'input_' . $name . '_' . uniqid();
    $hasError = isset($errors) && $errors->has($name);
@endphp

<div class="mb-5">
    <!-- Top Label & Link -->
    <div class="flex items-center justify-between mb-1.5">
        <label for="{{ $inputId }}" class="block text-xs font-bold text-black">
            {{ $label }}
            @if ($required)
                <span class="text-[#ba1a1a]">*</span>
            @endif
        </label>

        @if ($topLinkText && $topLinkUrl)
            <a href="{{ $topLinkUrl }}" class="text-xs text-neutral-400 hover:text-black transition">
                {{ $topLinkText }}
            </a>
        @endif
    </div>

    <!-- Password Input with Toggle Eye Button -->
    <div class="relative">
        <input type="password"
               id="{{ $inputId }}"
               name="{{ $name }}"
               placeholder="{{ $placeholder }}"
               {{ $required ? 'required' : '' }}
               {{ $attributes->merge(['class' => 'w-full bg-[#ffffff] border ' . ($hasError ? 'border-[#ba1a1a] ring-1 ring-[#ba1a1a]' : 'border-neutral-300 focus:border-black') . ' rounded-[4px] px-3.5 py-2.5 pr-10 text-xs md:text-sm text-black placeholder-neutral-400 focus:outline-hidden transition shadow-2xs']) }} />

        <!-- Toggle Visibility Button -->
        <button type="button"
                tabindex="-1"
                onclick="togglePasswordVisibility('{{ $inputId }}', this)"
                class="absolute inset-y-0 right-0 pr-3 flex items-center text-neutral-400 hover:text-black cursor-pointer focus:outline-hidden">
            <!-- Icon Eye Slashed (Hidden - Default) -->
            <svg class="w-4 h-4 icon-hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
            </svg>
            <!-- Icon Eye Open (Shown) -->
            <svg class="w-4 h-4 icon-shown hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
        </button>
    </div>

    <!-- Error Message -->
    @if (isset($errors) && $errors->has($name))
        <p class="mt-1.5 text-xs text-[#ba1a1a] font-medium flex items-center gap-1">
            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ $errors->first($name) }}</span>
        </p>
    @endif
</div>

@once
    <script>
        function togglePasswordVisibility(targetId, btn) {
            const input = document.getElementById(targetId);
            if (!input) return;

            const iconHidden = btn.querySelector('.icon-hidden');
            const iconShown = btn.querySelector('.icon-shown');

            if (input.type === 'password') {
                input.type = 'text';
                iconHidden.classList.add('hidden');
                iconShown.classList.remove('hidden');
            } else {
                input.type = 'password';
                iconHidden.classList.remove('hidden');
                iconShown.classList.add('hidden');
            }
        }
    </script>
@endonce
