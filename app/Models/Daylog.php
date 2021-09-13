<?php

namespace App\Models;

use App\Circadian\Questions\AlcoholInEveningQuestion;
use App\Circadian\Questions\AnyAlcoholQuestion;
use App\Circadian\Questions\AnySmokesQuestions;
use App\Circadian\Questions\BreakfastQuestion;
use App\Circadian\Questions\DinnerQuestion;
use App\Circadian\Questions\FallAsleepQuestion;
use App\Circadian\Questions\LogDateQuestion;
use App\Circadian\Questions\QuestionHasCurrentDate;
use App\Circadian\Questions\QuestionsCollection;
use App\Circadian\Questions\WakeUpQuestion;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Daylog
 *
 * @package App\Models
 * @property boolean $isComplete
 */
class Daylog extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'daylog';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'has_alcohol',
        'has_alcohol_in_evening',
        'has_smoked',
        'wake_at',
        'first_meal_at',
        'last_meal_at',
        'sleep_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'has_alcohol'            => 'boolean',
        'has_alcohol_in_evening' => 'boolean',
        'has_smoked'             => 'boolean',
        'wake_at'                => 'datetime',
        'first_meal_at'          => 'datetime',
        'last_meal_at'           => 'datetime',
        'sleep_at'               => 'datetime',
        'log_date'               => 'date',
    ];

    /**
     * @param array $answers
     *
     * @return $this
     */
    public function fillAnswers(array $answers)
    {
        $questions = Daylog::getQuestions($this->log_date);

        foreach ($answers as $field => $answer) {
            $question = $questions->findByDatabaseFieldName($field);

            if (!$question || !in_array($field, $this->fillable)) {
                throw new \UnexpectedValueException('Unexpected field: ' . $field);
            }

            $question->validateAnswer($answer);

            $this->{$field} = $question->normalizeAnswer($answer);
        }

        return $this;
    }

    /**
     * @param CarbonImmutable $currentDate
     *
     * @return QuestionsCollection
     */
    public static function getQuestions(CarbonImmutable $currentDate)
    {
        return QuestionsCollection::make([
            new LogDateQuestion(),
            new WakeUpQuestion($currentDate),
            new BreakfastQuestion($currentDate),
            new DinnerQuestion($currentDate),
            new FallAsleepQuestion($currentDate),
            new AnyAlcoholQuestion(),
            new AlcoholInEveningQuestion(),
            new AnySmokesQuestions(),
        ]);
    }

    /**
     * @return bool
     */
    public function getIsCompleteAttribute()
    {
        return $this->log_date &&
            $this->sleep_at !== null &&
            $this->last_meal_at !== null &&
            $this->first_meal_at !== null &&
            $this->wake_at !== null &&
            $this->has_smoked !== null &&
            $this->has_alcohol_in_evening !== null &&
            $this->has_alcohol !== null;
    }
}
