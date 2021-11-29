<?php

namespace App\Db\Entity;

use App\Constant\CandidateType;
use App\Constant\TestType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Event
 *
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $event_type
 * @property string $description
 * @property string $sub_type
 * @property string $param_1
 * @property string $param_2
 * @property string $param_3
 * @property boolean $object_is_deleted
 * @property boolean $is_read
 * @property boolean $is_popup_notification
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property string|null $userFullName
 *
 * @property User $user
 */
class Event extends BaseEntity
{
    use HasFactory;

    const EVENT_TYPE_TEST = 'TEST';
    const EVENT_TYPE_QUESTIONNAIRE = 'QUESTIONNAIRE';
    const EVENT_TYPE_TEST_RESULT = 'TEST_RESULT';
    const EVENT_TYPE_QUESTIONNAIRE_RESULT = 'TEST_RESULT';
    const EVENT_TYPE_TEST_RESULT_ANSWER = 'TEST_RESULT_ANSWER';
    const EVENT_TYPE_QUESTIONNAIRE_RESULT_ANSWER = 'QUESTIONNAIRE_RESULT_ANSWER';
    const EVENT_TYPE_QUESTION = 'QUESTION';
    const EVENT_TYPE_PROJECT = 'PROJECT';
    const EVENT_TYPE_PROJECT_TEST = 'PROJECT_TEST';
    const EVENT_TYPE_PROJECT_QUESTIONNAIRE = 'PROJECT_QUESTIONNAIRE';
    const EVENT_TYPE_PROJECT_CANDIDATE = 'PROJECT_CANDIDATE';
    const EVENT_TYPE_CANDIDATE = 'CANDIDATE';
    const EVENT_TYPE_EXPERT = 'EXPERT';
    const EVENT_TYPE_EMPLOYEE = 'EMPLOYEE';
    const EVENT_TYPE_TARIFF = 'TARIFF';
    const EVENT_TYPE_PAYMENT = 'PAYMENT';
    const EVENT_TYPE_INTERVIEW_INVITATION = 'INTERVIEW_INVITATION';
    const EVENT_TYPE_EXPERT_INTERVIEW_INVITATION = 'EXPERT_INTERVIEW_INVITATION';
    const EVENT_TYPE_ACCEPTED_INVITATION = 'ACCEPTED_INVITATION';
    const EVENT_TYPE_CANCEL_INVITATION = 'CANCEL_INVITATION';
    const EVENT_TYPE_EXPERT_ACCEPTED_INVITATION = 'EXPERT_ACCEPTED_INVITATION';
    const EVENT_TYPE_EXPERT_CANCEL_INVITATION = 'EXPERT_CANCEL_INVITATION';
    const EVENT_TYPE_SEND_TEST_INVITATION = 'SEND_TEST_INVITATION';
    const EVENT_TYPE_SEND_TEST_END_NOTIFICATION = 'SEND_TEST_END_NOTIFICATION';
    const EVENT_TYPE_SEND_QUESTIONNAIRE_INVITATION = 'SEND_QUESTIONNAIRE_INVITATION';
    const EVENT_TYPE_SEND_QUESTIONNAIRE_END_NOTIFICATION = 'SEND_QUESTIONNAIRE_END_NOTIFICATION';
    const EVENT_TYPE_SET_IS_FAVORITE = 'SET_IS_FAVORITE';
    const EVENT_TYPE_CHANGE_CANDIDATE_TYPE = 'CHANGE_CANDIDATE_TYPE';
    const EVENT_TYPE_TESTING = 'TESTING';
    const EVENT_TYPE_TEST_APPROVAL_REQUESTS = 'TEST_APPROVAL_REQUESTS';
    const EVENT_TYPE_QUESTIONNAIRE_APPROVAL_REQUESTS = 'QUESTIONNAIRE_APPROVAL_REQUESTS';
    const EVENT_TYPE_AGREED_CHECK_TEST_RESULT = 'AGREED_CHECK_TEST_RESULT';
    const EVENT_TYPE_AGREED_CHECK_QUESTIONNAIRE_RESULT = 'AGREED_CHECK_QUESTIONNAIRE_RESULT';
    const EVENT_TYPE_CANCEL_CHECK_TEST_RESULT = 'CANCEL_CHECK_TEST_RESULT';
    const EVENT_TYPE_CANCEL_CHECK_QUESTIONNAIRE_RESULT = 'CANCEL_CHECK_QUESTIONNAIRE_RESULT';
    const EVENT_TYPE_APPROVED_TEST_RESULT = 'APPROVED_TEST_RESULT';
    const EVENT_TYPE_NON_APPROVED_TEST_RESULT = 'NON_APPROVED_TEST_RESULT';
    const EVENT_TYPE_APPROVED_QUESTIONNAIRE_RESULT = 'APPROVED_QUESTIONNAIRE_RESULT';
    const EVENT_TYPE_NON_APPROVED_QUESTIONNAIRE_RESULT = 'NON_APPROVED_QUESTIONNAIRE_RESULT';

