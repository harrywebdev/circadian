<?php

namespace App\Console\Commands;

use App\Circadian\Questions\AlcoholInEveningQuestion;
use App\Circadian\Questions\AnswerValidationException;
use App\Circadian\Questions\AnyAlcoholQuestion;
use App\Circadian\Questions\AnySmokesQuestions;
use App\Circadian\Questions\BreakfastQuestion;
use App\Circadian\Questions\DaylogQuestion;
use App\Circadian\Questions\DinnerQuestion;
use App\Circadian\Questions\FallAsleepQuestion;
use App\Circadian\Questions\LogDateQuestion;
use App\Circadian\Questions\WakeUpQuestion;
use App\Models\Daylog;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class LogCircadian extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'circadian:log';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Log Circadian';

    const STEP_DATE_IS_SET = 'date is set';
    const STEP_CREATE_RECORD = 'create record';
    const STEP_FILL_IN_BLANKS = 'fill in blanks';
    const STEP_START_OVER = 'start over';
    const STEP_PICK_ANOTHER_DATE = 'pick another date';
    const STEP_QUIT = 'quit';

    /**
     * @var CarbonImmutable
     */
    private $date;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->newLine();
        $this->line('=====================');
        $this->line('<fg=cyan>Welcome to Circadian.</>');
        $this->line('=====================');
        $this->newLine();

        // launch the machine
        $this->date = new CarbonImmutable($this->askQuestion(new LogDateQuestion()));

        return $this->goToNextStep(self::STEP_DATE_IS_SET);
    }

    /**
     * Keeps asking given question until valid answer is provided.
     *
     * @param DaylogQuestion $question
     *
     * @return string
     */
    public function askQuestion(DaylogQuestion $question): string
    {
        $questionWording = $question->getQuestion();
        if (!($question instanceof LogDateQuestion)) {
            $questionWording .= ' (leave empty to skip)';
        }

        $answer = $this->anticipate($questionWording, $question->getAnswerSuggestions());

        try {
            $question->validateAnswer($answer);

            return $answer;
        } catch (AnswerValidationException $e) {
            $this->error($e->getMessage());

            return $this->askQuestion($question);
        }
    }

    /**
     * @param string      $action
     * @param Daylog|null $currentDaylog
     *
     * @return int
     */
    private function goToNextStep(string $action, Daylog $currentDaylog = null)
    {
        switch ($action) {
            case self::STEP_DATE_IS_SET:
                return $this->dateIsSet();

            case self::STEP_PICK_ANOTHER_DATE:
                $this->date = null;
                return $this->handle();

            case self::STEP_QUIT:
                return $this->quit();

            case self::STEP_CREATE_RECORD:
                return $this->createRecord();

            case self::STEP_FILL_IN_BLANKS:
                assert($currentDaylog !== null);
                return $this->fillInBlanks($currentDaylog);

            case self::STEP_START_OVER:
                $currentDaylog->delete();

                return $this->createRecord();

            default:
                $this->error('I did not recognize this action: "' . $action . '". Sorry :(');
                return 1;
        }
    }

    /**
     * @return int
     */
    private function quit()
    {
        $this->info('Alright then. Goodbye.');
        $this->newLine();
        return 0;
    }

    /**
     * @param array $records
     */
    private function printTable(array $records)
    {
        // transform records to printable rows (of pure arrays)
        $records = collect($records)->map(function (Daylog $daylog) {
            return $this->getQuestionsCollection($daylog->log_date, [new LogDateQuestion()])
                ->reduce(function (array $item, DaylogQuestion $question) use ($daylog) {
                    $answer = $daylog->{$question->getDatabaseFieldName()};

                    $item[$question->getDatabaseFieldName()] = $question->serializeAnswer($answer);

                    return $item;
                }, []);
        });

        $this->table(
            $this->getQuestionsCollection(CarbonImmutable::now(), [new LogDateQuestion()])
                ->map(function (DaylogQuestion $question) {
                    return $question->getLabel();
                })->toArray(),
            $records
        );
    }

    /**
     * @param Daylog $daylog
     *
     * @return int
     */
    private function saveOrDiscardProcedure(Daylog $daylog)
    {
        $this->info('Perfect! Have a look-see before saving.');

        $this->printTable([$daylog]);
        $choice = $this->choice('Happy to keep?', ['save', 'discard'], 0);

        if ($choice == 'save') {
            $daylog->save();
            $this->info('Awesome, got it noted down.');

            return $this->goToNextStep(self::STEP_DATE_IS_SET);
        }

        $this->info('Alright, I\'ve dropped it.');

        return $this->goToNextStep(self::STEP_DATE_IS_SET);
    }

    /**
     * Creates and possibly preserves the record.
     *
     * @return int
     */
    private function createRecord()
    {
        $daylog           = new Daylog();
        $daylog->log_date = $this->date;

        $questions = $this->getQuestionsCollection($daylog->log_date);

        $skipQuestions = [];
        /** @var DaylogQuestion $question */
        foreach ($questions as $question) {
            if (in_array($question->getDatabaseFieldName(), $skipQuestions)) {
                continue;
            }

            $answer = $this->askQuestion($question);

            if ($answer != '') {
                $daylog->{$question->getDatabaseFieldName()} = $question->normalizeAnswer($answer);

                // HACK: no alcohol - we don't have to ask about alcohol in the evening, we set it to false right away
                if ($question instanceof AnyAlcoholQuestion && $daylog->{$question->getDatabaseFieldName()} === false) {
                    $alcoholInEveningQuestion                                    = new AlcoholInEveningQuestion();
                    $daylog->{$alcoholInEveningQuestion->getDatabaseFieldName()} = false;
                    $skipQuestions[]                                             =
                        $alcoholInEveningQuestion->getDatabaseFieldName();
                }
            }
        }

        return $this->saveOrDiscardProcedure($daylog);
    }

    /**
     * @param string $answer
     * @param array  $question
     *
     * @return bool|CarbonImmutable|\Carbon\Traits\Date|null
     */
    private function transformAnswer(string $answer, array $question)
    {
        switch ($question['type']) {
            case 'boolean':
                return preg_match('/yes|y/', $answer) ? true : (preg_match('/no|n/', $answer) ? false : null);
            case 'time':
                if (!preg_match('/([0-9]{1,2}):([0-9]{1,2})/', $answer, $matches)) {
                    return null;
                }

                return $this->date->clone()
                    ->setHours($matches[1])
                    ->setMinutes($matches[2])
                    ->setSeconds(0)
                    ->setMicroseconds(0);
            default:
                throw new \UnexpectedValueException('Unknown question type: "' . $question['type'] . '".');
        }
    }

    /**
     * Offer filling only `null` fields
     *
     * @param Daylog $currentDaylog
     *
     * @return int
     */
    private function fillInBlanks(Daylog $currentDaylog)
    {
        $daylog = $currentDaylog;

        $questions = $this->getQuestionsCollection($currentDaylog->log_date);

        /** @var DaylogQuestion $question */
        foreach ($questions as $question) {
            if ($daylog->{$question->getDatabaseFieldName()} !== null) {
                continue;
            }

            $answer = $this->askQuestion($question);
            if ($answer != '') {
                $daylog->{$question->getDatabaseFieldName()} = $question->normalizeAnswer($answer);
            }
        }

        return $this->saveOrDiscardProcedure($daylog);
    }

    /**
     * Signpost - after selecting date, let user pick their next action.
     *
     * @return int
     */
    private function dateIsSet()
    {
        $this->newLine();
        $this->line('We\'re talking ' . $this->date->format('l jS F') . ' then. Alright, let me check.');
        $this->newLine();

        /** @var Daylog $existingRecord */
        $existingRecord = Daylog::where('log_date', '=', $this->date->format('Y-m-d H:i:s'))->first();
        if ($existingRecord) {
            $this->info('I\'ve found a record for this date.');
            $this->printTable([$existingRecord]);

            $options = [self::STEP_START_OVER, self::STEP_PICK_ANOTHER_DATE, self::STEP_QUIT];
            if (!$existingRecord->isComplete) {
                array_unshift($options, self::STEP_FILL_IN_BLANKS);
            }

            $action = $this->choice('What would you like to do?', $options);

            return $this->goToNextStep($action, $existingRecord);
        } else {
            $this->info('Looks like I don\'t have a record for this date.');
            $action = $this->choice('What would you like to do?',
                [self::STEP_CREATE_RECORD, self::STEP_PICK_ANOTHER_DATE, self::STEP_QUIT], 0);

            return $this->goToNextStep($action);
        }
    }

    private function getQuestionsCollection(CarbonImmutable $currentDate, array $prependQuestions = [])
    {
        return collect(array_merge($prependQuestions, [
            new WakeUpQuestion($currentDate),
            new BreakfastQuestion($currentDate),
            new DinnerQuestion($currentDate),
            new FallAsleepQuestion($currentDate),
            new AnyAlcoholQuestion(),
            new AlcoholInEveningQuestion(),
            new AnySmokesQuestions(),
        ]));
    }
}
