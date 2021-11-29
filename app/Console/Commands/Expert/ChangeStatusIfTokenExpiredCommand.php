<?php

namespace App\Console\Commands\Expert;

use App\Constant\ApprovalRequestStatus;
use App\Constant\ResultOfChecking;
use App\Db\Entity\QuestionnaireApprovalRequest;
use App\Db\Entity\TestApprovalRequest;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ChangeStatusIfTokenExpiredCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'approval-request:expired-token';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Change status';

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
        TestApprovalRequest::query()
            ->whereNull('result_of_checking')
            ->where('updated_at', '<', Carbon::now()->subDays(3)->timestamp)
            ->whereIn('status', [
                ApprovalRequestStatus::$Pending->getValue(),
                ApprovalRequestStatus::$Approved->getValue(),
            ])
            ->update([
                'status' => ApprovalRequestStatus::$Expired->getValue(),
                'access_token_verify' => null,
                'access_token_cancel' => null,
            ]);

        QuestionnaireApprovalRequest::query()
            ->whereNull('result_of_checking')
            ->where('updated_at', '<', Carbon::now()->subDays(3)->timestamp)
            ->whereIn('status', [
                ApprovalRequestStatus::$Pending->getValue(),
                ApprovalRequestStatus::$Approved->getValue(),
            ])
            ->update([
                'status' => ApprovalRequestStatus::$Expired->getValue(),
                'access_token_verify' => null,
                'access_token_cancel' => null,
            ]);

        return 0;
    }
}
