<?php

namespace Packages\Core\Src\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaFileResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'alt' => $this->alt,
            'permalink' => $this->permalink,
            'size' => $this->size,
            'mine_type' => $this->mine_type,
            'user_id' => $this->user_id,
            'folder_id' => $this->folder_id,
            'created_at' => Carbon::parse($this->created_at)->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::parse($this->updated_at)->format('Y-m-d H:i:s'),
            'deleted_at' => $this->deleted_at ? Carbon::parse($this->deleted_at)->format('Y-m-d H:i:s') : null,
        ];
    }
}
