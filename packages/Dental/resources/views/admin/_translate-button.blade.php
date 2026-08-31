{{--
    Nút dịch nội dung tiếng Việt sang tab tiếng Anh, gọi endpoint admin.translate của Content.

    Đặt bên trong scope dentalLocaleTabs() để dùng lại slugify/slugTouched của scope cha.

    Biến: $viIdx, $enIdx, $plain (mảng tên field input thường), $rich (mảng tên field CKEditor)
--}}
@php
    $plain = $plain ?? [];
    $rich = $rich ?? [];
@endphp

<div x-data="{
        translating: false,
        error: '',
        async run() {
            this.translating = true;
            this.error = '';

            const get = (n) => document.getElementsByName(n)[0];
            const plain = @js($plain);
            const rich = @js($rich);
            const viIdx = @js($viIdx);
            const enIdx = @js($enIdx);

            const values = [
                ...plain.map(f => get('translations[' + viIdx + '][' + f + ']')?.value || ''),
                ...rich.map(f => get('translations[' + viIdx + '][' + f + ']')?._ckEditor?.getData() || ''),
            ];

            const resp = await fetch('{{ route('admin.translate') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('[name=_token]')?.value || '',
                },
                body: JSON.stringify({ fields: values }),
            }).catch(() => null);

            if (! resp?.ok) {
                this.error = @js(__('dental::dental.actions.translate_failed'));
                this.translating = false;
                return;
            }

            const { translated } = await resp.json();

            plain.forEach((f, i) => {
                const el = get('translations[' + enIdx + '][' + f + ']');
                if (el && translated[i]) el.value = translated[i];
            });

            rich.forEach((f, i) => {
                const el = get('translations[' + enIdx + '][' + f + ']');
                const value = translated[plain.length + i];
                if (el?._ckEditor && value) el._ckEditor.setData(value);
            });

            // Slug tiếng Anh sinh lại từ tiêu đề vừa dịch, trừ khi admin đã tự sửa
            const enTitle = translated[0];
            if (enTitle && ! slugTouched['en']) {
                const slugEl = get('translations[' + enIdx + '][slug]');
                if (slugEl) slugEl.value = slugify(enTitle);
            }

            this.translating = false;
        },
     }" class="mb-3">
    <button type="button" @click="run()" :disabled="translating"
            class="inline-flex items-center gap-1.5 rounded-lg border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-medium text-blue-700 transition-colors hover:bg-blue-100 disabled:cursor-not-allowed disabled:opacity-50">
        <span x-show="! translating">🌐 {{ __('dental::dental.actions.translate_from_vi') }}</span>
        <span x-show="translating" x-cloak class="flex items-center gap-1">
            <svg class="h-3 w-3 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            {{ __('dental::dental.actions.translating') }}
        </span>
    </button>
    <p x-show="error" x-text="error" class="mt-1 text-xs text-red-600" x-cloak></p>
</div>
