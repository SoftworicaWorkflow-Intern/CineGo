<?php

namespace App\Services;

use App\Models\Booking;
use Barryvdh\DomPDF\PDF;
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Output\QRMarkupSVG;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class TicketService
{
    /**
     * QR code payload: the public verification URL for this ticket.
     */
    public function payload(Booking $booking): string
    {
        return route('booking.verify', $booking->booking_code);
    }

    /**
     * Render the ticket QR code as inline SVG (no image extensions needed).
     */
    public function qrSvg(Booking $booking): string
    {
        $options = new QROptions([
            'outputInterface' => QRMarkupSVG::class,
            'outputBase64' => false,
            'eccLevel' => EccLevel::M,
            'scale' => 5,
            'svgAddXmlHeader' => false,
            'svgUseFillAttributes' => true,
            'cssClass' => 'qr-code',
        ]);

        return (new QRCode($options))->render($this->payload($booking));
    }

    /**
     * Build the downloadable PDF ticket.
     */
    public function pdf(Booking $booking): PDF
    {
        return app(PDF::class)
            ->loadView('booking.pdf', [
                'booking' => $booking,
                'qrSvg' => $this->qrSvg($booking),
            ])
            ->setPaper([0, 0, 420, 600]);
    }
}
