<?php


namespace App\Circadian\Questions;


class WakeUpQuestion extends QuestionHasCurrentDate implements DaylogQuestion
{
    use QuestionsTime;

    public function getDatabaseFieldName(): string
    {
        return 'wake_at';
    }

    public function getLabel(): string
    {
        return 'Woke up at';
    }

    public function getQuestion(): string
    {
        return 'What time did you wake up?';
    }

    public function normalizeAnswer(string $answer)
    {
        return $this->normalizeAnswerWithCurrentDate($answer, $this->currentDate);
    }
}
