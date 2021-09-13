<?php


namespace App\Circadian\Questions;


use Illuminate\Support\Collection;

class QuestionsCollection extends Collection
{
    /**
     * @param string $field Database field name
     *
     * @return DaylogQuestion|null
     */
    public function findByDatabaseFieldName(string $field)
    {
        return $this->filter(function (DaylogQuestion $question) use ($field) {
            return $question->getDatabaseFieldName() === $field;
        })->first();
    }
}
