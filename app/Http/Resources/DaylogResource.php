<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DaylogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id'                     => $this->id,
            'is_complete'            => $this->is_complete,
            'log_date'               => $this->log_date,
            'has_alcohol'            => $this->has_alcohol,
            'has_alcohol_in_evening' => $this->has_alcohol_in_evening,
            'has_smoked'             => $this->has_smoked,
            'wake_at'                => $this->wake_at,
            'first_meal_at'          => $this->first_meal_at,
            'last_meal_at'           => $this->last_meal_at,
            'sleep_at'               => $this->sleep_at,
        ];
    }
}
