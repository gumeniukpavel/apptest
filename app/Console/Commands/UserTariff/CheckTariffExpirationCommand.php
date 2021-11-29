<?php

namespace App\Console\Commands\UserTariff;

use App\Db\Entity\TariffUser;
use App\Notifications\User\TariffExpirationNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckTariffExpirationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:tariff-expiration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checking the expiration of the users tariff';

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
        /** @var TariffUser $activeTariffs */
        $activeTariffs = TariffUser::query()->where('is_active', '=' , true)->get();

        foreach ($activeTariffs as $activeTariff)
        {
            $tariffEndDate = Carbon::createFromTimestamp($activeTariff->ended_at);
            $leftDays = $tariffEndDate->diffInDays(Carbon::now());

            if ($leftDays == 7 || ($leftDays <= 4 && $leftDays >= 0))
            {
                $tariffName = $activeTariff->tariff->name;
                $user = $activeTariff->user;
                $user->notify(new TariffExpirationNotification($user, $tariffName, $leftDays, $tariffEndDate));
            }
        }
        return 0;
    }
}
