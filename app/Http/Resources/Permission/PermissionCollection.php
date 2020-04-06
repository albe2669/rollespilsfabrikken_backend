<?php

namespace App\Http\Resources\Permission;

use Illuminate\Http\Resources\Json\ResourceCollection;

class PermissionCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'permissions' => $this->collection,
            'links' => [
                'first_page' => $this->url(1),
                'last_page' => $this->url($this->lastPage()),
                'prev_page'  => $this->previousPageUrl(),
                'next_page'  => $this->nextPageUrl(),
            ],
            'meta' => [
                'current_page' => $this->currentPage(),
                'first_item' => $this->firstItem(),
                'last_item' => $this->lastItem(),
                'per_page' => $this->perPage(),
                'total' => $this->total()
            ]
        ];
    }
}
