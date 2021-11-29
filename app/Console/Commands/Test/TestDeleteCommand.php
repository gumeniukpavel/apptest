<?php

namespace App\Console\Commands\Test;

use App\Db\Service\TestDao;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TestDeleteCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:delete {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete test';

    private TestDao $testDao;

    public function __construct(
        TestDao $testDao
    )
    {
        parent::__construct();
        $this->testDao = $testDao;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $test = $this->testDao->getOne($this->argument('id'));
        if ($test)
        {

            try
            {
                $this->testDao->deleteTest($test);
            }
            catch (\Exception $exception)
            {
                Log::error($exception);
                $this->info("Sending error: {$exception->getMessage()}");
            }
        }
        else
        {
            $this->info("Test not found");
        }
        return 0;
    }
}
