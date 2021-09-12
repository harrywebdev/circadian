<?php


namespace App\Circadian\Questions;


use Carbon\CarbonImmutable;

abstract class QuestionHasCurrentDate
{
    /**
     * @var CarbonImmutable
     */
    protected $currentDate;

    /**
     * WakeUpQuestion constructor.
     *
     * @param CarbonImmutable $currentDate
     */
    public function __construct(CarbonImmutable $currentDate)
    {
        $this->currentDate = $currentDate;
    }
}
