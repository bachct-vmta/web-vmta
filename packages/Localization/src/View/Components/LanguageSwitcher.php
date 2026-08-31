<?php

namespace Packages\Localization\Src\View\Components;

use Illuminate\View\Component;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

/**
 * Language switcher Blade component.
 *
 * Usage: <x-vmta-language-switcher /> or <x-localization::language-switcher />
 * Renders links that preserve the current path for each supported locale.
 */
class LanguageSwitcher extends Component
{
    public function __construct(public string $variant = 'inline')
    {
    }

    public function locales(): array
    {
        $current = app()->getLocale();
        $list = [];

        foreach (LaravelLocalization::getSupportedLocales() as $code => $meta) {
            $list[] = [
                'code' => $code,
                'native' => $meta['native'] ?? strtoupper($code),
                'name' => $meta['name'] ?? strtoupper($code),
                'url' => LaravelLocalization::getLocalizedURL($code, null, [], true),
                'active' => $code === $current,
            ];
        }

        return $list;
    }

    public function render()
    {
        return view('localization::components.language-switcher');
    }
}
