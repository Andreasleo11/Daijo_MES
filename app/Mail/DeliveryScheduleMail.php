<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class DeliveryScheduleMail extends Mailable
{
    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function build()
    {
        return $this->subject('Production Delivery Detail H+1 – H+3')
            ->view('mail.delivery-detail');
    }
}
