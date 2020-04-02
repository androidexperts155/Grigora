<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ForgotPassword extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($username,$password)
    {   
        $this->username = $username;
     
        $this->password = $password;
    }
    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('no_reply@1stAlert', "Grigora")
                        ->subject('New Password of Grigora account')
                        ->view('emails.forgotpassword')
                        ->with(['user_name' => $this->username,'pass'=>$this->password]);
    }
}
