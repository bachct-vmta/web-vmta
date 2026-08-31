import Alpine from 'alpinejs';
import Swiper from 'swiper';
import { Navigation, Pagination, Autoplay } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';

window.Alpine = Alpine;
Alpine.start();

/**
 * Auto-init Swiper carousels.
 *
 * Any element marked with `data-vmta-swiper` becomes a carousel. The number of
 * slides visible on desktop is read from `data-per-view` (default 3); tablet
 * shows 2 and mobile 1 — matching the md:/lg: Tailwind grid breakpoints
 * (768px / 1024px) used elsewhere on the site.
 */
function initVmtaSwipers() {
    document.querySelectorAll('[data-vmta-swiper]').forEach((el) => {
        if (el.dataset.swiperInit) return;
        el.dataset.swiperInit = '1';

        const perView = parseInt(el.dataset.perView || '3', 10);
        const slideCount = el.querySelectorAll('.swiper-slide').length;
        // Danh mục dài không nên tự chạy, đặt data-autoplay="false" để tắt
        const autoplay = el.dataset.autoplay !== 'false';

        new Swiper(el, {
            modules: [Navigation, Pagination, Autoplay],
            slidesPerView: 1,
            spaceBetween: 30,
            loop: slideCount > perView,
            autoplay: autoplay ? { delay: 4000, disableOnInteraction: false } : false,
            pagination: {
                el: el.querySelector('.swiper-pagination'),
                clickable: true,
            },
            navigation: {
                nextEl: el.querySelector('.swiper-button-next'),
                prevEl: el.querySelector('.swiper-button-prev'),
            },
            breakpoints: {
                768: { slidesPerView: Math.min(2, perView) },
                1024: { slidesPerView: perView },
            },
        });
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initVmtaSwipers);
} else {
    initVmtaSwipers();
}

window.initVmtaSwipers = initVmtaSwipers;
