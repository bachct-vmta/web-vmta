<?php

namespace Packages\Inquiry\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Packages\Inquiry\Src\Enums\ContactSectionPosition;
use Packages\Inquiry\Src\Models\ContactSection;

/**
 * Seeds the 3 contact-page sections with the content currently hardcoded in
 * the lang files / inquiry config. EN mirrors VI — content team translates later.
 * Idempotent via firstOrNew on the unique position.
 */
class ContactSectionSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            foreach ($this->payload() as $row) {
                $position = $row['position'];
                $section  = ContactSection::firstOrNew(['position' => $position->value]);
                $section->position   = $position;
                $section->is_active  = true;
                $section->sort_order = $position->defaultSortOrder();
                if (array_key_exists('map_embed', $row)) {
                    $section->map_embed = $row['map_embed'];
                }
                $section->save();

                foreach ($row['translations'] as $locale => $payload) {
                    $section->translateOrNew($locale)->fill($payload)->save();
                }
            }
        });
    }

    private function payload(): array
    {
        return [$this->hero(), $this->offices()];
    }

    private function hero(): array
    {
        $vi = [
            'title'    => 'Liên hệ',
            'subtitle' => 'Liên hệ với VMTA',
            'body'     => 'Nơi mọi hành trình chăm sóc sức khỏe được thiết kế riêng',
            'extra'    => 'Dù bạn là khách hàng đang tìm giải pháp điều trị hay đối tác muốn tham gia hệ sinh thái, VMTA luôn sẵn sàng đồng hành',
        ];

        return ['position' => ContactSectionPosition::Hero, 'translations' => ['vi' => $vi, 'en' => $vi]];
    }

    private function offices(): array
    {
        $items = [
            [
                'name'    => 'Trụ sở VMTA',
                'address' => (string) config('inquiry.offices.hq.address', ''),
                'email'   => (string) config('inquiry.offices.hq.email', ''),
                'phone'   => '',
                'note'    => '',
            ],
            [
                'name'    => 'Chi nhánh VMTA',
                'address' => (string) config('inquiry.offices.branch.address', ''),
                'email'   => (string) config('inquiry.offices.branch.email', ''),
                'phone'   => '',
                'note'    => '',
            ],
            [
                'name'    => 'Hỗ trợ kỹ thuật',
                'address' => '',
                'email'   => (string) config('inquiry.offices.tech_support.email', ''),
                'phone'   => (string) config('inquiry.offices.tech_support.phone', ''),
                'note'    => '(Phản hồi trong 24h)',
            ],
        ];
        $vi = ['title' => 'Thông tin liên hệ trực tiếp', 'items' => $items];

        return [
            'position'  => ContactSectionPosition::Offices,
            'map_embed' => null,
            'translations' => ['vi' => $vi, 'en' => $vi],
        ];
    }

}
