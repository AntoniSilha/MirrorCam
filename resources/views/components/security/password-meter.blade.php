@props([
    'targetId' => 'new-password',
])

<div class="mb-5 -mt-2">
    <!-- Strength Label -->
    <div class="flex items-center justify-between mb-2">
        <span class="text-xs font-bold text-neutral-600">Tingkat Kekuatan:</span>
        <span id="strength-text" class="text-xs font-medium text-neutral-400">Belum Terisi</span>
    </div>

    <!-- Segmented Strength Bar -->
    <div class="grid grid-cols-4 gap-1.5 mb-3">
        <div id="bar-1" class="h-1.5 rounded-[2px] bg-neutral-200 transition-colors duration-200"></div>
        <div id="bar-2" class="h-1.5 rounded-[2px] bg-neutral-200 transition-colors duration-200"></div>
        <div id="bar-3" class="h-1.5 rounded-[2px] bg-neutral-200 transition-colors duration-200"></div>
        <div id="bar-4" class="h-1.5 rounded-[2px] bg-neutral-200 transition-colors duration-200"></div>
    </div>

    <!-- Rule Indicators (8+ Karakter, Huruf & Angka, Simbol Khusus) -->
    <div class="flex flex-wrap items-center gap-4 sm:gap-8 text-xs text-neutral-500">
        <!-- 8+ Karakter -->
        <div id="rule-length" class="flex items-center gap-1.5 transition-colors duration-150">
            <span class="indicator-icon w-3.5 h-3.5 rounded-full border border-neutral-300 flex items-center justify-center text-[9px]"></span>
            <span class="indicator-text">8+ Karakter</span>
        </div>

        <!-- Huruf & Angka -->
        <div id="rule-alphanumeric" class="flex items-center gap-1.5 transition-colors duration-150">
            <span class="indicator-icon w-3.5 h-3.5 rounded-full border border-neutral-300 flex items-center justify-center text-[9px]"></span>
            <span class="indicator-text">Huruf & Angka</span>
        </div>

        <!-- Simbol Khusus -->
        <div id="rule-symbol" class="flex items-center gap-1.5 transition-colors duration-150">
            <span class="indicator-icon w-3.5 h-3.5 rounded-full border border-neutral-300 flex items-center justify-center text-[9px]"></span>
            <span class="indicator-text">Simbol Khusus</span>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const passwordInput = document.getElementById('{{ $targetId }}');
        if (!passwordInput) return;

        const bar1 = document.getElementById('bar-1');
        const bar2 = document.getElementById('bar-2');
        const bar3 = document.getElementById('bar-3');
        const bar4 = document.getElementById('bar-4');
        const strengthText = document.getElementById('strength-text');

        const ruleLength = document.getElementById('rule-length');
        const ruleAlphanumeric = document.getElementById('rule-alphanumeric');
        const ruleSymbol = document.getElementById('rule-symbol');

        function updateRule(elem, isValid) {
            const icon = elem.querySelector('.indicator-icon');
            const text = elem.querySelector('.indicator-text');
            if (isValid) {
                icon.className = 'indicator-icon w-3.5 h-3.5 rounded-full bg-[#16a34a] border border-[#16a34a] flex items-center justify-center text-[9px] text-white font-bold';
                icon.innerHTML = '✓';
                text.className = 'indicator-text text-[#16a34a] font-semibold';
            } else {
                icon.className = 'indicator-icon w-3.5 h-3.5 rounded-full border border-neutral-300 flex items-center justify-center text-[9px]';
                icon.innerHTML = '';
                text.className = 'indicator-text text-neutral-500 font-normal';
            }
        }

        passwordInput.addEventListener('input', function () {
            const val = this.value;

            // Check criteria
            const hasLength = val.length >= 8;
            const hasLetters = /[a-zA-Z]/.test(val);
            const hasNumbers = /[0-9]/.test(val);
            const hasAlphanumeric = hasLetters && hasNumbers;
            const hasSymbols = /[^a-zA-Z0-9]/.test(val);

            updateRule(ruleLength, hasLength);
            updateRule(ruleAlphanumeric, hasAlphanumeric);
            updateRule(ruleSymbol, hasSymbols);

            // Calculate score
            let score = 0;
            if (hasLength) score++;
            if (hasAlphanumeric) score++;
            if (hasSymbols) score++;
            if (val.length >= 12 && hasLength && hasAlphanumeric && hasSymbols) score++;

            // Reset bars
            [bar1, bar2, bar3, bar4].forEach(bar => {
                bar.className = 'h-1.5 rounded-[2px] bg-neutral-200 transition-colors duration-200';
            });

            if (val.length === 0) {
                strengthText.textContent = 'Belum Terisi';
                strengthText.className = 'text-xs font-medium text-neutral-400';
            } else if (score === 1) {
                bar1.className = 'h-1.5 rounded-[2px] bg-[#ba1a1a] transition-colors duration-200';
                strengthText.textContent = 'Lemah';
                strengthText.className = 'text-xs font-semibold text-[#ba1a1a]';
            } else if (score === 2) {
                bar1.className = 'h-1.5 rounded-[2px] bg-[#B45309] transition-colors duration-200';
                bar2.className = 'h-1.5 rounded-[2px] bg-[#B45309] transition-colors duration-200';
                strengthText.textContent = 'Sedang';
                strengthText.className = 'text-xs font-semibold text-[#B45309]';
            } else if (score === 3) {
                bar1.className = 'h-1.5 rounded-[2px] bg-yellow-500 transition-colors duration-200';
                bar2.className = 'h-1.5 rounded-[2px] bg-yellow-500 transition-colors duration-200';
                bar3.className = 'h-1.5 rounded-[2px] bg-yellow-500 transition-colors duration-200';
                strengthText.textContent = 'Kuat';
                strengthText.className = 'text-xs font-semibold text-yellow-600';
            } else if (score >= 4) {
                bar1.className = 'h-1.5 rounded-[2px] bg-[#16a34a] transition-colors duration-200';
                bar2.className = 'h-1.5 rounded-[2px] bg-[#16a34a] transition-colors duration-200';
                bar3.className = 'h-1.5 rounded-[2px] bg-[#16a34a] transition-colors duration-200';
                bar4.className = 'h-1.5 rounded-[2px] bg-[#16a34a] transition-colors duration-200';
                strengthText.textContent = 'Sangat Kuat';
                strengthText.className = 'text-xs font-semibold text-[#16a34a]';
            }
        });
    });
</script>
