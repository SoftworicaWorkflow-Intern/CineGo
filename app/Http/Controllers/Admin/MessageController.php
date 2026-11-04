<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index(Request $request): View
    {
        $tab = $request->query('tab', 'unread');

        $messages = ContactMessage::query()
            ->when($tab === 'unread', fn ($query) => $query->where('is_read', false))
            ->when($tab === 'read', fn ($query) => $query->where('is_read', true))
            ->latest()
            ->paginate(15);

        $unreadCount = ContactMessage::where('is_read', false)->count();

        return view('admin.messages.index', compact('messages', 'tab', 'unreadCount'));
    }

    public function show(ContactMessage $message): View
    {
        $message->update(['is_read' => true]);

        return view('admin.messages.show', compact('message'));
    }

    public function destroy(ContactMessage $message): RedirectResponse
    {
        $message->delete();

        return redirect()->route('admin.messages.index')
            ->with('success', ui('admin_message_deleted', 'Message deleted.'));
    }
}
