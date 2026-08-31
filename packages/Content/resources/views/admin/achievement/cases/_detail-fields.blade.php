@php
    $detail = is_array($detail ?? null) ? $detail : [];
    $text = fn (string $key, string $default = '') => data_get($detail, $key, $default);
    $rows = function (string $key, array $defaults) use ($detail) {
        $value = data_get($detail, $key, []);
        return array_values(is_array($value) && $value !== [] ? $value : $defaults);
    };

    /**
     * Resolve a media_files.id → public preview URL. Returns null when missing
     * so Alpine's `x-show="preview"` hides the thumbnail.
     */
    $resolveMediaPreview = function ($id) {
        if (! $id) return null;
        $media = \Packages\Core\Src\Models\MediaFile::find($id);
        if (! $media?->permalink) return null;
        if (preg_match('#^https?://#', $media->permalink)) return $media->permalink;
        $base = rtrim(config('file-manager.base_path', '/uploads'), '/');
        return url($base.'/'.ltrim($media->permalink, '/'));
    };

    /**
     * Enrich row data for Alpine repeater: ensure each row has a stable __uid
     * (for :key on x-for) and a pre-resolved icon_preview URL.
     */
    $prepareIconRows = function (array $rawRows) use ($resolveMediaPreview) {
        return array_values(array_map(function ($row) use ($resolveMediaPreview) {
            $row['title'] = $row['title'] ?? '';
            $row['body'] = $row['body'] ?? '';
            $row['icon'] = $row['icon'] ?? '';
            $row['icon_media_id'] = $row['icon_media_id'] ?? '';
            $row['icon_preview'] = $resolveMediaPreview($row['icon_media_id'] ?: null);
            $row['__uid'] = 'r_'.bin2hex(random_bytes(4));
            return $row;
        }, $rawRows));
    };

    $reasonRows = $prepareIconRows($rows('reason_items', array_fill(0, 4, ['title' => '', 'body' => '', 'icon' => ''])));
    $leftRows = $prepareIconRows($rows('breakthrough_left_items', array_fill(0, 4, ['title' => '', 'body' => '', 'icon' => ''])));
    $rightRows = $prepareIconRows($rows('breakthrough_right_items', array_fill(0, 3, ['title' => '', 'body' => '', 'icon' => ''])));

    /**
     * Choice items use `image_media_id` (full-size image, not an icon) — same
     * shape pattern as prepareIconRows but for image field.
     */
    $prepareChoiceRows = function (array $rawRows) use ($resolveMediaPreview) {
        return array_values(array_map(function ($row) use ($resolveMediaPreview) {
            $row['title'] = $row['title'] ?? '';
            $row['body'] = $row['body'] ?? '';
            $row['image_media_id'] = $row['image_media_id'] ?? '';
            $row['image_preview'] = $resolveMediaPreview($row['image_media_id'] ?: null);
            $row['__uid'] = 'r_'.bin2hex(random_bytes(4));
            return $row;
        }, $rawRows));
    };
    $choiceRows = $prepareChoiceRows($rows('choice_items', array_fill(0, 4, ['title' => '', 'body' => ''])));
    $processRows = $rows('process_items', array_fill(0, 4, ['body' => '']));
@endphp

