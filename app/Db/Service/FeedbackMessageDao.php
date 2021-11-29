<?php

namespace App\Db\Service;

use App\Db\Entity\FeedbackMessage;
use App\Http\Requests\Feedback\FeedbackMessageRequest;

class FeedbackMessageDao
{
    public function createFeedbackMessage(FeedbackMessageRequest $request): FeedbackMessage
    {
        $feedbackMessage = new FeedbackMessage();
        $feedbackMessage->name = $request->name;
        $feedbackMessage->email = $request->email ? $request->email : null;
        $feedbackMessage->phone = $request->phone ? $request->phone : null;
        $feedbackMessage->message = $request->message;
        $feedbackMessage->save();

        return $feedbackMessage;
    }
}
