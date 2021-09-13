<?php

namespace App\Http\Resources;

use App\Circadian\Questions\DaylogQuestion;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestionResource extends JsonResource
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
        /** @var DaylogQuestion $this */
        return [
            'db_field_name'      => $this->getDatabaseFieldName(),
            'label'              => $this->getLabel(),
            'question'           => $this->getQuestion(),
            'type'               => $this->getType(),
            'answer_suggestions' => $this->getAnswerSuggestions(),
        ];
    }
}
