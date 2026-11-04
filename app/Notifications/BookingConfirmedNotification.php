<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingConfirmedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  list<string>  $channels
     */
    public function __construct(
        public Booking $booking,
        array $channels = ['database'],
    ) {
        $this->channels = array_merge(['mail'], $channels);
    }

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return $this->channels;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'booking_code' => $this->booking->booking_code,
            'movie' => $this->booking->movie_title,
            'cinema' => $this->booking->cinema_name,
            'show_date' => $this->booking->show_date->toFormattedDateString(),
            'start_time' => $this->booking->start_time,
            'seats' => $this->booking->seat_labels(),
            'total' => $this->booking->total_amount,
            'url' => route('booking.ticket', $this->booking->booking_code),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $booking = $this->booking;

        return (new MailMessage)
            ->subject('CineGo booking confirmed — '.$booking->booking_code)
            ->greeting('Hello '.$notifiable->name.'!')
            ->line('Your booking for '.$booking->movie_title.' is confirmed.')
            ->line('Cinema: '.$booking->cinema_name.' · '.$booking->hall_name)
            ->line('Date: '.$booking->show_date->toFormattedDateString().' at '.$booking->start_time)
            ->line('Seats: '.$booking->seat_labels())
            ->line('Total: Rs. '.number_format($booking->total_amount))
            ->line('Booking ID: '.$booking->booking_code)
            ->action('View ticket', route('booking.ticket', $booking->booking_code))
            ->line('Please show your QR ticket at the entrance.');
    }
}
