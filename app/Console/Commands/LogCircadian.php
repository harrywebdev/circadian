<?php

namespace App\Console\Commands;

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

        

        return 0;
    }
}
