<?php

namespace App\Console\Commands\TestPayment;

use App\Db\Entity\Payment;
use App\Db\Entity\Role;
use App\Db\Entity\Tariff;
use App\Db\Entity\User;
use Illuminate\Console\Command;

class TestPaymentDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test-data:fill';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'To add an event with test data';

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
        /** @var User $user */
        $user = User::query()
            ->where('role_id', Role::ROLE_CUSTOMER)
            ->first();

        if (!$user)
        {
            $this->info('User not found.');
            return 1;
        }

        /** @var Tariff $tariff */
        $tariff = Tariff::query()->first();

        if (!$tariff)
        {
            $this->info('Tariff not found.');
            return 1;
        }

        Payment::factory()->count(10)->create([
            'user_id' => $user->id,
            'tariff_id' => $tariff->id,
            'status' => Payment::PAYMENT_STATUS_PENDING
        ]);

        return 0;
    }
}
