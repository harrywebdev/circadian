<?php


namespace App\Circadian\Questions;


class AnyAlcoholQuestion implements DaylogQuestion
{
    use QuestionsChoice;

    public function getDatabaseFieldName(): string
    {
        return 'has_alcohol';
    }

    public function getLabel(): string
    {
        return 'Any alcohol';
    }

    public function getQuestion(): string
    {
        return 'Did you have any alcohol on that day?';
    }
}
