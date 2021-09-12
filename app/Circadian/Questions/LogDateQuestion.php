<?php


namespace App\Circadian\Questions;


use Carbon\CarbonImmutable;

class LogDateQuestion implements DaylogQuestion
{

    public function getDatabaseFieldName(): string
    {
        return 'log_date';
    }

    public function getLabel(): string
    {
        return 'Date';
    }

    public function getQuestion(): string
    {
        return 'What date you have in mind?';
    }

    public function getType(): QuestionType
    {
        return QuestionType::DATE();
    }

    public function validateAnswer(string $answer): bool
    {
        try {
            new CarbonImmutable($answer);

            return true;
        } catch (\Exception $e) {
            throw new AnswerValidationException('I did not recognize that as a date. Please, try again.');
        }
    }

    public function normalizeAnswer(string $answer)
    {
        return (new CarbonImmutable($answer))->format('Y-m-d');
    }

    public function serializeAnswer($answer): string
    {
        if ($answer === null) {
            return 'n/a';
        }

        if (!($answer instanceof CarbonImmutable)) {
            $answer = new CarbonImmutable($answer);
        }

        return $answer->format('j. n. Y');
    }

    public function getAnswerSuggestions(): array
    {
        return ['today', 'yesterday', CarbonImmutable::now()->format('Y-m-d')];
    }
}
