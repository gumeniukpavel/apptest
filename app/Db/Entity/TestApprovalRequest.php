<?php

namespace App\Db\Entity;

use Carbon\Carbon;

/**
 * TestApprovalRequest
 *
 * @property int $id
 * @property int $user_id
 * @property int $expert_id
 * @property int $candidate_id
 * @property int $test_result_id
 * @property string $status
 * @property string $comment
 * @property string $result_of_checking
 * @property string $access_token_verify
 * @property string $access_token_cancel
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property Expert $expert
 * @property User $user
 * @property Candidate $candidate
 * @property TestResult $testResult
 */

class TestApprovalRequest extends BaseEntity
{
    protected $fillable = [
        'user_id',
        'expert_id',
        'candidate_id',
        'test_result_id',
        'status',
        'comment',
        'result_of_checking',
        'access_token_verify',
        'access_token_cancel',
        'created_at',
        'updated_at'
    ];

    protected $visible = [
        'id',
        'status',
        'comment',
        'result_of_checking',
        'created_at',
        'updated_at',

        'expert',
        'candidate',
        'testResult',
    ];

    protected $dateFormat = 'U';

    public function expert()
    {
        return $this->belongsTo(Expert::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }

    public function testResult()
    {
        return $this->belongsTo(TestResult::class);
    }
}
