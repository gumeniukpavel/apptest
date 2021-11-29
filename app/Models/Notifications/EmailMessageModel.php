<?php

namespace App\Models\Notifications;

/**
 * Class EmailMessage
 * @package App\Models\Notifications
 *
 * @property string $subject
 * @property string $body
 * @property string $template
 * @property array $data
 */
class EmailMessageModel
{
    public string $subject = '';
    public string $body = '';
    public string $template = 'vendor.mail.html.message';
    public array $data = [];

    public function setSubject(string $subject): self
    {
        $this->subject = $subject;
        return $this;
    }
    public function setTemplate(string $template): self
    {
        $this->template = $template;
        return $this;
    }

    public function setBody(string $body): self
    {
        $this->body = $body;
        return $this;
    }

    public function setData(array $data): self
    {
        $this->data = array_merge($this->data, $data);
        return $this;
    }
}
