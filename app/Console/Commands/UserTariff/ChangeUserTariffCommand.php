<?php

namespace App\Console\Commands\UserTariff;

use App\Db\Entity\Tariff;
use App\Db\Entity\User;
use App\Db\Service\TariffDao;
use App\Db\Service\TariffUserDao;
use App\Db\Service\UserDao;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ChangeUserTariffCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:change-tariff {userId} {tariffId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Change user tariff';

    private TariffUserDao $tariffUserDao;
    private TariffDao $tariffDao;
    private UserDao $userDao;

    public function __construct(
        TariffUserDao $tariffUserDao,
        TariffDao $tariffDao,
        UserDao $userDao
    )
    {
        parent::__construct();
        $this->tariffUserDao = $tariffUserDao;
        $this->tariffDao = $tariffDao;
        $this->userDao = $userDao;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $validator = Validator::make([
            'userId' => $this->argument('userId'),
            'tariffId' => $this->argument('tariffId'),
        ], [
            'userId' => [
                'required',
                Rule::exists(User::class, 'id')
            ],
            'tariffId' => [
                'required',
                Rule::exists(Tariff::class, 'id')
            ],
        ]);

        if ($validator->fails()) {
            $this->info('User tariff not changed. See error messages below:');

            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return 1;
        }

        $user = $this->userDao->firstWithData($this->argument('userId'));
        $tariff = $this->tariffDao->getOne($this->argument('tariffId'));

        try
        {
            $this->tariffUserDao->changeTariffForUser($user, $tariff);
            $this->info("Tariff successfully changed");
        }
        catch (\Exception $exception)
        {
            Log::error($exception);
            $this->info("Sending error: {$exception->getMessage()}");
            return 1;
        }
        return 0;
    }
}
