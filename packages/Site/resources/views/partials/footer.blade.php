@php $locale = app()->getLocale(); @endphp
<footer class="footer-wrapper bg-white">
    <section class="ss-footer relative overflow-hidden py-[60px] md-fs:py-[110px]">
        {{-- Background image (full bleed, behind everything). The `.fill` wrapper
             pairs with `.ss-footer .fill img` rule in app.css to apply opacity 20%
             + scale 1.5 (matches vmta.test custom CSS). --}}
        <div class="fill absolute inset-0 overflow-hidden">
            <img src="{{ asset('images/about/908c99ad-f012-4b20-9d8a-cbeee71686e5.png') }}"
                 class="w-full h-full object-cover" alt="" loading="lazy" aria-hidden="true">
        </div>

        <div class="relative max-w-7xl mx-auto px-4">
            {{-- Row 1: Logo / Newsletter / Social --}}
            <div class="grid grid-cols-1 md-fs:grid-cols-3 gap-8 items-start">

                {{-- Col 1: Logo --}}
                <div class="vmta-footer">
                    <img src="{{ asset('images/home/footer/logo-vmta-white.png') }}" alt="VMTA"
                         class="w-1/2 md-fs:w-[70%] mx-auto md-fs:mx-0">
                </div>

                {{-- Col 2: Newsletter --}}
                <div class="">
                    <h3 class="font-utm-helve uppercase text-[#0b7f7c] mb-6 font-bold">
                        {{ __('site::site.footer.newsletter_heading') }}
                    </h3>
                    @if(session('status'))
                        <p class="text-[#0b7f7c] text-base mb-2" role="status">{{ session('status') }}</p>
                    @endif
                    <form method="POST" action="{{ route('newsletter.' . $locale . '.subscribe') }}"
                          class="relative max-w-md mx-auto">
                        @csrf
                        @honeypot
                        <input type="email" name="email" required
                               placeholder="{{ __('site::site.footer.email_placeholder') }}"
                               value="{{ old('email') }}"
                               class="w-full rounded-xl bg-white/80 border border-[#0b7f7c]/30 pl-5 pr-12 py-2.5 text-base text-[#0b7f7c] placeholder-[#0b7f7c]/60 focus:outline-none focus:ring-2 focus:ring-[#0b7f7c]/40">
                        <label class="hidden"><input type="checkbox" name="consent_given" value="1" checked></label>
                        <button type="submit"
                                class="cf7-btn absolute right-2 top-1/2 -translate-y-1/2 inline-flex items-center justify-center w-8 h-8 rounded-full text-[#0b7f7c] hover:text-[#0b7f7c]/70 transition"
                                aria-label="{{ __('site::site.footer.subscribe') }}">
                            <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true">
                                <path fill="currentColor" d="M2,21L23,12L2,3V10L17,12L2,14V21Z"/>
                            </svg>
                        </button>
                    </form>
                    @error('email')
                        <p class="text-red-700 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Col 3: Social --}}
                <div class="text-right">
                    <h3 class="font-utm-helve uppercase text-[#0b7f7c] mb-6 font-bold text-left md:text-right">
    {{ __('site::site.footer.social_heading') }}
