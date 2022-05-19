<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendMailable extends Mailable
{
    use Queueable, SerializesModels;
    public $name;
    public $state;
    public $debug;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($name, $state,$debug)
    {
        //
        $this->name = $name;
        $this->state = $state;
        $this->debug = $debug;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject("Adquisiciones BID")->view('email.email');
    }
}
