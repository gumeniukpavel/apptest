<?php

namespace App\Db\Service;

use App\Db\Entity\LetterTemplate;
use App\Db\Entity\User;
use App\Http\Requests\LetterTemplate\CreateLetterTemplateRequest;
use Illuminate\Database\Eloquent\Builder;

class LetterTemplateDao
{
    public function saveLetterTemplate(User $user, CreateLetterTemplateRequest $request)
    {
        $letterTemplate = new LetterTemplate();
        $letterTemplate->user_id = $user->id;
        $letterTemplate->type_id = $request->typeId;
        $letterTemplate->subject = $request->subject;
        $letterTemplate->body = $request->body;
        $letterTemplate->save();

        $this->setLetterTemplateStatus($letterTemplate, true, $user);
        return $letterTemplate;
    }

    public function getLetterTemplatesList(User $user): Builder
    {
        return LetterTemplate::query()
            ->where('user_id', $user->id);
    }

    public function getOne(int $id): ?LetterTemplate
    {
        /** @var LetterTemplate $letterTemplate */
        $letterTemplate = LetterTemplate::query()
            ->where('id', $id)
            ->first();
        return $letterTemplate;
    }

    public function setLetterTemplateStatus(LetterTemplate $template, bool $status, User $user)
    {
        LetterTemplate::query()
            ->where([
                'user_id' => $user->id,
                'type_id' => $template->type_id,
                'is_active' => true
            ])
            ->update(['is_active' => false]);
        $template->is_active = $status;
        $template->save();

        return $template;
    }
}
