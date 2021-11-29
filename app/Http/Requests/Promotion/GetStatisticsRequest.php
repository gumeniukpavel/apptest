<?php


namespace App\Http\Requests\Promotion;


use Illuminate\Foundation\Http\FormRequest;

/**
 * GetStatisticsRequest
 *
 * @property int $fromDate
 * @property int $toDate
 */
class GetStatisticsRequest extends FormRequest
{
    public function rules()
    {
        return [
            'fromDate' => 'nullable|integer',
            'toDate' => 'nullable|integer',
        ];
    }
}
