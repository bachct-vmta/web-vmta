@once
    @push('scripts')
        <script>
            // Lọc phía client: bỏ dấu rồi so khớp, và đổi carousel sang lưới phẳng khi đang lọc
            function dentalFilter() {
                const initial = new URLSearchParams(window.location.search).get('q') || '';
                return {
                    query: initial,
                    get filtering() {
                        return this.query.trim() !== '';
                    },
                    normalise(value) {
                        return (value || '')
                            .normalize('NFD').replace(/[̀-ͯ]/g, '')
                            .replace(/đ/g, 'd').replace(/Đ/g, 'D')
                            .toLowerCase().trim();
                    },
                    matches(haystack) {
                        return this.normalise(haystack).includes(this.normalise(this.query));
                    },
                };
            }
        </script>
    @endpush
@endonce