    const EVENT_TYPES = [
        self::EVENT_TYPE_TEST,
        self::EVENT_TYPE_QUESTIONNAIRE,
        self::EVENT_TYPE_QUESTIONNAIRE_RESULT,
        self::EVENT_TYPE_QUESTIONNAIRE_RESULT_ANSWER,
        self::EVENT_TYPE_TEST_RESULT,
        self::EVENT_TYPE_QUESTION,
        self::EVENT_TYPE_PROJECT,
        self::EVENT_TYPE_PROJECT_TEST,
        self::EVENT_TYPE_PROJECT_QUESTIONNAIRE,
        self::EVENT_TYPE_PROJECT_CANDIDATE,
        self::EVENT_TYPE_CANDIDATE,
        self::EVENT_TYPE_EXPERT,
        self::EVENT_TYPE_EMPLOYEE,
        self::EVENT_TYPE_TEST_RESULT_ANSWER,
        self::EVENT_TYPE_TARIFF,
        self::EVENT_TYPE_PAYMENT,
        self::EVENT_TYPE_INTERVIEW_INVITATION,
        self::EVENT_TYPE_EXPERT_INTERVIEW_INVITATION,
        self::EVENT_TYPE_ACCEPTED_INVITATION,
        self::EVENT_TYPE_CANCEL_INVITATION,
        self::EVENT_TYPE_EXPERT_ACCEPTED_INVITATION,
        self::EVENT_TYPE_EXPERT_CANCEL_INVITATION,
        self::EVENT_TYPE_SEND_TEST_INVITATION,
        self::EVENT_TYPE_SEND_TEST_END_NOTIFICATION,
        self::EVENT_TYPE_SEND_QUESTIONNAIRE_INVITATION,
        self::EVENT_TYPE_SEND_QUESTIONNAIRE_END_NOTIFICATION,
        self::EVENT_TYPE_SET_IS_FAVORITE,
        self::EVENT_TYPE_CHANGE_CANDIDATE_TYPE,
        self::EVENT_TYPE_TESTING,
        self::EVENT_TYPE_TEST_APPROVAL_REQUESTS,
        self::EVENT_TYPE_QUESTIONNAIRE_APPROVAL_REQUESTS,
        self::EVENT_TYPE_AGREED_CHECK_TEST_RESULT,
        self::EVENT_TYPE_AGREED_CHECK_QUESTIONNAIRE_RESULT,
        self::EVENT_TYPE_CANCEL_CHECK_TEST_RESULT,
        self::EVENT_TYPE_CANCEL_CHECK_QUESTIONNAIRE_RESULT,
        self::EVENT_TYPE_APPROVED_TEST_RESULT,
        self::EVENT_TYPE_NON_APPROVED_TEST_RESULT,
        self::EVENT_TYPE_APPROVED_QUESTIONNAIRE_RESULT,
        self::EVENT_TYPE_NON_APPROVED_QUESTIONNAIRE_RESULT,
    ];

    const EVENT_SUB_TYPE_UPDATE = 'UPDATE';
    const EVENT_SUB_TYPE_CREATE = 'CREATE';
    const EVENT_SUB_TYPE_APPEND = 'APPEND';
    const EVENT_SUB_TYPE_DELETE = 'DELETE';
    const EVENT_SUB_TYPE_SET_IS_FAVORITE = 'SET_IS_FAVORITE';
    const EVENT_SUB_TYPE_SET_IS_SHOW_IN_REVIEW = 'SET_IS_SHOW_IN_REVIEW';
    const EVENT_SUB_TYPE_CLOSE_PROJECT = 'CLOSE_PROJECT';
    const EVENT_SUB_TYPE_OPEN_PROJECT = 'OPEN_PROJECT';
    const EVENT_SUB_TYPE_UPLOAD_AUDIO = 'UPLOAD_AUDIO';
    const EVENT_SUB_TYPE_UPLOAD_VIDEO = 'UPLOAD_VIDEO';
    const EVENT_SUB_TYPE_UPLOAD_IMAGE = 'UPLOAD_IMAGE';
    const EVENT_SUB_TYPE_START_TEST = 'START_TEST';
    const EVENT_SUB_TYPE_END_TEST = 'END_TEST';
    const EVENT_SUB_TYPE_START_QUESTIONNAIRE = 'START_QUESTIONNAIRE';
    const EVENT_SUB_TYPE_END_QUESTIONNAIRE = 'END_QUESTIONNAIRE';
    const EVENT_SUB_TYPE_SAVE_ANSWER = 'SAVE_ANSWER';
    const EVENT_SUB_TYPE_SENT = 'SENT';
    const EVENT_SUB_TYPE_CANDIDATE = 'CANDIDATE';
    const EVENT_SUB_TYPE_EXPERT = 'EXPERT';
    const EVENT_SUB_TYPE_INVITATION = 'INVITATION';
    const EVENT_SUB_TYPE_RESULT = 'RESULT';
    const EVENT_SUB_TYPE_ANSWERS = 'ANSWERS';

