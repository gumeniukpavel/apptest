<?php

namespace App\Db\Entity;

use App\Constant\TestType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Test
 *
 * @property int $id
 * @property int $level_id
 * @property int $category_id
 * @property int $user_id
 * @property int $time_limit
 * @property string $name
 * @property string $description
 * @property int $pass_point_value
 * @property int $max_allowed_questions
 *
 * @property TestType $type
 * @property int $maximumTestScoreValue
 *
 * @property Level $level
 * @property Tag[] $tags
 * @property Category $category
 * @property Question[] | Collection $questions
 * @property TestResult[] | Collection $testCandidates
 * @property Project[] | Collection $projects
 * @property User | Null $user
 * @property ProjectTest[] | Collection $projectTests
 */
class Test extends ExtendedBaseEntity
{
    use HasFactory;

    protected $fillable = [
        'level_id',
        'user_id',
        'category_id',
        'name',
        'description',
        'time_limit',
        'max_allowed_questions'
    ];

    protected $appends = [
        'maximumTestScoreValue',
    ];

    protected $hidden = [
        'questions'
    ];

    protected $visible = [
        'id',
        'name',
        'description',
        'pass_point_value',
        'user_id',
        'category_id',
        'level_id',
        'time_limit',
        'max_allowed_questions',

        'isActiveForTariff',
        'questions_count',

        'category',
        'level',
        'questions',
        'testCandidates',
        'projects',
        'user',
        'tags',

        'maximumTestScoreValue'
    ];

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function questions()
    {
        return $this->hasMany(Question::class, 'test_id');
    }

    public function testResults()
    {
        return $this->hasMany(TestResult::class, 'test_id');
    }

    public function testCandidates()
    {
        return $this->hasMany(TestResult::class)->with('user');
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function projectTests()
    {
        return $this->hasMany(ProjectTest::class);
    }

    public function getMaximumTestScoreValueAttribute()
    {
        return $this->questions()->limit(null)->sum('score');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'test_tags');
    }

    public function getTypeAttribute()
    {
        return TestType::getEnumObject($this->attributes['type']);
    }

    public function setTypeAttribute(TestType $type)
    {
        $this->attributes['type'] = $type->getValue();
    }

    public function isTest(): bool
    {
        return $this->type === TestType::$Test;
    }

    public function isQuestionnaire(): bool
    {
        return $this->type === TestType::$Questionnaire;
    }
}
