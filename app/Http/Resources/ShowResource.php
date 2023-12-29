<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShowResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $show = parent::toArray($request);

        $show['moderators'] = $this->moderators->map(function ($moderator) {
            return [
                'id' => $moderator->id,
                'name' => $moderator->name,
                'primary' => $moderator->moderators->primary === 1,
            ];
        });

        return $show;
    }
}
