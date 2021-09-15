<?php


namespace App\Circadian\Questions;


use Carbon\CarbonImmutable;

trait QuestionsChoice
{
    public function getType(): QuestionType
    {
        return QuestionType::BOOLEAN();
    }

    public function validateAnswer($answer = null): bool
    {
        if ($answer === null || is_bool($answer)) {
            return true;
        }

        if (!preg_match('/yes|y|no|n/i', $answer)) {
            throw new AnswerValidationException('Invalid choice format, expecting "yes" ("y") or "no" ("n")');
        }

        return true;
    }

    public function normalizeAnswer($answer = null)
    {
        if (is_bool($answer)) {
            return $answer;
        }
        
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
