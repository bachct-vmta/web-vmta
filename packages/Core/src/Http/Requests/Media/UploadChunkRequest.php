<?php

namespace Packages\Core\Src\Http\Requests\Media;

use Illuminate\Foundation\Http\FormRequest;
use Packages\Core\Src\Models\MediaSetting;

class UploadChunkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasPermission('media.create');
    }

    public function rules(): array
    {
        $chunkSize = $this->getChunkSizeKB();

        return [
            'file' => [
                'required',
                'file',
                "max:{$chunkSize}",
            ],
            'resumableChunkNumber' => 'required|integer|min:1',
            'resumableTotalChunks' => 'required|integer|min:1',
            'resumableIdentifier' => 'required|string|max:255',
            'resumableFilename' => 'required|string|max:255',
            'resumableTotalSize' => 'nullable|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Chunk data is required.',
            'file.max' => 'Chunk size exceeds the configured limit.',
            'resumableChunkNumber.required' => 'Chunk number is required.',
            'resumableTotalChunks.required' => 'Total chunks count is required.',
            'resumableIdentifier.required' => 'File identifier is required.',
            'resumableFilename.required' => 'Filename is required.',
        ];
    }

    /**
     * Get chunk size in KB from MediaSetting.
     */
    protected function getChunkSizeKB(): int
    {
        $bytes = MediaSetting::getValue('media_chunk_size', 1048576);

        // Allow 10% overhead for encoding
        return (int) ceil(($bytes * 1.1) / 1024);
    }
}
