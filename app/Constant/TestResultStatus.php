<?php
namespace App\Constant;

use App\Constant\Common\Enum;

class TestResultStatus extends Enum
{
    public static TestResultStatus $StatusIgnored;
    public static TestResultStatus $StatusDidNotPassOnPoints;
    public static TestResultStatus $StatusDidNotPassInTime;
    public static TestResultStatus $StatusSuccess;
    public static TestResultStatus $StatusNotCompleted;
}

TestResultStatus::$StatusIgnored = new TestResultStatus('StatusIgnored', 'Проигнорировано');
TestResultStatus::$StatusDidNotPassOnPoints = new TestResultStatus('StatusDidNotPassOnPoints', 'Не прошёл по баллам');
TestResultStatus::$StatusDidNotPassInTime = new TestResultStatus('StatusDidNotPassInTime', 'Не прошёл по времени');
TestResultStatus::$StatusSuccess = new TestResultStatus('StatusSuccess', 'Успешно');
TestResultStatus::$StatusNotCompleted = new TestResultStatus('StatusNotCompleted', 'Не завершено');

TestResultStatus::init();
