<?php

namespace App\Mail;

use App\Models\Reporter;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPassword extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $password;
    public $resetLink;

    public function __construct(Reporter $reporter, $password)
    {
        $this->user = $reporter;
        $this->password = $password;
        $this->resetLink = 'http://yourdomain.com/reset-password?token='.$reporter->createToken('Reset Token')->plainTextToken;
    }

    public function build()
    {
        return $this->subject('Reset Password')
                    ->view('emails.reset');
    }
}
