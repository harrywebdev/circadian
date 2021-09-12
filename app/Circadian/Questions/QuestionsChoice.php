<?php


namespace App\Circadian\Questions;


use Carbon\CarbonImmutable;

trait QuestionsChoice
{
    public function getType(): QuestionType
    {
        return QuestionType::BOOLEAN();
    }

    public function validateAnswer(string $answer): bool
    {
        if (!preg_match('/yes|y|no|n/i', $answer)) {
            throw new AnswerValidationException('Invalid choice format, expecting "yes" ("y") or "no" ("n")');
        }

        return true;
    }

    public function normalizeAnswer(string $answer)
    {
        return preg_match('/yes|y/i', $answer) ? true : (preg_match('/no|n/i', $answer) ? false : null);
    }

    public function serializeAnswer($answer): string
    {
        if ($answer === null) {
            return 'n/a';
        }

        return $answer ? 'yes' : 'no';
    }

    public function getAnswerSuggestions(): array
    {
        return ['yes', 'no'];
    }
}
