<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class AccountVerification extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($username,$url)
    {   
        $this->username = $username;
     
        $this->url = $url;
    }
    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('no_reply@1stAlert', "Grigora")
                        ->subject('Verification of your account')
                        ->view('emails.AccountVerification')
                        ->with(['user_name' => $this->username,'url'=>$this->url]);
    }
}
