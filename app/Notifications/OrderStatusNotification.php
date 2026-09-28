<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusNotification extends Notification
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
        $status = $this->order->statusLabel();
        $isFarmer = method_exists($notifiable, 'isFarmer') && $notifiable->isFarmer();

        $message = (new MailMessage)
            ->subject('MarketLink order ' . $this->order->order_number . ' — ' . $status)
            ->greeting('Hello ' . $notifiable->name . ',');

        if ($isFarmer) {
            $message->line('Pre-order **' . $this->order->order_number . '** from *' . $this->order->customer->name . '* is now *' . $status . '*.');
        } else {
            $message->line('Your pre-order **' . $this->order->order_number . '** from *' . $this->order->farmer->stall_name . '* is now *' . $status . '*.');
        }

        $message->line('Pickup: ' . $this->order->pickup_date->format('l, j F Y') . ' · ' . $this->order->pickup_slot);

        if ($this->order->status === 'ready_for_pickup' && ! $isFarmer) {
            $message->line('Your items are ready — see you at the stall!');
        }

        return $message->action('View order', $isFarmer
                ? route('farmer.orders.show', $this->order)
                : route('customer.orders.show', $this->order))
            ->line('Payment is settled in person at pickup. Thank you for supporting local farmers.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'order_status',
            'title' => 'Order ' . $this->order->statusLabel(),
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'status' => $this->order->status,
            'farmer' => $this->order->farmer->stall_name,
            'pickup_date' => $this->order->pickup_date->toDateString(),
            'pickup_slot' => $this->order->pickup_slot,
            'message' => 'Order ' . $this->order->order_number . ' is ' . $this->order->statusLabel(),
        ];
    }
}
