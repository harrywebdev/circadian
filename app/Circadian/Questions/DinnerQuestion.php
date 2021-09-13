<?php


namespace App\Circadian\Questions;


class DinnerQuestion extends QuestionHasCurrentDate implements DaylogQuestion
{
    use QuestionsTime;

    public function getDatabaseFieldName(): string
    {
        return 'last_meal_at';
    }

    public function getLabel(): string
    {
        return 'Last meal at';
    }

    public function getQuestion(): string
    {
        return 'What time did you have your *last* bite or sip?';
    }

    public function normalizeAnswer(string $answer = null)
    {
        return $this->normalizeAnswerWithCurrentDate($answer, $this->currentDate);
    }
}
