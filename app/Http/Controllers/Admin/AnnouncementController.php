<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function index(): View
    {
        $announcements = Announcement::orderBy('sort_order')->get();

        return view('admin.announcements.index', compact('announcements'));
    }

    public function store(Request $request): RedirectResponse
    {
        Announcement::create($this->validated($request));

        return redirect()->route('admin.announcements.index')
            ->with('success', ui('admin_announcement_created', 'Announcement published.'));
    }

    public function update(Request $request, Announcement $announcement): RedirectResponse
    {
        $announcement->update($this->validated($request));

        return redirect()->route('admin.announcements.index')
            ->with('success', ui('admin_announcement_updated', 'Announcement updated.'));
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        $announcement->delete();

        return redirect()->route('admin.announcements.index')
            ->with('success', ui('admin_announcement_deleted', 'Announcement deleted.'));
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'message' => 'required|string|max:200',
            'link' => 'nullable|string|max:300',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ], [
            'message.required' => ui('validation_announcement', 'Please enter the announcement text.'),
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);

        return $validated;
    }
}