    const EVENT_SUB_TYPES = [
        self::EVENT_SUB_TYPE_UPDATE,
        self::EVENT_SUB_TYPE_CREATE,
        self::EVENT_SUB_TYPE_APPEND,
        self::EVENT_SUB_TYPE_DELETE,
        self::EVENT_SUB_TYPE_SET_IS_FAVORITE,
        self::EVENT_SUB_TYPE_SET_IS_SHOW_IN_REVIEW,
        self::EVENT_SUB_TYPE_CLOSE_PROJECT,
        self::EVENT_SUB_TYPE_OPEN_PROJECT,
        self::EVENT_SUB_TYPE_UPLOAD_AUDIO,
        self::EVENT_SUB_TYPE_UPLOAD_VIDEO,
        self::EVENT_SUB_TYPE_UPLOAD_IMAGE,
        self::EVENT_SUB_TYPE_START_TEST,
        self::EVENT_SUB_TYPE_END_TEST,
        self::EVENT_SUB_TYPE_START_QUESTIONNAIRE,
        self::EVENT_SUB_TYPE_END_QUESTIONNAIRE,
        self::EVENT_SUB_TYPE_SAVE_ANSWER,
        self::EVENT_SUB_TYPE_SENT,
        self::EVENT_SUB_TYPE_CANDIDATE,
        self::EVENT_SUB_TYPE_EXPERT,
        self::EVENT_SUB_TYPE_INVITATION,
        self::EVENT_SUB_TYPE_RESULT,
        self::EVENT_SUB_TYPE_ANSWERS
    ];

    protected $fillable = [
        'user_id',
        'event_type',
        'sub_type',
        'param_1',
        'param_2',
        'param_3',
        'object_is_deleted'
    ];

    protected $visible = [
        'id',
        'event_type',
        'sub_type',
        'description',
        'param_1',
        'param_2',
        'param_3',
        'object_is_deleted',
        'is_read',
        'is_popup_notification',
        'created_at',

        'user',

        'userFullName',
        'eventSubTypeLabel'
    ];

