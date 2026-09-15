<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderPaidMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order)
    {
        //
    }

    public function build()
    {
        return $this->subject('Pembayaran Berhasil - Order #'.$this->order->order_number)
            ->markdown('emails.order-paid');
    }
}
