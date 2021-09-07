<?php

namespace App\Console\Commands;

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
    const STEP_QUIT = 'quit';

    const MODEL_FIELDS = [
        ['field' => 'log_date', 'label' => 'Date', 'type' => 'date'],
        ['field' => 'wake_at', 'label' => 'Woke up at', 'type' => 'time'],
        ['field' => 'first_meal_at', 'label' => 'First meal at', 'type' => 'time'],
        ['field' => 'last_meal_at', 'label' => 'Last meal at', 'type' => 'time'],
        ['field' => 'sleep_at', 'label' => 'Went to bed at', 'type' => 'time'],
        ['field' => 'has_alcohol', 'label' => 'Any alcohol', 'type' => 'boolean'],
        ['field' => 'has_alcohol_in_evening', 'label' => 'Alcohol in the evening', 'type' => 'boolean'],
        ['field' => 'has_smoked', 'label' => 'Any smokes', 'type' => 'boolean'],
    ];
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

        $date = $this->anticipate('What date you have in mind?',
            ['today', 'yesterday', CarbonImmutable::now()->format('Y-m-d')]);

        try {
            $this->date = new CarbonImmutable($date);
        } catch (\Exception $e) {
            $this->error('I did not recognize that as a date. Please, try again.');
            return $this->handle();
        }

        return $this->goToNextStep(self::STEP_DATE_IS_SET);
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
            case self::STEP_QUIT:
                return $this->quit();

            case self::STEP_CREATE_RECORD:
                return $this->createRecord();

            case self::STEP_FILL_IN_BLANKS:
                assert($currentDaylog !== null);
                return $this->fillInBlanks($currentDaylog);

            case self::STEP_DATE_IS_SET:
                return $this->dateIsSet();

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
        return 0;
    }

    /**
     * @param array $records
     */
    private function printTable(array $records)
    {
        // transform records to printable rows (of pure arrays)
        $records = collect($records)->map(function (Daylog $daylog) {
            return collect(self::MODEL_FIELDS)->reduce(function (array $item, array $mapping) use ($daylog) {
                $field = $daylog->{$mapping['field']};
                switch (true) {
                    case $field instanceof CarbonImmutable:
                        // format time only
                        if (preg_match('/_at$/', $mapping['field'])) {
                            $item[$mapping['field']] = $field->format('H:i');
                            break;
                        }

                        $item[$mapping['field']] = $field->format('j. n. Y');
                        break;
                    case $field === null:
                        $item[$mapping['field']] = 'n/a';
                        break;
                    case gettype($field) === 'boolean':
                        $item[$mapping['field']] = $field ? '<fg=red>yes</>' : '<fg=green>no</>';
                        break;
                    default:
                        $item[$mapping['field']] = $field;
                        break;
                }

                return $item;
            }, []);
        });

        $this->table(
            collect(array_values(self::MODEL_FIELDS))->map(function ($item) {
                return $item['label'];
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

        $questions = array_slice(self::MODEL_FIELDS, 1);

        foreach ($questions as $question) {
            $answer = $this->ask($question['label'] . '? (leave empty to skip)');
            if ($answer != '') {
                $daylog->{$question['field']} = $this->transformAnswer($answer, $question);
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

        $questions = array_slice(self::MODEL_FIELDS, 1);

        foreach ($questions as $question) {
            if ($daylog->{$question['field']} !== null) {
                continue;
            }

            $answer = $this->ask($question['label'] . '? (leave empty to skip)');
            if ($answer != '') {
                $daylog->{$question['field']} = $this->transformAnswer($answer, $question);
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
        $this->line('We\'re talking ' . $this->date->format('l jS F') . ' then. Alright, let me check.');
        $this->newLine();

        /** @var Daylog $existingRecord */
        $existingRecord = Daylog::where('log_date', '=', $this->date->format('Y-m-d H:i:s'))->first();
        if ($existingRecord) {
            $this->info('I\'ve found a record for this date.');
            $this->printTable([$existingRecord]);

            $options = [self::STEP_START_OVER, self::STEP_QUIT];
            if (!$existingRecord->isComplete) {
                array_unshift($options, self::STEP_FILL_IN_BLANKS);
            }

            $action = $this->choice('What would you like to do?', $options, 0);

            return $this->goToNextStep($action, $existingRecord);
        } else {
            $this->info('Looks like I don\'t have a record for this date.');
            $action = $this->choice('What would you like to do?', [self::STEP_CREATE_RECORD, self::STEP_QUIT], 0);

            return $this->goToNextStep($action);
        }
    }
}
