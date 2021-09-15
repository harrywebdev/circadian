<?php


namespace App\Circadian\Questions;


class BreakfastQuestion extends QuestionHasCurrentDate implements DaylogQuestion
{
    use QuestionsTime;

    public function getDatabaseFieldName(): string
    {
        return 'first_meal_at';
    }

    public function getLabel(): string
    {
        return 'First meal at';
    }

    public function getQuestion(): string
    {
        return 'What time did you have your *first* bite or sip?';
    }

    public function normalizeAnswer($answer = null)
    {
        return $this->normalizeAnswerWithCurrentDate($answer, $this->currentDate);
    }
}