<section class="mt-6 rounded border border-teal-100 bg-teal-50/40 p-4">
    <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-teal-800">
        {{ __('content::achievement.cases.form.detail_heading') }}
    </h3>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        @foreach([
            'hero_eyebrow' => 'detail_hero_eyebrow',
            'hero_title' => 'detail_hero_title',
            'hero_highlight' => 'detail_hero_highlight',
            'cta_label' => 'detail_cta_label',
            'hospital_name' => 'detail_hospital_name',
            'time_value' => 'detail_time_value',
        ] as $field => $label)
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('content::achievement.cases.form.' . $label) }}</label>
                <input type="text" name="translations[{{ $loc }}][detail_content][{{ $field }}]"
                       value="{{ $text($field) }}" class="w-full rounded border-slate-300 text-sm">
            </div>
        @endforeach
    </div>

    <div class="mt-4">
        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('content::achievement.cases.form.detail_hero_highlight_text') }}</label>
        <input type="text" name="translations[{{ $loc }}][detail_content][hero_highlight_text]"
               value="{{ $text('hero_highlight_text') }}" maxlength="200"
               class="w-full rounded border-slate-300 text-sm">
    </div>

    <div class="mt-4">
        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('content::achievement.cases.form.detail_hero_body') }}</label>
        <textarea name="translations[{{ $loc }}][detail_content][hero_body]" rows="3"
                  class="w-full rounded border-slate-300 text-sm">{{ $text('hero_body') }}</textarea>
    </div>

    <div class="mt-4">
        <x-core::ckeditor
            :name="'translations[' . $loc . '][detail_content][intro_body]'"
            :value="$text('intro_body')"
            :label="__('content::achievement.cases.form.detail_intro_body')"
            rows="12"
        />
    </div>

    <div class="mt-4">
        @php
            $introMediaId = $text('intro_media_id');
            $introMediaModel = $introMediaId ? \Packages\Core\Src\Models\MediaFile::find($introMediaId) : null;
        @endphp
        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('content::achievement.cases.form.detail_intro_media') }}</label>
        <x-core::media-picker
            :name="'translations[' . $loc . '][detail_content][intro_media_id]'"
            :value="$introMediaId"
            :preview-url="$introMediaModel?->permalink"
            store="id"
        />
    </div>

    {{-- Repeater sections (reason_items + breakthrough_left + breakthrough_right):
         each row has Title, Body, Icon (Media Picker). Admin can Add/Remove rows
         via buttons. Field naming uses live x-for index so on remove/reorder the
         POST array stays sequential (translations[loc][group][0..N]).
         Icon is stored as icon_media_id; the legacy `icon` filename column is
         preserved (passed through as hidden input) for backward compat. --}}
    @foreach([
        ['key' => 'reason_items', 'label' => 'detail_reason_items', 'rows' => $reasonRows],
        ['key' => 'breakthrough_left_items', 'label' => 'detail_breakthrough_left_items', 'rows' => $leftRows],
        ['key' => 'breakthrough_right_items', 'label' => 'detail_breakthrough_right_items', 'rows' => $rightRows],
    ] as $cfg)
        @php
            $group = $cfg['key'];
            $groupLabel = $cfg['label'];
            $initialRows = $cfg['rows'];
        @endphp
        <div class="mt-5"
             x-data="{
                 rows: @js($initialRows),
                 add() {
                     this.rows.push({
                         title: '', body: '', icon: '',
                         icon_media_id: '', icon_preview: null,
                         __uid: 'r_' + Math.random().toString(36).slice(2, 10),
                     });
                 },
                 remove(idx) { this.rows.splice(idx, 1); },
                 mediaUrl: @js(route('admin.media.index').'?popup=1&for=media-picker'),
                 openPicker(row) {
                     window._activeMediaPicker = {
                         store: 'id',
                         set value(v) { row.icon_media_id = v; },
                         get value() { return row.icon_media_id; },
                         set preview(p) { row.icon_preview = p; },
                     };
                     window.open(this.mediaUrl, 'media_picker_' + Math.random().toString(36).slice(2),
                         'width=1100,height=720,resizable=yes,scrollbars=yes');
                 },
                 clearIcon(row) { row.icon_media_id = ''; row.icon_preview = null; },
             }">
            <div class="mb-2 flex items-center justify-between">
                <h4 class="text-sm font-semibold text-slate-700">{{ __('content::achievement.cases.form.' . $groupLabel) }}</h4>
                <button type="button" @click="add()"
                        class="rounded bg-emerald-600 hover:bg-emerald-700 px-3 py-1.5 text-xs font-semibold text-white">
                    + {{ __('content::achievement.cases.form.add_row') }}
                </button>
            </div>

            <div class="space-y-3">
                <template x-for="(row, idx) in rows" :key="row.__uid">
                    <div class="grid grid-cols-1 gap-3 rounded border border-slate-200 bg-white p-3 md:grid-cols-[1fr_1fr_14rem_auto]">
                        <input type="text" x-model="row.title"
                               :name="`translations[{{ $loc }}][detail_content][{{ $group }}][${idx}][title]`"
                               placeholder="{{ __('content::achievement.cases.form.item_title') }}"
                               class="rounded border-slate-300 text-sm">

                        <input type="text" x-model="row.body"
                               :name="`translations[{{ $loc }}][detail_content][{{ $group }}][${idx}][body]`"
                               placeholder="{{ __('content::achievement.cases.form.item_body') }}"
                               class="rounded border-slate-300 text-sm">

                        {{-- Inline media picker bound to row.icon_media_id --}}
                        <div class="flex items-start gap-2">
                            <div class="h-16 w-16 flex-shrink-0 overflow-hidden rounded border border-slate-200 bg-slate-50 flex items-center justify-center">
                                <template x-if="row.icon_preview">
                                    <img :src="row.icon_preview" alt="" class="h-full w-full object-cover">
                                </template>
                                <template x-if="! row.icon_preview">
                                    <span class="text-[10px] text-slate-400">No image</span>
                                </template>
                            </div>
                            <div class="flex-1 space-y-1">
                                {{-- Hidden inputs persist legacy `icon` filename + new icon_media_id --}}
                                <input type="hidden" :name="`translations[{{ $loc }}][detail_content][{{ $group }}][${idx}][icon_media_id]`" :value="row.icon_media_id">
                                <input type="hidden" :name="`translations[{{ $loc }}][detail_content][{{ $group }}][${idx}][icon]`" :value="row.icon">
                                <div class="flex gap-1">
                                    <button type="button" @click="openPicker(row)"
                                            class="rounded bg-blue-600 hover:bg-blue-700 px-2 py-1 text-[11px] text-white">
                                        {{ __('Chọn ảnh') }}
                                    </button>
                                    <button type="button" x-show="row.icon_media_id" @click="clearIcon(row)"
                                            class="rounded bg-slate-200 hover:bg-slate-300 px-2 py-1 text-[11px] text-slate-700">
                                        {{ __('Bỏ') }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <button type="button" @click="remove(idx)"
                                class="self-center rounded bg-red-50 hover:bg-red-100 px-2 py-1.5 text-xs font-medium text-red-600"
                                :aria-label="`{{ __('content::achievement.cases.form.remove_row') }}`">
                            ×
                        </button>
                    </div>
                </template>

                <template x-if="rows.length === 0">
                    <p class="rounded border border-dashed border-slate-300 bg-slate-50 p-3 text-center text-xs text-slate-500">
                        {{ __('content::achievement.cases.form.no_rows') }}
                    </p>
                </template>
            </div>
        </div>
    @endforeach

    {{-- Center column image (between breakthrough_left and breakthrough_right
         on the public layout). Defaults to lungs.png if not picked. --}}
    <div class="mt-4">
        @php
            $breakCenterId = $text('breakthrough_center_media_id');
            $breakCenterModel = $breakCenterId ? \Packages\Core\Src\Models\MediaFile::find($breakCenterId) : null;
        @endphp
        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('content::achievement.cases.form.detail_breakthrough_center_media') }}</label>
        <x-core::media-picker
            :name="'translations[' . $loc . '][detail_content][breakthrough_center_media_id]'"
            :value="$breakCenterId"
            :preview-url="$breakCenterModel?->permalink"
            store="id"
        />
    </div>

    <div class="mt-4">
        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('content::achievement.cases.form.detail_breakthrough_note') }}</label>
        <textarea name="translations[{{ $loc }}][detail_content][breakthrough_note]" rows="3"
                  class="w-full rounded border-slate-300 text-sm">{{ $text('breakthrough_note') }}</textarea>
    </div>

    {{-- Choice items (Vì sao chọn Việt Nam): Alpine repeater. Each row = Title +
         Body (textarea) + Image (Media Picker). Field stored as image_media_id;
         public render falls back to the static $choiceImages array when no
         media is picked. --}}
    <div class="mt-5"
         x-data="{
             rows: @js($choiceRows),
             add() {
                 this.rows.push({
                     title: '', body: '',
                     image_media_id: '', image_preview: null,
                     __uid: 'r_' + Math.random().toString(36).slice(2, 10),
                 });
             },
             remove(idx) { this.rows.splice(idx, 1); },
             mediaUrl: @js(route('admin.media.index').'?popup=1&for=media-picker'),
             openPicker(row) {
                 window._activeMediaPicker = {
                     store: 'id',
                     set value(v) { row.image_media_id = v; },
                     get value() { return row.image_media_id; },
                     set preview(p) { row.image_preview = p; },
                 };
                 window.open(this.mediaUrl, 'media_picker_' + Math.random().toString(36).slice(2),
                     'width=1100,height=720,resizable=yes,scrollbars=yes');
             },
             clearImage(row) { row.image_media_id = ''; row.image_preview = null; },
         }">
        <div class="mb-2 flex items-center justify-between">
            <h4 class="text-sm font-semibold text-slate-700">{{ __('content::achievement.cases.form.detail_choice_items') }}</h4>
            <button type="button" @click="add()"
                    class="rounded bg-emerald-600 hover:bg-emerald-700 px-3 py-1.5 text-xs font-semibold text-white">
                + {{ __('content::achievement.cases.form.add_row') }}
            </button>
        </div>

        <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
            <template x-for="(row, idx) in rows" :key="row.__uid">
                <div class="relative rounded border border-slate-200 bg-white p-3">
                    <button type="button" @click="remove(idx)"
                            class="absolute top-2 right-2 rounded bg-red-50 hover:bg-red-100 px-2 py-0.5 text-xs font-medium text-red-600">
                        ×
                    </button>

                    <input type="text" x-model="row.title"
                           :name="`translations[{{ $loc }}][detail_content][choice_items][${idx}][title]`"
                           placeholder="{{ __('content::achievement.cases.form.item_title') }}"
                           class="mb-2 w-full rounded border-slate-300 text-sm">
                    <textarea x-model="row.body"
                              :name="`translations[{{ $loc }}][detail_content][choice_items][${idx}][body]`"
                              rows="2"
                              placeholder="{{ __('content::achievement.cases.form.item_body') }}"
                              class="mb-3 w-full rounded border-slate-300 text-sm"></textarea>

                    {{-- Inline media picker bound to row.image_media_id --}}
                    <div class="flex items-start gap-2">
                        <div class="h-20 w-28 flex-shrink-0 overflow-hidden rounded border border-slate-200 bg-slate-50 flex items-center justify-center">
                            <template x-if="row.image_preview">
                                <img :src="row.image_preview" alt="" class="h-full w-full object-cover">
                            </template>
                            <template x-if="! row.image_preview">
                                <span class="text-[10px] text-slate-400">No image</span>
                            </template>
                        </div>
                        <div class="flex-1 space-y-1">
                            <input type="hidden" :name="`translations[{{ $loc }}][detail_content][choice_items][${idx}][image_media_id]`" :value="row.image_media_id">
                            <div class="flex gap-1">
                                <button type="button" @click="openPicker(row)"
                                        class="rounded bg-blue-600 hover:bg-blue-700 px-2 py-1 text-[11px] text-white">
                                    {{ __('Chọn ảnh') }}
                                </button>
                                <button type="button" x-show="row.image_media_id" @click="clearImage(row)"
                                        class="rounded bg-slate-200 hover:bg-slate-300 px-2 py-1 text-[11px] text-slate-700">
                                    {{ __('Bỏ') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <template x-if="rows.length === 0">
                <p class="md:col-span-2 rounded border border-dashed border-slate-300 bg-slate-50 p-3 text-center text-xs text-slate-500">
                    {{ __('content::achievement.cases.form.no_rows') }}
                </p>
            </template>
        </div>
    </div>

    <div class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-2">
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('content::achievement.cases.form.detail_form_title') }}</label>
            <textarea name="translations[{{ $loc }}][detail_content][form_title]" rows="2"
                      class="w-full rounded border-slate-300 text-sm">{{ $text('form_title') }}</textarea>
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('content::achievement.cases.form.detail_form_body') }}</label>
            <textarea name="translations[{{ $loc }}][detail_content][form_body]" rows="2"
                      class="w-full rounded border-slate-300 text-sm">{{ $text('form_body') }}</textarea>
        </div>
    </div>

    <div class="mt-5">
        <h4 class="mb-2 text-sm font-semibold text-slate-700">{{ __('content::achievement.cases.form.detail_process_items') }}</h4>
        <div class="grid grid-cols-1 gap-2 md:grid-cols-4">
            @foreach($processRows as $i => $item)
                <input type="text" name="translations[{{ $loc }}][detail_content][process_items][{{ $i }}][body]"
                       value="{{ $item['body'] ?? '' }}" class="rounded border-slate-300 text-sm">
            @endforeach
        </div>
    </div>

    {{-- CTA final block.
         - cta_title: plain text (kept as textarea, same as before).
         - cta_body: CKEditor HTML — admin writes description + bullet list inside
           this one editor; the legacy 3 cta_points text inputs were removed. --}}
    <div class="mt-5">
        <label class="mb-1 block text-sm font-medium text-slate-700">{{ __('content::achievement.cases.form.detail_cta_title') }}</label>
        <textarea name="translations[{{ $loc }}][detail_content][cta_title]" rows="2"
                  class="w-full rounded border-slate-300 text-sm">{{ $text('cta_title') }}</textarea>
    </div>

    <div class="mt-4">
        <x-core::ckeditor
            :name="'translations[' . $loc . '][detail_content][cta_body]'"
            :value="$text('cta_body')"
            :label="__('content::achievement.cases.form.detail_cta_body')"
            rows="8"
        />
    </div>
</section>
