<?php
namespace App\Constant;

use App\Constant\Common\Enum;

class CandidateType extends Enum
{
    public static CandidateType $Candidate;
    public static CandidateType $Employee;
}

CandidateType::$Candidate = new CandidateType('Candidate', 'Candidate');
CandidateType::$Employee = new CandidateType('Employee', 'Employee');

CandidateType::init();
