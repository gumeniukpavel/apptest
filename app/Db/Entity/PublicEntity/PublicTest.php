<?php

namespace App\Db\Entity\PublicEntity;

use App\Db\Entity\Category;
use App\Db\Entity\Test;
use Illuminate\Notifications\Notifiable;

/**
 * PublicTest
 *
 * @property int $testQuestionsCount
 */
class PublicTest extends Test
{
    use Notifiable;

    protected $table = 'tests';

    protected $appends = [
        'questionsCount'
    ];

    protected $visible = [
        'id',
        'name',
        'description',
        'time_limit',
        'level_id',

        'category',
        'test_questions_count'
    ];

    public function testQuestions()
    {
        return $this->hasMany(PublicQuestion::class, 'test_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function getQuestionsCountAttribute(): int
    {
        return $this->questions->count();
    }
}
