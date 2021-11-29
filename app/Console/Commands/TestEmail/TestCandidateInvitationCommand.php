<?php

namespace App\Console\Commands\TestEmail;

use App\Constant\AccountType;
use App\Db\Entity\Candidate;
use App\Db\Entity\LetterTemplate;
use App\Db\Entity\LetterTemplateType;
use App\Db\Entity\Project;
use App\Db\Entity\ProjectCandidate;
use App\Db\Entity\ProjectTest;
use App\Db\Entity\Question;
use App\Db\Entity\Role;
use App\Db\Entity\Test;
use App\Db\Entity\User;
use App\Db\Entity\UserProfile;
use App\Db\Service\CandidateDao;
use App\Db\Service\ProjectDao;
use App\Db\Service\TestDao;
use App\Db\Service\TestResultDao;
use App\Notifications\Candidate\TestInvitationNotification;
use Illuminate\Console\Command;

class TestCandidateInvitationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test-email:candidate-invitation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send test candidate invitation';

    private TestResultDao $testResultDao;
    private ProjectDao $projectDao;
    private CandidateDao $candidateDao;
    private TestDao $testDao;
    public function __construct(
        TestResultDao $testResultDao,
        ProjectDao $projectDao,
        CandidateDao $candidateDao,
        TestDao $testDao
    )
    {
        parent::__construct();
        $this->testResultDao = $testResultDao;
        $this->projectDao = $projectDao;
        $this->candidateDao = $candidateDao;
        $this->testDao = $testDao;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $email = $this->ask('Enter candidate email');

        /** @var Role $role */
        $role = Role::query()->where('name', Role::ROLE_NAME_CUSTOMER)->first();
        /** @var User $user */
        $user = User::factory()->create([
            'role_id' => $role->id,
            'account_type' => AccountType::$Individual
        ]);
        $user->generateToken();

        /** @var UserProfile $userProfile */
        $userProfile = UserProfile::factory()->create([
            'user_id' => $user->id
        ]);

        /** @var Test $test */
        $test = Test::factory()->create();

        /** @var Question $question */
        $question = Question::factory()->create();
        $test->questions()->save($question);

        /** @var Project $project */
        $project = Project::factory()->create();
        $project->tests()->save($test);

        /** @var ProjectTest $projectTest */
        $projectTest = $project->projectTests()->first();

        /** @var Candidate $candidate */
        $candidate = Candidate::factory()->create([
            'email' => $email
        ]);

        $projectCandidate = new ProjectCandidate();
        $projectCandidate->project_test_id = $projectTest->id;
        $projectCandidate->project_id = $project->id;
        $projectCandidate->candidate_id = $candidate->id;
        $projectCandidate->save();

        $preparedTestResult = $this->testResultDao->buildTestResultsPackForUser(
            $projectCandidate,
            $test
        );

        /** @var LetterTemplate $template */
        $template = LetterTemplate::query()
            ->where([
                'user_id' => $user->id,
                'type_id' => LetterTemplateType::TestInvitation,
                'is_active' => true
            ])->first();


        $projectCandidate->candidate->notify(
            new TestInvitationNotification($preparedTestResult, $userProfile, $template)
        );

        $this->info('Invitation is sent...');

        $this->candidateDao->deleteCandidate($candidate);
        $this->projectDao->deleteProjectTest($projectTest);
        $this->testDao->deleteTest($test);

        $this->info('Temp data cleared...');

        return 0;
    }
}
