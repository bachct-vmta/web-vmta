@once
    @push('scripts')
        <script>
            // Slug tự sinh từ tên cho tới khi admin tự sửa ô slug
            function dentalLocaleTabs(initialLocale) {
                return {
                    activeLocale: initialLocale,
                    slugTouched: {},
                    slugify(s) {
                        return (s || '')
                            .normalize('NFD').replace(/[̀-ͯ]/g, '')
                            .replace(/đ/g, 'd').replace(/Đ/g, 'D')
                            .toLowerCase()
                            .replace(/[^a-z0-9\s-]/g, '')
                            .trim()
                            .replace(/[\s_-]+/g, '-')
                            .replace(/^-+|-+$/g, '');
                    },
                    syncSlug(idx, locale, value) {
                        if (this.slugTouched[locale]) return;
                        const el = document.getElementsByName('translations[' + idx + '][slug]')[0];
                        if (el) el.value = this.slugify(value);
                    },
                };
            }
        </script>
    @endpush
@endonce