</h3>
                    <div class="flex items-center justify-start md:justify-end gap-4">
                        <a href="{{ setting('social.facebook_url') ?: '#' }}" target="_blank" rel="noopener noreferrer" aria-label="Facebook" class="block max-h-[50px]">
                            <img src="{{ asset('images/home/footer/social-1.png') }}" alt="Facebook" class="w-full h-auto [filter:brightness(0)_saturate(100%)_invert(38%)_sepia(25%)_saturate(1700%)_hue-rotate(132deg)_brightness(96%)_contrast(91%)]">
                        </a>
                        <a href="{{ setting('social.instagram_url') ?: '#' }}" target="_blank" rel="noopener noreferrer" aria-label="Instagram" class="block max-h-[50px]">
                            <img src="{{ asset('images/home/footer/social-2.png') }}" alt="Instagram" class="w-full h-auto [filter:brightness(0)_saturate(100%)_invert(38%)_sepia(25%)_saturate(1700%)_hue-rotate(132deg)_brightness(96%)_contrast(91%)]">
                        </a>
                        <a href="{{ setting('social.youtube_url') ?: '#' }}" target="_blank" rel="noopener noreferrer" aria-label="YouTube" class="block max-h-[50px]">
                            <img src="{{ asset('images/home/footer/social-3.png') }}" alt="YouTube" class="w-full h-auto [filter:brightness(0)_saturate(100%)_invert(38%)_sepia(25%)_saturate(1700%)_hue-rotate(132deg)_brightness(96%)_contrast(91%)]">
                        </a>
                        <a href="{{ setting('social.tiktok_url') ?: '#' }}" target="_blank" rel="noopener noreferrer" aria-label="TikTok" class="block max-h-[50px]">
                            <img src="{{ asset('images/home/footer/social-4.png') }}" alt="TikTok" class="w-full h-auto [filter:brightness(0)_saturate(100%)_invert(38%)_sepia(25%)_saturate(1700%)_hue-rotate(132deg)_brightness(96%)_contrast(91%)]">
                        </a>
                    </div>
                </div>
            </div>

            {{-- Row 2: info | policies | links | support(auto=fits content).
                 Col 1 width = calc((100% - 2×gap-8)/3) = same as col 1 of row 1's `grid-cols-3`
                 → col 2 (Chính Sách) lines up vertically with col 2 of row 1 (newsletter).
                 4rem = 2 × gap-8 (gap-8 = 2rem); single subtraction works because row 1 has
                 2 gaps between 3 cols.  Col 4 `auto` sits at container right edge. --}}
            <div class="mt-10 md-fs:mt-12 grid grid-cols-1 sm-fs:grid-cols-2 gap-8 items-start md-fs:[grid-template-columns:calc((100%_-_4rem)/3)_1fr_1fr_auto]">

                {{-- Col 1: Company info --}}
                <div>
                    <p class="font-utm-helve font-bold text-[#0b7f7c] text-xl md-fs:text-2xl leading-[1.4] mb-3">
                        {{ __('site::site.footer.company_name') }}
                    </p>
                    <p class="font-utm-helve text-[#4a4a4a] text-base leading-relaxed mb-3 text-justify" style="text-align-last: left;">
                        {{ __('site::site.footer.company_description') }}
                    </p>
                    <p class="font-utm-helve text-[#4a4a4a] text-base mb-4">
                        {{ __('site::site.footer.address') }}
                    </p>
                    <img src="{{ asset('images/home/footer/vmta-bo-y-te-badge.png') }}"
                         alt="Đã thông báo Bộ Công Thương"
                         class="w-[60%] h-auto"
                         loading="lazy">
                </div>

                {{-- Col 2: Policies --}}
                {{-- Col 2: Policies — items pulled from the `footer_1_navigation` menu (DB),
                     resolved per-locale via MenuService (cached). --}}
                @php
                    $footerMenu = app(\Packages\Content\Src\Services\MenuService::class)
                        ->getMenu('footer_1_navigation', $locale);
                    $footerItems = $footerMenu?->rootItems
                        ?->where('is_active', true)
                        ?? collect();
                @endphp
                <div>
                    <h4 class="font-utm-helve font-bold  text-lg mb-3 text-[#0b7f7c]">
                        {{ __('site::site.footer.menu_policies') }}
                    </h4>
                    <ul class="space-y-2 font-utm-helve text-base text-[#4a4a4a]">
                        @forelse($footerItems as $item)
                            @php
                                $tr = $item->translations->firstWhere('locale', $locale)
                                    ?? $item->translations->first();
                                // Scheme guard mirrors content::public.partials.menu-item:
                                // permit relative + http(s)/mailto/tel, collapse anything else to '#'.
                                $rawHref = ! empty($tr?->url) ? $tr->url : '#';
                                $hrefLower = strtolower(ltrim((string) $rawHref));
                                $isSafe = $hrefLower === ''
                                    || str_starts_with($hrefLower, '/')
                                    || str_starts_with($hrefLower, '#')
                                    || str_starts_with($hrefLower, '?')
                                    || str_starts_with($hrefLower, 'http://')
                                    || str_starts_with($hrefLower, 'https://')
                                    || str_starts_with($hrefLower, 'mailto:')
                                    || str_starts_with($hrefLower, 'tel:');
                                $href = $isSafe ? $rawHref : '#';
                            @endphp
                            <li>
                                <a href="{{ $href }}"
                                   @if($item->open_new_tab) target="_blank" rel="noopener" @endif
                                   class="hover:text-[#0b7f7c] transition">{{ $tr?->label ?? '—' }}</a>
                            </li>
                        @empty
                            <li><a href="#" class="hover:text-[#0b7f7c] transition">{{ __('site::site.footer.policy_privacy') }}</a></li>
                            <li><a href="#" class="hover:text-[#0b7f7c] transition">{{ __('site::site.footer.policy_payment') }}</a></li>
                        @endforelse
                    </ul>
                </div>

                {{-- Col 3: Quick Links — items pulled from the `footer_2_navigation` menu (DB),
                     resolved per-locale via MenuService (cached). --}}
                @php
                    $linksMenu = app(\Packages\Content\Src\Services\MenuService::class)
                        ->getMenu('footer_2_navigation', $locale);
                    $linksItems = $linksMenu?->rootItems
                        ?->where('is_active', true)
                        ?? collect();
                @endphp
                <div>
                    <h4 class="font-utm-helve font-bold text-lg mb-3 text-[#0b7f7c]">
                        {{ __('site::site.footer.menu_links') }}
                    </h4>
                    <ul class="space-y-2 font-utm-helve text-base text-[#4a4a4a]">
                        @forelse($linksItems as $item)
                            @php
                                $tr = $item->translations->firstWhere('locale', $locale)
                                    ?? $item->translations->first();
                                // Scheme guard mirrors content::public.partials.menu-item:
                                // permit relative + http(s)/mailto/tel, collapse anything else to '#'.
                                $rawHref = ! empty($tr?->url) ? $tr->url : '#';
                                $hrefLower = strtolower(ltrim((string) $rawHref));
                                $isSafe = $hrefLower === ''
                                    || str_starts_with($hrefLower, '/')
                                    || str_starts_with($hrefLower, '#')
                                    || str_starts_with($hrefLower, '?')
                                    || str_starts_with($hrefLower, 'http://')
                                    || str_starts_with($hrefLower, 'https://')
                                    || str_starts_with($hrefLower, 'mailto:')
                                    || str_starts_with($hrefLower, 'tel:');
                                $href = $isSafe ? $rawHref : '#';
                            @endphp
                            <li>
                                <a href="{{ $href }}"
                                   @if($item->open_new_tab) target="_blank" rel="noopener" @endif
                                   class="hover:text-[#0b7f7c] transition">{{ $tr?->label ?? '—' }}</a>
                            </li>
                        @empty
                            <li><a href="#" class="hover:text-[#0b7f7c] transition">{{ __('site::site.footer.link_register_business') }}</a></li>
                            <li><a href="#" class="hover:text-[#0b7f7c] transition">{{ __('site::site.footer.link_learn_more') }}</a></li>
                        @endforelse
                    </ul>
                </div>
                {{-- Col 4: Support. Text left-aligned (visual consistency with cols 2-3).
                     Col 4 grid track is `auto` → shrinks to content → col 4's right edge
                     sits at container right edge = aligns with THAM GIA button right edge. --}}
                <div>
                    <h4 class="font-utm-helve font-bold text-lg mb-3 text-[#0b7f7c]">
                        {{ __('site::site.footer.menu_support') }}
                    </h4>
                    <ul class="space-y-2 font-utm-helve text-base text-[#4a4a4a]">
                        <li>
                            <a href="mailto:{{ __('site::site.footer.support_email') }}" class="hover:text-[#0b7f7c] transition">
                                {{ __('site::site.footer.support_email_label') }}: {{ __('site::site.footer.support_email') }}
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

</footer>
