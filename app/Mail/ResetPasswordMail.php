<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $token;

    public function __construct(string $token)
    {
        $this->token = $token;
    }

    public function build()
    {
        // Use frontend URL for password reset
        $frontendUrl = config('app.frontend_url', 'http://localhost:5173');
        $url = $frontendUrl . '/reset-password?token=' . $this->token . '&email=' . $this->to[0]['address'];

        return $this->subject('Pokemon Explorer - Password Reset Request')
            ->view('emails.password_reset')
            ->with(['url' => $url, 'token' => $this->token]);
    }
}
