<?php


namespace App\Circadian\Questions;


class AlcoholInEveningQuestion implements DaylogQuestion
{
    use QuestionsChoice;

    public function getDatabaseFieldName(): string
    {
        return 'has_alcohol_in_evening';
    }

    public function getLabel(): string
    {
        return 'Alcohol in the evening';
    }

    public function getQuestion(): string
    {
        return 'Did you drink any alcohol in the evening?';
    }
}
