<?php

namespace Packages\Core\Src\Http\Requests\Media;

use Illuminate\Foundation\Http\FormRequest;
use Packages\Core\Src\Models\MediaSetting;

class UploadFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasPermission('media.create');
    }

    public function rules(): array
    {
        $maxSize = $this->getMaxSizeKB();
        $allowedMimes = $this->getAllowedExtensions();

        return [
            'file' => [
                'required',
                'file',
                "max:{$maxSize}",
                "mimes:{$allowedMimes}",
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => trans('core-media::media.message.file_not_uploaded'),
            'file.max' => trans('core-media::media.message.file_too_big', [
                'size' => $this->formatSize($this->getMaxSizeKB() * 1024),
            ]),
            'file.mimes' => trans('core-media::media.message.can_not_detect_file_type'),
        ];
    }

    /**
     * Get max file size in KB from MediaSetting.
     */
    protected function getMaxSizeKB(): int
    {
        $bytes = MediaSetting::getValue('media_max_file_size', 10485760);

        return (int) ceil($bytes / 1024);
    }

    /**
     * Get allowed extensions as comma-separated string.
     */
    protected function getAllowedExtensions(): string
    {
        $raw = MediaSetting::getValue(
            'media_allowed_mime_types',
            'jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx,ppt,pptx,mp4,mp3,zip'
        );

        // Remove svg for security (prevent stored XSS)
        $extensions = is_string($raw) ? explode(',', $raw) : (array) $raw;
        $extensions = array_filter(
            array_map('trim', $extensions),
            fn ($ext) => strtolower($ext) !== 'svg'
        );

        return implode(',', $extensions);
    }

    /**
     * Format bytes for display.
     */
    protected function formatSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2).' '.$units[$i];
    }
}
