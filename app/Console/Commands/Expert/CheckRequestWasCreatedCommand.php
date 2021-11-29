<?php

namespace App\Console\Commands\Expert;

use App\Constant\ApprovalRequestStatus;
use App\Db\Entity\QuestionnaireApprovalRequest;
use App\Db\Entity\TestApprovalRequest;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckRequestWasCreatedCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'approval-request:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete test';

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
        /** @var TestApprovalRequest[] $testApprovalRequests */
        $testApprovalRequests = TestApprovalRequest::query()
            ->where('created_at', '<', Carbon::now()->subHours(48)->timestamp)
            ->where('status', ApprovalRequestStatus::$Pending->getValue())
            ->get();

        foreach ($testApprovalRequests as $testApprovalRequest)
        {
            $testApprovalRequest->status = ApprovalRequestStatus::$Canceled->getValue();
            $testApprovalRequest->access_token_verify = null;
            $testApprovalRequest->access_token_cancel = null;
            $testApprovalRequest->save();
        }

        /** @var QuestionnaireApprovalRequest[] $questionnaireApprovalRequests */
        $questionnaireApprovalRequests = QuestionnaireApprovalRequest::query()
            ->where('created_at', '<', Carbon::now()->subHours(48)->timestamp)
            ->where('status', ApprovalRequestStatus::$Pending->getValue())
            ->get();

        foreach ($questionnaireApprovalRequests as $questionnaireApprovalRequest)
        {
            $questionnaireApprovalRequest->status = ApprovalRequestStatus::$Canceled->getValue();
            $questionnaireApprovalRequest->access_token_verify = null;
            $questionnaireApprovalRequest->access_token_cancel = null;
            $questionnaireApprovalRequest->save();
        }

        return 0;
    }
}
