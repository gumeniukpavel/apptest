<?php

namespace App\Db\Entity\PublicEntity;

use App\Db\Entity\Candidate;
use Illuminate\Notifications\Notifiable;

/**
 * PublicCandidate
 */
class PublicCandidate extends Candidate
{
    use Notifiable;

    protected $table = 'candidates';

    protected $visible = [
        'id',
        'name',
        'surname',
        'middle_name'
    ];
}
