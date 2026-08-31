<?php

namespace Packages\Content\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Packages\Content\Src\Models\Menu;
use Packages\Content\Src\Models\MenuItem;

/**
 * Seeds the canonical VMTA navigation per `Sitemap web VMTA.pptx`.
 *  - main_navigation    : top primary nav (Giới thiệu / Y tế / Sản phẩm / Mạng lưới / Tin tức / Liên hệ)
 *  - footer_1_navigation: policies + customer support
 *
 * Idempotent: re-running upserts by `location` + (menu_id, sort_order) so re-seeding does
 * NOT duplicate items. Translations are synced via translateOrNew.
 */
class MenuSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // Drop legacy `header`/`footer` slugs so the strict enum validator
            // (Menu::LOCATIONS) doesn't reject existing rows on the next edit.
            Menu::query()
                ->whereIn('location', ['header', 'footer'])
                ->update(['location' => DB::raw("CASE location WHEN 'header' THEN 'main_navigation' WHEN 'footer' THEN 'footer_1_navigation' END")]);

            $header = $this->upsertMenu('Main Navigation', 'main_navigation');
            $this->upsertItems($header, $this->headerItems());

            $footer = $this->upsertMenu('Footer 1 Navigation', 'footer_1_navigation');
            $this->upsertItems($footer, $this->footerItems());
        });
    }

    private function upsertMenu(string $name, string $location): Menu
    {
        $menu = Menu::firstOrNew(['location' => $location]);
        $menu->name = $name;
        $menu->is_active = true;
        $menu->save();

        return $menu;
    }

    /**
     * @param  array<int, array{vi: array{label: string, url: string}, en: array{label: string, url: string}, icon?: ?string}>  $items
     */
    private function upsertItems(Menu $menu, array $items): void
    {
        foreach ($items as $idx => $row) {
            $existing = MenuItem::where('menu_id', $menu->id)
                ->where('sort_order', $idx + 1)
                ->whereNull('parent_id')
                ->first();

            $item = $existing ?? new MenuItem;
            $item->menu_id = $menu->id;
            $item->parent_id = null;
            $item->link_type = 'url';
            $item->target_type = null;
            $item->target_id = null;
            $item->icon = $row['icon'] ?? null;
            $item->css_class = null;
            $item->open_new_tab = false;
            $item->sort_order = $idx + 1;
            $item->is_active = true;
            $item->save();

            foreach ($row as $locale => $payload) {
                if (! is_array($payload) || ! isset($payload['label'], $payload['url'])) {
                    continue;
                }
                $item->translateOrNew($locale)->fill([
                    'label' => $payload['label'],
                    'url' => $payload['url'],
                ])->save();
            }
        }
    }

    /**
     * Header nav per Sitemap web VMTA.pptx.
     *
     * @return array<int, array{vi: array{label: string, url: string}, en: array{label: string, url: string}}>
     */
    private function headerItems(): array
    {
        return [
            ['vi' => ['label' => 'Giới thiệu', 'url' => '/vi/gioi-thieu'],
                'en' => ['label' => 'About', 'url' => '/en/about']],
            ['vi' => ['label' => 'Y Tế – Trị Liệu', 'url' => '/vi/y-te-tri-lieu'],
                'en' => ['label' => 'Medical Services', 'url' => '/en/medical-services']],
            ['vi' => ['label' => 'Sản phẩm', 'url' => '/vi/san-pham'],
                'en' => ['label' => 'Products', 'url' => '/en/products']],
            ['vi' => ['label' => 'Mạng lưới Liên Minh', 'url' => '/vi/mang-luoi-lien-minh'],
                'en' => ['label' => 'Alliance Network', 'url' => '/en/alliance-network']],
            ['vi' => ['label' => 'Tin tức', 'url' => '/vi/tin-tuc'],
                'en' => ['label' => 'News', 'url' => '/en/tin-tuc']],
            ['vi' => ['label' => 'Liên hệ', 'url' => '/vi/contact'],
                'en' => ['label' => 'Contact', 'url' => '/en/contact']],
        ];
    }

    /**
     * @return array<int, array{vi: array{label: string, url: string}, en: array{label: string, url: string}}>
     */
    private function footerItems(): array
    {
        return [
            ['vi' => ['label' => 'Chính sách bảo mật', 'url' => '/vi/chinh-sach-bao-mat'],
                'en' => ['label' => 'Privacy Policy', 'url' => '/en/privacy-policy']],
            ['vi' => ['label' => 'Chính sách thanh toán', 'url' => '/vi/chinh-sach-thanh-toan'],
                'en' => ['label' => 'Payment Policy', 'url' => '/en/payment-policy']],
            ['vi' => ['label' => 'Hỗ trợ khách hàng', 'url' => '/vi/khan-cap'],
                'en' => ['label' => 'Customer Support', 'url' => '/en/khan-cap']],
        ];
    }
}
