<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderShippedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order)
    {
        //
    }

    public function build()
    {
        return $this->subject('Pesanan #' . $this->order->order_number . ' Sedang Dalam Perjalanan 🚚')
            ->markdown('emails.order-shipped');
    }
}
