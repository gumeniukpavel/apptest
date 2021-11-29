<?php

namespace App\Db\Entity\PublicEntity;

use App\Db\Entity\ProjectCandidate;
use App\Db\Entity\TestResult;

/**
 * PublicTestResult
 *
 * @property PublicCandidate $candidate
 * @property PublicTest $test
 */
class PublicTestResult extends TestResult
{
    protected $table = 'test_results';

    protected $visible = [
        'estimation_time',
        'started_at',
        'finished_at',

        'finishTime',
        'testTime',
        'timeIsOver',

        'candidate',
        'test',
        'answered_answers_count'
    ];

    public function candidate()
    {
        return $this->hasOneThrough(
            PublicCandidate::class,
            ProjectCandidate::class,
            'id',
            'id',
            'project_candidate_id',
            'candidate_id'
        );
    }

    public function test()
    {
        return $this->belongsTo(PublicTest::class)->with('category');
    }
}
