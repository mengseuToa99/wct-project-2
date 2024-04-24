<?php

namespace App\Mail;

use App\Models\Reporter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class ResetPassword extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $password;
    public $resetLink;

    /**
     * Create a new message instance.
     */
    public function __construct(Reporter $reporter, $password)
    {
        $this->user = $reporter;
        $this->password = $password;
        $this->resetLink = 'http://yourdomain.com/reset-password?token='.$reporter->createToken('Reset Token')->plainTextToken;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Reset Password')
                    ->view('emails.reset');
    }
}
