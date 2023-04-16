<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PdfFile extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'PDF ID' => $this->id,
            'PDF Name' => $this->name,
            'Upload Date' => $this->uploaded_at,
            'Number of Pages' => $this->page_count,
            'File Size (bytes)' => $this->size,
        ];
    }
}
