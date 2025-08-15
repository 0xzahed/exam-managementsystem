<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public $resetUrl;
    public $userName;

    public function __construct($resetUrl, $userName = null)
    {
        $this->resetUrl = $resetUrl;
        $this->userName = $userName;
    }

    public function build()
    {
        return $this->subject('Reset Your Password - InsightEdu')
                    ->view('emails.password-reset');
    }
}
