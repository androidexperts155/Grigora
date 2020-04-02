<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ApproveAcount extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($username)
    {   
        $this->username = $username;
     
        //$this->password = $password;
    }
    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('no_reply@1stAlert', "Grigora")
                        ->subject('Grigora Account Approved')
                        ->view('emails.approveAccount')
                        ->with(['user_name' => $this->username]);
    }
}
