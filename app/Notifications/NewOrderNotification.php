<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewOrderNotification extends Notification
{
    use Queueable;

    public function __construct(public Order $order)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New pre-order ' . $this->order->order_number . ' on MarketLink')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('You received a new pre-order **' . $this->order->order_number . '** from *' . $this->order->customer->name . '*.')
            ->line('Total: $' . number_format($this->order->total_amount, 2))
            ->line('Pickup: ' . $this->order->pickup_date->format('l, j F Y') . ' · ' . $this->order->pickup_slot)
            ->action('Review order', route('farmer.orders.show', $this->order))
            ->line('Please accept or decline it before the pickup cutoff.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'new_order',
            'title' => 'New pre-order',
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'customer' => $this->order->customer->name,
            'total' => $this->order->total_amount,
            'pickup_date' => $this->order->pickup_date->toDateString(),
            'pickup_slot' => $this->order->pickup_slot,
            'message' => 'New pre-order ' . $this->order->order_number . ' from ' . $this->order->customer->name,
        ];
    }
}
