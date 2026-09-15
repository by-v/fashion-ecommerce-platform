<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderPendingMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order)
    {
        //
    }

    public function build()
    {
        return $this->subject('Menunggu Pembayaran - Order #'.$this->order->order_number)
            ->markdown('emails.order-pending');
    }
}
