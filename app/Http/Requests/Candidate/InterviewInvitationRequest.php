<?php

namespace App\Http\Requests\Candidate;

use App\Db\Entity\Candidate;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * InterviewInvitationRequest
 *
 * @property int $candidateId
 * @property int $date
 * @property Carbon $timeFrom
 * @property Carbon $timeTo
 * @property string $description
 */
class InterviewInvitationRequest extends FormRequest
{
    public function rules()
    {
        return [
            'candidateId'=> [
                Rule::exists(Candidate::class, 'id'),
                'required'
            ],
            'date' => 'required|integer',
            'timeFrom' => 'required|date_format:H:i',
            'timeTo' => 'nullable|date_format:H:i|after:timeFrom',
            'description' => 'string|required'
        ];
    }

    public function startDate()
    {
        $date = Carbon::createFromTimestamp($this->date)
            ->startOfDay();
        $timeFrom = explode(':', $this->timeFrom);
        $startDate = $date->addHours($timeFrom[0])->addMinutes($timeFrom[1]);
        return $startDate
            ->setTimezone('UTC')
            ->timestamp;
    }

    public function endDate()
    {
        $date = Carbon::createFromTimestamp($this->date)
            ->startOfDay();
        if ($this->timeTo)
        {
            $timeTo = explode(':', $this->timeTo);
            $endDate = $date->addHours($timeTo[0])->addMinutes($timeTo[1]);
        }
        else
        {
            $timeFrom = explode(':', $this->timeFrom);
            $endDate = $date->addHours($timeFrom[0])->addMinutes($timeFrom[1] + 60);
        }
        return $endDate
            ->setTimezone('UTC')
            ->timestamp;
    }
}
