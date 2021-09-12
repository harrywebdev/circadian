<?php


namespace App\Circadian\Questions;


class AnySmokesQuestions implements DaylogQuestion
{
    use QuestionsChoice;

    public function getDatabaseFieldName(): string
    {
        return 'has_smoked';
    }

    public function getLabel(): string
    {
        return 'Any smokes';
    }

    public function getQuestion(): string
    {
        return 'Did you smoke at all on that day?';
    }
}
