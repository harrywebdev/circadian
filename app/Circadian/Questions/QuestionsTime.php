<?php


namespace App\Circadian\Questions;


use Carbon\CarbonImmutable;

trait QuestionsTime
{
    public function getType(): QuestionType
    {
        return QuestionType::TIME();
    }

    public function validateAnswer($answer = null): bool
    {
        if ($answer === null) {
            return true;
        }

        if (!preg_match('/([0-9]{1,2}):([0-9]{1,2})/', $answer, $matches)) {
            throw new AnswerValidationException('Invalid time format, expecting HH:MM');
        }

        return true;
    }

    public function normalizeAnswerWithCurrentDate(string $answer = null, CarbonImmutable $currentDate)
    {
        if ($answer === null) {
            return null;
        }

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
        if ($answer === null) {
            return 'n/a';
        }

        if (!($answer instanceof CarbonImmutable)) {
            $answer = new CarbonImmutable($answer);
        }

        return $answer->format('H:i');
    }

    public function getAnswerSuggestions(): array
    {
        return [];
    }
}
