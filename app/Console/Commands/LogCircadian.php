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
        $this->line('Welcome to Circadian.');

        $date = $this->anticipate('What date you have in mind?',
            ['today', 'yesterday', CarbonImmutable::now()->format('Y-m-d')]);

        try {
            $date = new CarbonImmutable($date);
        } catch (\Exception $e) {
            $this->error('I did not recognize that as a date. Please, try again.');
            return $this->handle();
        }

        $this->line('We\'re talking ' . $date->format('l jS F') . ' then. Alright, let me check.');

        $existingRecord = Daylog::where('date', '=', $date->format('Y-m-d'))->first();
        if ($existingRecord) {
            $this->info('I\'ve found a record for this date.');
            $this->printTable($existingRecord);
            $action = $this->choice('What would you like to do?', ['fill in blanks', 'start over', 'quit']);

            $this->line('Action chosen: ' . $action);
            $this->line('Goodbye.');
        } else {
            $this->info('Looks like I don\'t have a record for this date.');
            $action = $this->choice('What would you like to do?', ['create record', 'quit']);

            $this->line('Action chosen: ' . $action);
            $this->line('Goodbye.');
        }

        return 0;
    }

    private function printTable($existingRecord)
    {
        $this->table(
            [
                'Date',
                'Woke up at',
                'First meal at',
                'Last meal at',
                'Went to bed at',
                'Any alcohol?',
                'Alcohol in the evening?',
                'Any smokes?',
            ],
            $existingRecord->all([
                'log_date',
                'wake_at',
                'first_meal_at',
                'last_meal_at',
                'sleep_at',
                'has_alcohol',
                'has_alcohol_in_evening',
                'has_smoked',
            ])->toArray()
        );
    }
}
