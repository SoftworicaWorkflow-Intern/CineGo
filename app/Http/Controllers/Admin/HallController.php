<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hall;
use App\Models\Seat;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HallController extends Controller
{
    /**
     * Create a screen (hall) and generate its seat layout.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'cinema_id' => 'required|integer|exists:cinemas,id',
            'name' => 'required|string|max:60',
            'total_rows' => 'required|integer|min:3|max:26',
            'seats_per_row' => 'required|integer|min:4|max:30',
        ], [
            'name.required' => ui('validation_hall_name', 'Please enter a screen name.'),
            'cinema_id.required' => ui('validation_cinema', 'Please choose a cinema branch.'),
        ]);

        $hall = DB::transaction(function () use ($validated) {
            $hall = Hall::create($validated);
            $this->syncSeats($hall);

            return $hall;
        });

        return redirect()->route('admin.cinemas.edit', $hall->cinema_id)
            ->with('success', ui('admin_hall_created', 'Screen created and seats generated.'));
    }

    /**
     * Update a screen, keeping its seat layout in sync.
     */
    public function update(Request $request, Hall $hall): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:60',
            'total_rows' => 'required|integer|min:3|max:26',
            'seats_per_row' => 'required|integer|min:4|max:30',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        DB::transaction(function () use ($hall, $validated) {
            $hall->update($validated);
            $this->syncSeats($hall);
        });

        return redirect()->route('admin.cinemas.edit', $hall->cinema_id)
            ->with('success', ui('admin_hall_updated', 'Screen updated.'));
    }

    public function destroy(Hall $hall): RedirectResponse
    {
        $cinemaId = $hall->cinema_id;
        $hall->delete();

        return redirect()->route('admin.cinemas.edit', $cinemaId)
            ->with('success', ui('admin_hall_deleted', 'Screen deleted.'));
    }

    /**
     * Create missing seats and retire seats outside the current layout.
     * Seats that already have bookings are deactivated instead of removed,
     * so historical tickets keep their seat references.
     */
    private function syncSeats(Hall $hall): void
    {
        $desired = [];

        for ($row = 0; $row < $hall->total_rows; $row++) {
            $label = chr(65 + $row);

            for ($number = 1; $number <= $hall->seats_per_row; $number++) {
                $desired[$label.'-'.$number] = [
                    'row_label' => $label,
                    'seat_number' => $number,
                    'seat_type' => $this->seatTypeForRow($row, $hall->total_rows),
                ];
            }
        }

        $existing = Seat::query()->where('hall_id', $hall->id)->get()->keyBy(fn (Seat $seat) => $seat->row_label.'-'.$seat->seat_number);

        foreach ($desired as $key => $attributes) {
            if ($existing->has($key)) {
                $existing[$key]->update(['seat_type' => $attributes['seat_type'], 'is_active' => true]);

                continue;
            }

            Seat::create(array_merge($attributes, ['hall_id' => $hall->id, 'is_active' => true]));
        }

        foreach ($existing as $key => $seat) {
            if (array_key_exists($key, $desired)) {
                continue;
            }

            $inUse = $seat->bookingSeats()->exists() || $seat->locks()->exists();

            if ($inUse) {
                $seat->update(['is_active' => false]);

                continue;
            }

            $seat->delete();
        }
    }

    private function seatTypeForRow(int $rowIndex, int $totalRows): string
    {
        $lastRow = $totalRows - 1;

        if ($rowIndex === $lastRow) {
            return 'recliner';
        }

        if ($rowIndex === $lastRow - 1) {
            return 'premium';
        }

        return 'normal';
    }
}
