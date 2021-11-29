<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TemplatedEmail extends Mailable
{
    use Queueable, SerializesModels;

    private ?string $template;
    private ?string $body;
    private array $data;

    public function __construct(?string $template, ?string $body, string $subject, array $data = [])
    {
        $this->subject = $subject;
        $this->template = $template;
        $this->body = $body;
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $emailBody = null;
        if ($this->body)
        {
            $emailBody = $this->body;
            foreach ($this->data as $paramName => $paramValue)
            {
                $emailBody = str_replace("{{{$paramName}}}", $paramValue, $emailBody);
            }
        }

        return $this->markdown($this->template, array_merge(
            $this->data,
            [ 'customContent' => $emailBody ]
        ));
    }
}
