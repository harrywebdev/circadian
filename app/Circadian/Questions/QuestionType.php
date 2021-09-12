<?php

namespace App\Circadian\Questions;

use MyCLabs\Enum\Enum;

/**
 * Class QuestionType
 *
 * @package App\Circadian\Questions
 * @method static QuestionType TIME()
 * @method static QuestionType DATE()
 * @method static QuestionType BOOLEAN()
 */
final class QuestionType extends Enum
{
    private const TIME = 'time';
    private const DATE = 'date';
    private const BOOLEAN = 'boolean';
}
