<?php


namespace App\Circadian\Questions;


use Carbon\CarbonImmutable;

trait QuestionsTime
{
    public function getType(): QuestionType
    {
        return QuestionType::TIME();
    }

    public function validateAnswer(string $answer): bool
    {
        if (!preg_match('/([0-9]{1,2}):([0-9]{1,2})/', $answer, $matches)) {
            throw new AnswerValidationException('Invalid time format, expecting HH:MM');
        }
    }

    public function normalizeAnswerWithCurrentDate(string $answer, CarbonImmutable $currentDate)
    {
        preg_match('/([0-9]{1,2}):([0-9]{1,2})/', $answer, $matches);

        return $currentDate->clone()
            ->setHours($matches[1])
            ->setMinutes($matches[2])
            ->setSeconds(0)
            ->setMicroseconds(0)
            ->toDateTimeString();
    }

    public function serializeAnswer($answer): string
    {
        if (!($answer instanceof CarbonImmutable)) {
            $answer = new CarbonImmutable($answer);
        }

        return $answer->format('H:i');
    }

}
