<?php
namespace App\Constant;

use App\Constant\Common\Enum;

class TestType extends Enum
{
    public static TestType $Test;
    public static TestType $Questionnaire;
}

TestType::$Test = new TestType('Test', 'Test');
TestType::$Questionnaire = new TestType('Questionnaire', 'Questionnaire');

TestType::init();
