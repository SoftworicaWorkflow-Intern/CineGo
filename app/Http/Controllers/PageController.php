<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function about(): View
    {
        return view('pages.about');
    }

    public function contact(): View
    {
        return view('pages.contact');
    }

    public function storeContact(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:160',
            'phone' => 'nullable|string|max:30',
            'subject' => 'required|string|max:160',
            'message' => 'required|string|max:3000',
        ], [
            'name.required' => ui('validation_name', 'Please enter your name.'),
            'email.required' => ui('validation_email', 'Please enter your email address.'),
            'email.email' => ui('validation_email_invalid', 'Please enter a valid email address.'),
            'subject.required' => ui('validation_subject', 'Please enter a subject.'),
            'message.required' => ui('validation_message', 'Please enter a message.'),
        ]);

        ContactMessage::create($validated);

        return redirect()->route('pages.contact')
            ->with('success', ui('message_sent', 'Thanks! Your message has been sent.'));
    }

    public function terms(): View
    {
        return view('pages.legal', [
            'title' => ui('terms', 'Terms & Conditions'),
            'intro' => ui('terms_intro', 'These terms govern your use of the CineGo booking platform.'),
            'sections' => $this->termsSections(),
        ]);
    }

    public function privacy(): View
    {
        return view('pages.legal', [
            'title' => ui('privacy', 'Privacy Policy'),
            'intro' => ui('privacy_intro', 'How CineGo collects, uses and protects your information.'),
            'sections' => $this->privacySections(),
        ]);
    }

    public function refund(): View
    {
        return view('pages.legal', [
            'title' => ui('refund', 'Refund Policy'),
            'intro' => ui('refund_intro', 'When a ticket can be cancelled and how refunds work.'),
            'sections' => $this->refundSections(),
        ]);
    }

    /**
     * @return list<array{heading: string, body: string}>
     */
    private function termsSections(): array
    {
        return [
            [
                'heading' => ui('terms_1_h', 'Booking and tickets'),
                'body' => ui('terms_1_b', 'A booking is valid only after payment is confirmed (or marked as pay-at-counter). Each booking has a unique booking ID and QR code. One booking admits the number of seats shown on the ticket.'),
            ],
            [
                'heading' => ui('terms_2_h', 'Seat selection'),
                'body' => ui('terms_2_b', 'Seats are held for a short time while you complete payment. Seats are confirmed only when the booking is completed, and duplicate booking of the same seat is not possible.'),
            ],
            [
                'heading' => ui('terms_3_h', 'Entry and late arrival'),
                'body' => ui('terms_3_b', 'Please arrive at least 15 minutes before showtime. Late entry is subject to cinema policy. Tickets must be shown on a phone or as a printout.'),
            ],
            [
                'heading' => ui('terms_4_h', 'Responsibilities'),
                'body' => ui('terms_4_b', 'CineGo is an online booking platform for partner cinemas. Age restrictions and cinema house rules apply to every show.'),
            ],
        ];
    }

    /**
     * @return list<array{heading: string, body: string}>
     */
    private function privacySections(): array
    {
        return [
            [
                'heading' => ui('privacy_1_h', 'What we collect'),
                'body' => ui('privacy_1_b', 'Your name, email, phone number, city preference and booking history — only what is needed to confirm tickets and contact you about them.'),
            ],
            [
                'heading' => ui('privacy_2_h', 'How we use it'),
                'body' => ui('privacy_2_b', 'To process bookings, issue digital tickets, send booking confirmations and improve the CineGo experience. We never sell your personal data.'),
            ],
            [
                'heading' => ui('privacy_3_h', 'Payments'),
                'body' => ui('privacy_3_b', 'Payments are handled by your chosen provider (eSewa, Khalti or card gateway). We store transaction references for receipts and audit purposes only.'),
            ],
            [
                'heading' => ui('privacy_4_h', 'Your choices'),
                'body' => ui('privacy_4_b', 'You can update your profile and language preference any time from your account, and ask us to delete your data by contacting support.'),
            ],
        ];
    }

    /**
     * @return list<array{heading: string, body: string}>
     */
    private function refundSections(): array
    {
        return [
            [
                'heading' => ui('refund_1_h', 'Cancellation window'),
                'body' => ui('refund_1_b', 'Tickets can be cancelled up to 6 hours before showtime for a refund of the ticket amount, less any payment gateway charges.'),
            ],
            [
                'heading' => ui('refund_2_h', 'Non-refundable cases'),
                'body' => ui('refund_2_b', 'Tickets are not refundable within 6 hours of showtime, after the show has started, or for discounted promotional tickets unless required by law.'),
            ],
            [
                'heading' => ui('refund_3_h', 'How to request'),
                'body' => ui('refund_3_b', 'Contact support with your booking ID. Approved refunds are returned to the original payment method within 5–7 working days.'),
            ],
            [
                'heading' => ui('refund_4_h', 'Show cancelled by cinema'),
                'body' => ui('refund_4_b', 'If a cinema cancels a show, you are entitled to a full refund or a seat in another show of the same movie.'),
            ],
        ];
    }
}
