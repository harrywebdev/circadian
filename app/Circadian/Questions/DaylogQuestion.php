<?php


namespace App\Circadian\Questions;


interface DaylogQuestion
{
    /**
     * Name of DB column
     *
     * @return string
     */
    public function getDatabaseFieldName(): string;

    /**
     * How to present question in Table view
     *
     * @return string
     */
    public function getLabel(): string;

    /**
     * Actual question wording
     *
     * @return string
     */
    public function getQuestion(): string;

    /**
     * Type of question
     *
     * @return QuestionType
     */
    public function getType(): QuestionType;

    /**
     * Determines whether given answer is valid
     *
     * @param string $answer
     *
     * @return bool
     */
    public function validateAnswer(string $answer): bool;

    /**
     * Normalizes input for storage
     *
     * @param string $answer
     *
     * @return mixed
     */
    public function normalizeAnswer(string $answer);

    /**
     * How to present answer in Table view
     *
     * @param mixed $answer
     *
     * @return string
     */
    public function serializeAnswer($answer): string;
}
