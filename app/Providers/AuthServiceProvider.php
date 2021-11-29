<?php

namespace App\Providers;

use App\Db\Entity\Candidate;
use App\Db\Entity\CandidateFile;
use App\Db\Entity\CompanyPosition;
use App\Db\Entity\EmployeeRegistries;
use App\Db\Entity\EmployeeRegistriesFile;
use App\Db\Entity\Event;
use App\Db\Entity\Expert;
use App\Db\Entity\ExpertFile;
use App\Db\Entity\ExpertInterviewEvent;
use App\Db\Entity\LetterTemplate;
use App\Db\Entity\MediaFile;
use App\Db\Entity\News;
use App\Db\Entity\Payment;
use App\Db\Entity\Project;
use App\Db\Entity\Question;
use App\Db\Entity\SystemSetting;
use App\Db\Entity\Test;
use App\Db\Entity\Category;
use App\Db\Entity\QuestionAnswer;
use App\Db\Entity\User;
use App\Db\Entity\Level;
use App\Db\Entity\UserEvent;
use App\Policies\CandidateFilePolicy;
use App\Policies\CandidatePolicy;
use App\Policies\CompanyPosition\CompanyPositionPolicy;
use App\Policies\EmployeeRegistries\EmployeeRegistriesFilePolicy;
use App\Policies\EmployeeRegistries\EmployeeRegistriesPolicy;
use App\Policies\Event\EventPolicy;
use App\Policies\ExpertImagePolicy;
use App\Policies\ExpertInterviewEvent\ExpertInterviewEventPolicy;
use App\Policies\ExpertPolicy;
use App\Policies\LetterTemplate\LetterTemplatePolicy;
use App\Policies\MediaFilePolicy;
use App\Policies\Payment\PaymentPolicy;
use App\Policies\Project\ProjectPolicy;
use App\Policies\Question\QuestionPolicy;
use App\Policies\Test\TestPolicy;
use App\Policies\SystemSetting\SystemSettingPolicy;
use App\Policies\News\NewsPolicy;
use App\Policies\Question\CategoryPolicy;
use App\Policies\Question\QuestionAnswerPolicy;
use App\Policies\User\UserPolicy;
use App\Policies\LevelPolicy;
use App\Policies\UserEvent\UserEventPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Test::class => TestPolicy::class,
        LetterTemplate::class => LetterTemplatePolicy::class,
        Project::class => ProjectPolicy::class,
        Payment::class => PaymentPolicy::class,
        Candidate::class => CandidatePolicy::class,
        Expert::class => ExpertPolicy::class,
        Question::class => QuestionPolicy::class,
        MediaFile::class => MediaFilePolicy::class,
        CandidateFile::class => CandidateFilePolicy::class,
        ExpertFile::class => ExpertImagePolicy::class,
        SystemSetting::class => SystemSettingPolicy::class,
        News::class => NewsPolicy::class,
        Category::class => CategoryPolicy::class,
        QuestionAnswer::class => QuestionAnswerPolicy::class,
        User::class => UserPolicy::class,
        Level::class => LevelPolicy::class,
        Event::class => EventPolicy::class,
        UserEvent::class => UserEventPolicy::class,
        ExpertInterviewEvent::class => ExpertInterviewEventPolicy::class,
        CompanyPosition::class => CompanyPositionPolicy::class,
        EmployeeRegistries::class => EmployeeRegistriesPolicy::class,
        EmployeeRegistriesFile::class => EmployeeRegistriesFilePolicy::class
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //
    }
}
