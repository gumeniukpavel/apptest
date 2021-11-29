<?php

namespace App\Db\Service;

use App\Db\Entity\Candidate;
use App\Db\Entity\Event;
use App\Db\Entity\Project;
use App\Db\Entity\ProjectCandidate;
use App\Db\Entity\ProjectTest;
use App\Db\Entity\TestResultAnswer;
use App\Service\AuthService;
use Illuminate\Database\Eloquent\Builder;

class ProjectCandidateDao
{
    private AuthService $authService;
    protected EventDao $eventDao;

    public function __construct(
        AuthService $authService,
        EventDao $eventDao
    )
    {
        $this->authService = $authService;
        $this->eventDao = $eventDao;
    }

    public function getByCandidate(Candidate $candidate)
    {
        return ProjectCandidate::query()
            ->where('candidate_id', $candidate->id)
            ->get();
    }

    public function getSearchQuery(Project $project): Builder
    {
        $builder = ProjectCandidate::query()
            ->with('testResults')
            ->where('project_id', $project->id);

        return $builder;
    }

    public function setIsFavoriteCandidate(ProjectCandidate $projectCandidate, bool $isFavorite)
    {
        ProjectCandidate::query()
            ->where('project_id', $projectCandidate->project_id)
            ->where('candidate_id', $projectCandidate->candidate_id)
            ->update(['is_favorite' => $isFavorite]);
    }

    public function deleteProjectCandidate(ProjectCandidate $projectCandidate)
    {
        if ($projectCandidate->test)
        {
            foreach ($projectCandidate->testResults as $testResult)
            {
                foreach ($testResult->answers as $testResultAnswer)
                {
                    foreach ($testResult->testApprovalRequests as $testApprovalRequest)
                    {
                        $this->eventDao->markEventAsDeleted($testApprovalRequest->id, Event::EVENT_TYPE_TEST_APPROVAL_REQUESTS);
                    }
                    $this->eventDao->markEventAsDeleted($testResultAnswer->id, Event::EVENT_TYPE_TEST_RESULT_ANSWER);
                    $testResultAnswer->delete();
                }
                $this->eventDao->markEventAsDeleted($testResult->id, Event::EVENT_TYPE_TEST_RESULT);
                $testResult->testApprovalRequests()->delete();
                $testResult->delete();
            }
        }
        if ($projectCandidate->questionnaire)
        {
            foreach ($projectCandidate->questionnaireResults as $questionnaireResult)
            {
                foreach ($questionnaireResult->answers as $questionnaireResultAnswer)
                {
                    foreach ($questionnaireResult->questionnaireApprovalRequests as $questionnaireApprovalRequest)
                    {
                        $this->eventDao->markEventAsDeleted($questionnaireApprovalRequest->id, Event::EVENT_TYPE_QUESTIONNAIRE_APPROVAL_REQUESTS);
                    }
                    $this->eventDao->markEventAsDeleted($questionnaireResultAnswer->id, Event::EVENT_TYPE_QUESTIONNAIRE_RESULT_ANSWER);
                    $questionnaireResultAnswer->delete();
                }
                $this->eventDao->markEventAsDeleted($questionnaireResult->id, Event::EVENT_TYPE_QUESTIONNAIRE_RESULT);
                $questionnaireResult->questionnaireApprovalRequests()->delete();
                $questionnaireResult->delete();
            }
        }
        $this->eventDao->markEventAsDeleted($projectCandidate->id, Event::EVENT_TYPE_PROJECT_CANDIDATE);
        $projectCandidate->delete();
    }
}
