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

    const MAPPING = [
        'log_date'               => ['label' => 'Date', 'type' => 'date'],
        'wake_at'                => ['label' => 'Woke up at', 'type' => 'time'],
        'first_meal_at'          => ['label' => 'First meal at', 'type' => 'time'],
        'last_meal_at'           => ['label' => 'Last meal at', 'type' => 'time'],
        'sleep_at'               => ['label' => 'Went to bed at', 'type' => 'time'],
        'has_alcohol'            => ['label' => 'Any alcohol', 'type' => 'boolean'],
        'has_alcohol_in_evening' => ['label' => 'Alcohol in the evening', 'type' => 'boolean'],
        'has_smoked'             => ['label' => 'Any smokes', 'type' => 'boolean'],
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
        $this->line('Welcome to Circadian.');
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

        $this->line('We\'re talking ' . $this->date->format('l jS F') . ' then. Alright, let me check.');
        $this->newLine();

        $existingRecord = Daylog::where('log_date', '=', $this->date->format('Y-m-d H:i:s'))->first();
        if ($existingRecord) {
            $this->info('I\'ve found a record for this date.');
            $this->printTable($existingRecord);
            $action = $this->choice('What would you like to do?', ['fill in blanks', 'start over', 'quit'], 0);

            return $this->goToNextStep($action);
        } else {
            $this->info('Looks like I don\'t have a record for this date.');
            $action = $this->choice('What would you like to do?', ['create record', 'quit'], 0);

            return $this->goToNextStep($action);
        }
    }

    /**
     * @param string $action
     *
     * @return int
     */
    private function goToNextStep(string $action)
    {
        switch ($action) {
            case 'quit':
                return $this->quit();

            case 'create record':
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
        return 0;
    }

    /**
     * @param Daylog $existingRecord
     */
    private function printTable(Daylog $existingRecord)
    {
        $this->table(
            collect(array_values(self::MAPPING))->map(function ($item) {
                return $item['label'];
            })->toArray(),
            [$existingRecord->only(array_keys(self::MAPPING))]
        );
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

        $questions = array_slice(self::MAPPING, 1);

        foreach ($questions as $field => $question) {
            $answer = $this->ask($question['label'] . '? (leave empty to skip)');
            if ($answer != '') {
                $daylog->{$field} = $this->transformAnswer($answer, $question);
            }
        }

        $this->info('Perfect! Have a look-see before saving.');

        $this->printTable($daylog);
        $choice = $this->choice('Happy to keep?', ['save', 'discard'], 0);

        if ($choice == 'save') {
            $daylog->save();
            $this->info('Awesome, got it noted down.');

            return $this->handle();
        }

        $this->info('Alright, I\'ve dropped it.');

        return $this->handle();
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
                if (!preg_match('/([0-9]{2}):([0-9]{2})/', $answer, $matches)) {
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
}
