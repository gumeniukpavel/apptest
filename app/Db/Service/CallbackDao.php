<?php

namespace App\Db\Service;

use App\Db\Entity\CallbackMessage;
use App\Http\Requests\Feedback\CallbackMessageRequest;

class CallbackDao
{
    public function createCallbackMessage(CallbackMessageRequest $request)
    {
        $callback = new CallbackMessage();
        $callback->name = $request->name;
        $callback->phone = $request->phone;
        $callback->message = $request->message;
        $callback->save();

        return $callback;
    }
}