    protected $appends = [
        'userFullName',
        'eventSubTypeLabel'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function save(array $options = [])
    {
        $this->description = $this->buildDescription();
        return parent::save($options);
    }

    public function getEventSubTypeLabelAttribute()
    {
        switch ($this->sub_type)
        {
            case Event::EVENT_SUB_TYPE_CANDIDATE:
                return trans('events.subType.candidate');

            case Event::EVENT_SUB_TYPE_EXPERT:
                return trans('events.subType.expert');

            case Event::EVENT_SUB_TYPE_UPDATE:
                return trans('events.subType.update');

            case Event::EVENT_SUB_TYPE_CREATE:
                return trans('events.subType.create');

            case Event::EVENT_SUB_TYPE_APPEND:
                return trans('events.subType.add');

            case Event::EVENT_SUB_TYPE_DELETE:
                return trans('events.subType.delete');

            case Event::EVENT_SUB_TYPE_SET_IS_FAVORITE:
                return trans('events.subType.favorite');

            case Event::EVENT_SUB_TYPE_SET_IS_SHOW_IN_REVIEW:
                return trans('events.subType.shownInReview');

            case Event::EVENT_SUB_TYPE_CLOSE_PROJECT:
                return trans('events.subType.closing');

            case Event::EVENT_SUB_TYPE_OPEN_PROJECT:
                return trans('events.subType.opening');

            case Event::EVENT_SUB_TYPE_UPLOAD_AUDIO:
                return trans('events.subType.uploadAudio');

            case Event::EVENT_SUB_TYPE_UPLOAD_VIDEO:
                return trans('events.subType.uploadVideo');

            case Event::EVENT_SUB_TYPE_UPLOAD_IMAGE:
                return trans('events.subType.uploadImage');

            case Event::EVENT_SUB_TYPE_START_TEST:
                return trans('events.subType.start');

            case Event::EVENT_SUB_TYPE_END_TEST:
                return trans('events.subType.end');

            case Event::EVENT_SUB_TYPE_START_QUESTIONNAIRE:
                return trans('events.subType.questionnaireStart');

            case Event::EVENT_SUB_TYPE_END_QUESTIONNAIRE:
                return trans('events.subType.questionnaireEnd');

            case Event::EVENT_SUB_TYPE_SAVE_ANSWER:
                return trans('events.subType.save');

            case Event::EVENT_SUB_TYPE_SENT:
                return trans('events.subType.send');

            case Event::EVENT_SUB_TYPE_INVITATION:
                return trans('events.subType.invitation');

            case Event::EVENT_SUB_TYPE_RESULT:
                return trans('events.subType.result');

            case Event::EVENT_SUB_TYPE_ANSWERS:
                return trans('events.subType.answers');
        }
    }

    public function buildDescription(): string
    {
        $description = '';
        switch ($this->sub_type)
        {
            case Event::EVENT_SUB_TYPE_CANDIDATE:
                $description .= trans('events.subType.candidate');
                break;

            case Event::EVENT_SUB_TYPE_EXPERT:
                $description .= trans('events.subType.expert');
                break;

            case Event::EVENT_SUB_TYPE_UPDATE:
                $description .= trans('events.subType.update');
                break;

            case Event::EVENT_SUB_TYPE_CREATE:
                $description .= trans('events.subType.create');
                break;

            case Event::EVENT_SUB_TYPE_APPEND:
                $description .= trans('events.subType.add');
                break;

            case Event::EVENT_SUB_TYPE_DELETE:
                $description .= trans('events.subType.delete');
                break;

            case Event::EVENT_SUB_TYPE_SET_IS_FAVORITE:
                $description .= trans('events.subType.favorite');
                break;

            case Event::EVENT_SUB_TYPE_SET_IS_SHOW_IN_REVIEW:
                $description .= trans('events.subType.shownInReview');
                break;

            case Event::EVENT_SUB_TYPE_CLOSE_PROJECT:
                $description .= trans('events.subType.closing');
                break;

            case Event::EVENT_SUB_TYPE_OPEN_PROJECT:
                $description .= trans('events.subType.opening');
                break;

            case Event::EVENT_SUB_TYPE_UPLOAD_AUDIO:
                $description .= trans('events.subType.uploadAudio');
                break;

            case Event::EVENT_SUB_TYPE_UPLOAD_VIDEO:
                $description .= trans('events.subType.uploadVideo');
                break;

            case Event::EVENT_SUB_TYPE_UPLOAD_IMAGE:
                $description .= trans('events.subType.uploadImage');
                break;

            case Event::EVENT_SUB_TYPE_START_TEST:
                $description .= trans('events.subType.start');
                break;

            case Event::EVENT_SUB_TYPE_END_TEST:
                $description .= trans('events.subType.end');
                break;

            case Event::EVENT_SUB_TYPE_START_QUESTIONNAIRE:
                $description .= trans('events.subType.questionnaireStart');
                break;

            case Event::EVENT_SUB_TYPE_END_QUESTIONNAIRE:
                $description .= trans('events.subType.questionnaireEnd');
                break;

            case Event::EVENT_SUB_TYPE_SAVE_ANSWER:
                $description .= trans('events.subType.save');
                break;

            case Event::EVENT_SUB_TYPE_SENT:
                $description .= trans('events.subType.send');
                break;

            case Event::EVENT_SUB_TYPE_INVITATION:
                $description .= trans('events.subType.invitation');
                break;

            case Event::EVENT_SUB_TYPE_RESULT:
                $description .= trans('events.subType.result');
                break;

            case Event::EVENT_SUB_TYPE_ANSWERS:
                $description .= trans('events.subType.answers');
                break;
        }
        switch ($this->event_type)
        {
            case Event::EVENT_TYPE_TEST:
                $description .= trans('events.type.test');

                if ($this->param_1)
                {
                    /** @var Test $test */
                    $test = Test::byId($this->param_1);

                    if ($test)
                    {
                        $description .= ' "' . $test->name . '".';
                    }
                }
                break;

            case Event::EVENT_TYPE_QUESTIONNAIRE:
                $description .= trans('events.type.questionnaire');

                if ($this->param_1)
                {
                    /** @var Test $test */
                    $test = Test::byId($this->param_1);

                    if ($test)
                    {
                        $description .= ' "' . $test->name . '".';
                    }
                }
                break;

            case Event::EVENT_TYPE_TEST_RESULT_ANSWER:
            case Event::EVENT_TYPE_TEST_RESULT:
                $description .= trans('events.type.testResult');
                break;

            case Event::EVENT_TYPE_QUESTIONNAIRE_RESULT_ANSWER:
            case Event::EVENT_TYPE_QUESTIONNAIRE_RESULT:
                $description .= trans('events.type.questionnaireResult');
                break;

            case Event::EVENT_TYPE_QUESTION:
                $description .= trans('events.type.question');

                if ($this->param_1)
                {
                    /** @var Question $question */
                    $question = Question::byId($this->param_1);
                    if ($question)
                    {
                        $description .= ' "' . $question->description . '"';
                    }
                }

                if ($this->param_2)
                {
                    /** @var Test $test */
                    $test = Test::byId($this->param_2);
                    if ($test->isTest())
                    {
                        $description .= trans('events.type.inTest') . '"' . $test->name . '".';
                    }

                    else
                    {
                        $description .= trans('events.type.inQuestionnaire') . '"' . $test->name . '".';
                    }
                }
                break;

            case Event::EVENT_TYPE_PROJECT:
                $description .= trans('events.type.project');

                if ($this->param_1)
                {
                    /** @var Project $project */
                    $project = Project::byId($this->param_1);

                    if ($project)
                    {
                        $description .= ' "' . $project->name . '".';
                    }
                }
                break;

            case Event::EVENT_TYPE_PROJECT_TEST:
                $description .= trans('events.type.test');

                if ($this->param_1)
                {
                    /** @var Test $test */
                    $test = Test::byId($this->param_1);

                    if ($test)
                    {
                        $description .= ' "' . $test->name . '".';
                    }
                }

                if ($this->param_2)
                {
                    /** @var Project $project */
                    $project = Project::byId($this->param_2);

                    if ($project)
                    {
                        $description .= trans('events.type.inToProject') . '"' . $project->name . '".';
                    }
                }
                break;

            case Event::EVENT_TYPE_PROJECT_QUESTIONNAIRE:
                $description .= trans('events.type.projectQuestionnaire');
                break;

            case Event::EVENT_TYPE_PROJECT_CANDIDATE:
                $description .= trans('events.type.candidate');

                if ($this->param_1)
                {
                    /** @var ProjectCandidate $projectCandidate */
                    $projectCandidate = ProjectCandidate::byId($this->param_1);

                    if ($projectCandidate)
                    {
                        $description .= ' ' . $projectCandidate->candidate->surname .' '. $projectCandidate->candidate->name . '';
                    }
                }

                if ($this->param_2)
                {
                    /** @var Project $project */
                    $project = Project::byId($this->param_2);

                    if ($project)
                    {
                        $description .= trans('events.type.inProject') . '"' . $project->name . '".';
                    }
                }
                break;

            case Event::EVENT_TYPE_SET_IS_FAVORITE:
                $description .= trans('events.type.candidate');

                if ($this->param_1)
                {
                    /** @var ProjectCandidate $projectCandidate */
                    $projectCandidate = ProjectCandidate::byId($this->param_1);

                    if ($projectCandidate)
                    {
                        $description .= ' '. $projectCandidate->candidate->surname. ' ' . $projectCandidate->candidate->name .'';
                    }
                }

                if ($this->param_2)
                {
                    /** @var Project $project */
                    $project = Project::byId($this->param_2);

                    if ($project)
                    {
                        $description .= trans('events.type.toSelectedProject') . '"' . $project->name . '".';
                    }
                }
                break;

            case Event::EVENT_TYPE_CANDIDATE:
                $description .= trans('events.type.candidate');

                if ($this->param_1)
                {
                    /** @var Candidate $candidate */
                    $candidate = Candidate::byId($this->param_1);

                    if ($candidate)
                    {
                        $description .= ' ' . $candidate->surname . ' ' . $candidate->name . '.';
                    }
                }
                break;

            case Event::EVENT_TYPE_EXPERT:
                $description .= trans('events.type.expert');

                if ($this->param_1)
                {
                    /** @var Expert $expert */
                    $expert = Expert::byId($this->param_1);

                    if ($expert)
                    {
                        $description .= ' ' . $expert->surname . ' ' . $expert->name . '.';
                    }
                }
                break;

            case Event::EVENT_TYPE_EMPLOYEE:
                $description .= trans('events.type.employee');

                if ($this->param_1)
                {
                    /** @var Candidate $candidate */
                    $candidate = Candidate::byId($this->param_1);

                    if ($candidate)
                    {
                        $description .= ' '. $candidate->surname . ' ' . $candidate->name .'.';
                    }
                }
                break;

            case Event::EVENT_TYPE_TESTING:

                if ($this->param_1)
                {
                    /** @var Test $test */
                    $test = Test::byId($this->param_1);

                    if ($test)
                    {
                        $description .= ' "' . $test->name . '"';
                        $description .= trans('events.type.candidateTesting');
                    }
                }

                if ($this->param_2)
                {
                    /** @var ProjectCandidate $projectCandidate */
                    $projectCandidate = ProjectCandidate::byId($this->param_2);

                    if ($projectCandidate)
                    {
                        $description .= ' ' . $projectCandidate->candidate->surname . ' ' . $projectCandidate->candidate->name . '.';

                    }
                }
                break;

            case Event::EVENT_TYPE_CHANGE_CANDIDATE_TYPE:

                if ($this->param_1)
                {
                    /** @var Candidate $candidate */
                    $candidate = Candidate::byId($this->param_1);

                    if ($candidate->type->getValue() == CandidateType::$Candidate)
                    {
                        $description .= ' ' . $candidate->surname . ' ' . $candidate->name . '';
                        $description .= trans('events.type.transferToCandidate');
                    }

                    if ($candidate->type->getValue() == CandidateType::$Employee)
                    {
                        $description .= ' ' . $candidate->surname . ' ' . $candidate->name . '';
                        $description .= trans('events.type.transferToEmployee');
                    }
                }
                break;

            case Event::EVENT_TYPE_SEND_TEST_END_NOTIFICATION:

                if ($this->param_1)
                {
                    /** @var Test $test */
                    $test = Test::byId($this->param_1);

                    if ($test)
                    {
                        $description .= trans('events.type.passingTest') . '"' . $test->name . '"';
                    }
                }

                if ($this->param_2)
                {
                    /** @var ProjectCandidate $projectCandidate */
                    $projectCandidate = ProjectCandidate::byId($this->param_2);

                    if ($projectCandidate)
                    {
                        $description .= trans('events.type.sendToTheCandidate') . $projectCandidate->candidate->surname
                            .' '. $projectCandidate->candidate->name . '.';
                    }
                }
                break;

            case Event::EVENT_TYPE_SEND_QUESTIONNAIRE_END_NOTIFICATION:

                if ($this->param_1)
                {
                    /** @var Test $test */
                    $test = Test::byId($this->param_1);

                    if ($test)
                    {
                        $description .= trans('events.type.passingQuestionnaire') . '"' . $test->name . '"';
                    }
                }

                if ($this->param_2)
                {
                    /** @var ProjectCandidate $projectCandidate */
                    $projectCandidate = ProjectCandidate::byId($this->param_2);

                    if ($projectCandidate)
                    {
                        $description .= trans('events.type.sendToTheCandidate') . $projectCandidate->candidate->surname
                            .' '. $projectCandidate->candidate->name . '.';
                    }
                }
                break;

            case Event::EVENT_TYPE_SEND_TEST_INVITATION:

                if ($this->param_1)
                {
                    /** @var Test $test */
                    $test = Test::byId($this->param_1);

                    if ($test)
                    {
                        $description .= trans('events.type.testInvitation') . '"' . $test->name . '"';
                    }
                }

                if ($this->param_2)
                {
                    /** @var ProjectCandidate $projectCandidate */
                    $projectCandidate = ProjectCandidate::byId($this->param_2);

                    if ($projectCandidate)
                    {
                        $description .= trans('events.type.sendToCandidate') . $projectCandidate->candidate->surname
                            .' '. $projectCandidate->candidate->name . '.';
                    }
                }
                break;

            case Event::EVENT_TYPE_SEND_QUESTIONNAIRE_INVITATION:

                if ($this->param_1)
                {
                    /** @var Test $test */
                    $test = Test::byId($this->param_1);

                    if ($test)
                    {
                        $description .= trans('events.type.questionnaireInvitation') . '"' . $test->name . '"';
                    }
                }

                if ($this->param_2)
                {
                    /** @var ProjectCandidate $projectCandidate */
                    $projectCandidate = ProjectCandidate::byId($this->param_2);

                    if ($projectCandidate)
                    {
                        $description .= trans('events.type.sendToCandidate') . $projectCandidate->candidate->surname
                            .' '. $projectCandidate->candidate->name . '.';
                    }
                }
                break;

            case Event::EVENT_TYPE_TARIFF:
                $description .= trans('events.type.tariff');
                break;

            case Event::EVENT_TYPE_PAYMENT:
                $description .= trans('events.type.payment');

                if ($this->param_1)
                {
                    /** @var Payment $payment */
                    $payment = Payment::query()
                        ->where('id', $this->param_1)
                        ->first();

                    if ($payment)
                    {
                        $description .= ' "' . $payment->status . '"';
                    }
                }

                if ($this->param_2)
                {
                    /** @var Tariff $tariff */
                    $tariff = Tariff::query()
                        ->where('id', $this->param_2)
                        ->first();

                    if ($tariff)
                    {
                        $description .= trans('events.type.rate') . '"' . $tariff->name . '".';
                    }
                }
                break;

            case Event::EVENT_TYPE_INTERVIEW_INVITATION:
                $description .= trans('events.type.interviewInvitation');

                if ($this->param_1)
                {
                    /** @var Candidate $candidate */
                    $candidate = Candidate::byId($this->param_1);

                    if ($candidate)
                    {
                        $description .= ' ' . $candidate->surname .' '. $candidate->name . '.';
                    }
                }
                break;

            case Event::EVENT_TYPE_ACCEPTED_INVITATION:

                if ($this->param_1)
                {
                    /** @var Candidate $candidate */
                    $candidate = Candidate::byId($this->param_1);

                    if ($candidate)
                    {
                        $description .= ' ' . $candidate->surname .' '. $candidate->name . trans('events.type.acceptedInterviewInvitation');
                    }
                }
                break;

            case Event::EVENT_TYPE_CANCEL_INVITATION:

                if ($this->param_1)
                {
                    /** @var Candidate $candidate */
                    $candidate = Candidate::byId($this->param_1);

                    if ($candidate)
                    {
                        $description .= ' ' . $candidate->surname .' '. $candidate->name . trans('events.type.cancelInterviewInvitation');
                    }
                }
                break;

            case Event::EVENT_TYPE_EXPERT_INTERVIEW_INVITATION:
                $description .= trans('events.type.expertInterviewInvitation');

                if ($this->param_1)
                {
                    /** @var Expert $expert */
                    $expert = Expert::byId($this->param_1);

                    if ($expert)
                    {
                        $description .= ' ' . $expert->surname .' '. $expert->name . '.';
                    }
                }
                break;

            case Event::EVENT_TYPE_EXPERT_ACCEPTED_INVITATION:

                if ($this->param_1)
                {
                    /** @var Expert $expert */
                    $expert = Expert::byId($this->param_1);

                    if ($expert)
                    {
                        $description .= ' ' . $expert->surname .' '. $expert->name . trans('events.type.acceptedInterviewInvitation');
                    }
                }
                break;

            case Event::EVENT_TYPE_EXPERT_CANCEL_INVITATION:

                if ($this->param_1)
                {
                    /** @var Expert $expert */
                    $expert = Expert::byId($this->param_1);

                    if ($expert)
                    {
                        $description .= ' ' . $expert->surname .' '. $expert->name . trans('events.type.cancelInterviewInvitation');
                    }
                }
                break;

            case Event::EVENT_TYPE_TEST_APPROVAL_REQUESTS:

                if ($this->param_1)
                {
                    /** @var TestResult $testResult */
                    $testResult = TestResult::byId($this->param_1);

                    if ($testResult)
                    {
                        $description .= trans('events.type.candidate') . ' ' . $testResult->candidate->surname .' ' .
                            $testResult->candidate->name .'' . trans('events.type.byTest') . '"' . $testResult->test->name .
                            '"' . trans('events.type.sendExpert');
                    }
                }

                if ($this->param_2)
                {
                    /** @var Expert $expert */
                    $expert = Expert::byId($this->param_2);

                    if ($expert)
                    {
                        $description .= ' ' . $expert->surname . ' ' . $expert->name . '.';
                    }
                }
                break;

            case Event::EVENT_TYPE_QUESTIONNAIRE_APPROVAL_REQUESTS:

                if ($this->param_1)
                {
                    /** @var TestResult $testResult */
                    $testResult = TestResult::byId($this->param_1);

                    if ($testResult)
                    {
                        $description .= trans('events.type.candidate') . ' ' . $testResult->candidate->surname .' ' .
                            $testResult->candidate->name .'' . trans('events.type.questionnaireAnswer') . '"' . $testResult->test->name .
                            '"' . trans('events.type.sendExpert');
                    }
                }

                if ($this->param_2)
                {
                    /** @var Expert $expert */
                    $expert = Expert::byId($this->param_2);

                    if ($expert)
                    {
                        $description .= ' ' . $expert->surname . ' ' . $expert->name . '.';
                    }
                }
                break;

            case Event::EVENT_TYPE_AGREED_CHECK_TEST_RESULT:

                if ($this->param_1)
                {
                    /** @var TestApprovalRequest $testApprovalRequest */
                    $testApprovalRequest = TestApprovalRequest::byId($this->param_1);

                    if ($testApprovalRequest)
                    {
                        $description .= ' ' . $testApprovalRequest->expert->surname . ' ' . $testApprovalRequest->expert->name
                            . '' . trans('events.type.agreedToCheckResults') . ' ' . $testApprovalRequest->candidate->surname
                            . ' ' . $testApprovalRequest->candidate->name . '' . trans('events.type.inTest') . '"' . $testApprovalRequest->testResult->test->name . '".';
                    }
                }
                break;

            case Event::EVENT_TYPE_CANCEL_CHECK_TEST_RESULT:

                if ($this->param_1)
                {
                    /** @var TestApprovalRequest $testApprovalRequest */
                    $testApprovalRequest = TestApprovalRequest::byId($this->param_1);

                    if ($testApprovalRequest)
                    {
                        $description .= ' ' . $testApprovalRequest->expert->surname . ' ' . $testApprovalRequest->expert->name
                            . '' . trans('events.type.cancelToCheckResults') . ' "' . $testApprovalRequest->testResult->test->name
                            . '"' . trans('events.type.candidate') . ' ' . $testApprovalRequest->candidate->surname . ' '
                            . $testApprovalRequest->candidate->name . '.';
                    }
                }
                break;

            case Event::EVENT_TYPE_APPROVED_TEST_RESULT:

                if ($this->param_1)
                {
                    /** @var TestApprovalRequest $testApprovalRequest */
                    $testApprovalRequest = TestApprovalRequest::byId($this->param_1);

                    if ($testApprovalRequest)
                    {
                        $description .=' ' . $testApprovalRequest->expert->surname . ' ' . $testApprovalRequest->expert->name
                            . '' . trans('events.type.approveTestResult') . ' "' . $testApprovalRequest->testResult->test->name
                            . '"' . trans('events.type.candidate') . ' ' . $testApprovalRequest->candidate->surname
                            . ' ' . $testApprovalRequest->candidate->name . '.';
                    }
                }
                break;

            case Event::EVENT_TYPE_NON_APPROVED_TEST_RESULT:

                if ($this->param_1)
                {
                    /** @var TestApprovalRequest $testApprovalRequest */
                    $testApprovalRequest = TestApprovalRequest::byId($this->param_1);

                    if ($testApprovalRequest)
                    {
                        $description .=' '.$testApprovalRequest->expert->surname . ' ' . $testApprovalRequest->expert->name
                            . '' . trans('events.type.nonApprovedTestResult') . ' "' . $testApprovalRequest->testResult->test->name
                            . '"' . trans('events.type.candidate') . ' ' . $testApprovalRequest->candidate->surname
                            . ' ' . $testApprovalRequest->candidate->name . '.';
                    }
                }
                break;

            case Event::EVENT_TYPE_AGREED_CHECK_QUESTIONNAIRE_RESULT:

                if ($this->param_1)
                {
                    /** @var QuestionnaireApprovalRequest $questionnaireApprovalRequest */
                    $questionnaireApprovalRequest = QuestionnaireApprovalRequest::byId($this->param_1);

                    if ($questionnaireApprovalRequest)
                    {
                        $description .= ' ' . $questionnaireApprovalRequest->expert->surname . ' ' . $questionnaireApprovalRequest->expert->name
                            . '' . trans('events.type.agreedToCheckQuestionnaire') . ' "' . $questionnaireApprovalRequest->questionnaireResult->test->name
                            . '"' . trans('events.type.candidate') . ' ' . $questionnaireApprovalRequest->candidate->surname
                            . ' ' . $questionnaireApprovalRequest->candidate->name . '.';
                    }
                }
                break;

            case Event::EVENT_TYPE_CANCEL_CHECK_QUESTIONNAIRE_RESULT:

                if ($this->param_1)
                {
                    /** @var QuestionnaireApprovalRequest $questionnaireApprovalRequest */
                    $questionnaireApprovalRequest = QuestionnaireApprovalRequest::byId($this->param_1);

                    if ($questionnaireApprovalRequest)
                    {
                        $description .= ' ' . $questionnaireApprovalRequest->expert->surname . ' ' . $questionnaireApprovalRequest->expert->name
                            . '' . trans('events.type.cancelToCheckQuestionnaire') . ' "' . $questionnaireApprovalRequest->questionnaireResult->test->name
                            . '"' . trans('events.type.candidate') . ' ' . $questionnaireApprovalRequest->candidate->surname
                            . ' ' . $questionnaireApprovalRequest->candidate->name . '.';
                    }
                }
                break;

            case Event::EVENT_TYPE_APPROVED_QUESTIONNAIRE_RESULT:

                if ($this->param_1)
                {
                    /** @var QuestionnaireApprovalRequest $questionnaireApprovalRequest */
                    $questionnaireApprovalRequest = QuestionnaireApprovalRequest::byId($this->param_1);

                    if ($questionnaireApprovalRequest)
                    {
                        $description .= ' ' . $questionnaireApprovalRequest->expert->surname . ' ' . $questionnaireApprovalRequest->expert->name
                            . '' . trans('events.type.approveQuestionnaireResult') . ' "' . $questionnaireApprovalRequest->questionnaireResult->test->name
                            . '"' . trans('events.type.candidate') . ' ' . $questionnaireApprovalRequest->candidate->surname
                            . ' ' . $questionnaireApprovalRequest->candidate->name . '.';
                    }
                }
                break;

            case Event::EVENT_TYPE_NON_APPROVED_QUESTIONNAIRE_RESULT:

                if ($this->param_1)
                {
                    /** @var QuestionnaireApprovalRequest $questionnaireApprovalRequest */
                    $questionnaireApprovalRequest = QuestionnaireApprovalRequest::byId($this->param_1);

                    if ($questionnaireApprovalRequest)
                    {
                        $description .= ' ' . $questionnaireApprovalRequest->expert->surname . ' ' . $questionnaireApprovalRequest->expert->name
                            . '' . trans('events.type.nonApproveQuestionnaireResult') . ' "' . $questionnaireApprovalRequest->questionnaireResult->test->name
                            . '"' . trans('events.type.candidate') . ' ' . $questionnaireApprovalRequest->candidate->surname
                            . ' ' . $questionnaireApprovalRequest->candidate->name . '.';
                    }
                }
                break;
        }

        return $description;
    }

    public function getUserFullNameAttribute()
    {
        if ($this->user)
        {
            return $this->user->surname . ' ' . $this->user->name . ' ' . $this->user->middle_name;
        }
        return null;
    }
}
