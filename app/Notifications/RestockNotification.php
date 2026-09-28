<?php

namespace App\Notifications;

use App\Models\Favorite;
use App\Models\Product;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Notification as NotificationFacade;

class RestockNotification extends Notification
{
    use Queueable;

    public function __construct(public Product $product)
    {
    }

    /**
     * Alert every customer who favorited this product that it is orderable again.
     */
    public static function notifyFavoriters(Product $product): void
    {
        $customerIds = Favorite::where('favoritable_type', 'product')
            ->where('favoritable_id', $product->id)
            ->pluck('user_id');

        if ($customerIds->isEmpty()) {
            return;
        }

        $customers = User::whereIn('id', $customerIds)->where('role', 'customer')->get();

        if ($customers->isEmpty()) {
            return;
        }

        // Best-effort: the in-app copy is written by the database channel first;
        // a mail failure must not break the farmer's stock action.
        try {
            NotificationFacade::send($customers, new self($product));
        } catch (\Throwable $e) {
            report($e);
        }
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->product->name . ' is back in stock on MarketLink')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Good news — **' . $this->product->name . '** from *' . $this->product->farmer->stall_name . '* is available again.')
            ->line('Price: $' . number_format($this->product->price, 2) . ' / ' . $this->product->unit)
            ->action('View product', route('products.show', $this->product))
            ->line('Stock is limited, so pre-order soon to secure yours. Pay at pickup as usual.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'restock',
            'product_id' => $this->product->id,
            'title' => 'Back in stock',
            'message' => $this->product->name . ' from ' . $this->product->farmer->stall_name . ' is available again.',
            'url' => route('products.show', $this->product),
        ];
    }
}
