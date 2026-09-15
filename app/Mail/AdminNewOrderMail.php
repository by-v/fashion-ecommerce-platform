<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminNewOrderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order)
    {
        //
    }

    public function build()
    {
        return $this->subject('🛍️ Pesanan Baru #'.$this->order->order_number.' Telah Dibayar')
            ->markdown('emails.admin-new-order');
    }
}
