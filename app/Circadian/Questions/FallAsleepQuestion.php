<?php


namespace App\Circadian\Questions;


class FallAsleepQuestion extends QuestionHasCurrentDate implements DaylogQuestion
{
    use QuestionsTime;

    public function getDatabaseFieldName(): string
    {
        return 'sleep_at';
    }

    public function getLabel(): string
    {
        return 'Went to bed at';
    }

    public function getQuestion(): string
    {
        return 'What time did you fall asleep?';
    }

    public function normalizeAnswer(string $answer)
    {
        return $this->normalizeAnswerWithCurrentDate($answer, $this->currentDate);
    }
}
