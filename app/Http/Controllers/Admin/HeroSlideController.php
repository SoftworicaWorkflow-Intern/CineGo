<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReorderHeroSlidesRequest;
use App\Http\Requests\StoreHeroSlideRequest;
use App\Http\Requests\UpdateHeroSlideRequest;
use App\Models\HeroSlide;
use App\Models\Movie;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class HeroSlideController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', HeroSlide::class);
        $heroSlides = HeroSlide::query()
            ->with('movie')
            ->orderBy('display_order')
            ->orderBy('id')
            ->get();

        return view('admin.hero-sliders.index', compact('heroSlides'));
    }

    public function create(): View
    {
        Gate::authorize('create', HeroSlide::class);

        return view('admin.hero-sliders.form', [
            'heroSlide' => new HeroSlide(['status' => HeroSlide::STATUS_DRAFT]),
            'movies' => Movie::query()
                ->comingSoon()
                ->whereDoesntHave('heroSlides')
                ->orderBy('title')
                ->get(),
        ]);
    }

    public function store(StoreHeroSlideRequest $request): RedirectResponse
    {
        $data = Arr::except($request->validated(), ['desktop_image', 'mobile_image']);
        $data['status'] ??= HeroSlide::STATUS_PUBLISHED;
        $data['display_order'] = 0;
        $storedPaths = [];

        try {
            $data['desktop_image'] = $this->storeImage($request->file('desktop_image'), $storedPaths);

            if ($request->hasFile('mobile_image')) {
                $data['mobile_image'] = $this->storeImage($request->file('mobile_image'), $storedPaths);
            }

            $heroSlide = DB::transaction(function () use ($data): HeroSlide {
                HeroSlide::query()->increment('display_order');

                return HeroSlide::query()->create($data);
            });
        } catch (Throwable $exception) {
            $this->cleanupAfterFailure($storedPaths, $exception);
            throw $exception;
        }

        return redirect()->route('admin.hero-sliders.edit', $heroSlide)
            ->with('success', 'Hero banner created.');
    }

    public function edit(HeroSlide $heroSlide): View
    {
        Gate::authorize('update', $heroSlide);
        $heroSlide->load('movie');
        $movies = Movie::query()
            ->comingSoon()
            ->whereDoesntHave('heroSlides')
            ->orderBy('title')
            ->get();

        if ($heroSlide->movie && ! $movies->contains('id', $heroSlide->movie_id)) {
            $movies->prepend($heroSlide->movie);
        }

        return view('admin.hero-sliders.form', compact('heroSlide', 'movies'));
    }

    public function update(UpdateHeroSlideRequest $request, HeroSlide $heroSlide): RedirectResponse
    {
        $data = Arr::except($request->validated(), [
            'desktop_image',
            'mobile_image',
            'remove_desktop_image',
            'remove_mobile_image',
        ]);
        $oldPaths = $heroSlide->uploaded_image_paths();
        $storedPaths = [];

        try {
            if ($request->hasFile('desktop_image')) {
                $data['desktop_image'] = $this->storeImage($request->file('desktop_image'), $storedPaths);
            } elseif ($request->boolean('remove_desktop_image')) {
                $data['desktop_image'] = null;
            } else {
                unset($data['desktop_image']);
            }

            if ($request->hasFile('mobile_image')) {
                $data['mobile_image'] = $this->storeImage($request->file('mobile_image'), $storedPaths);
            } elseif ($request->boolean('remove_mobile_image')) {
                $data['mobile_image'] = null;
            } else {
                unset($data['mobile_image']);
            }

            DB::transaction(fn (): bool => $heroSlide->update($data));
        } catch (Throwable $exception) {
            $this->cleanupAfterFailure($storedPaths, $exception);
            throw $exception;
        }

        $this->deleteManagedFiles(array_values(array_diff($oldPaths, $heroSlide->uploaded_image_paths())));

        return redirect()->route('admin.hero-sliders.edit', $heroSlide)
            ->with('success', 'Hero banner updated.');
    }

    public function destroy(HeroSlide $heroSlide): RedirectResponse
    {
        Gate::authorize('delete', $heroSlide);
        $imagePaths = $heroSlide->uploaded_image_paths();

        DB::transaction(fn (): bool => $heroSlide->delete());
        $this->deleteManagedFiles($imagePaths);

        return redirect()->route('admin.hero-sliders.index')
            ->with('success', 'Hero banner deleted.');
    }

    public function preview(HeroSlide $heroSlide): View
    {
        Gate::authorize('view', $heroSlide);
        $heroSlide->load('movie');

        return view('admin.hero-sliders.preview', compact('heroSlide'));
    }

    public function toggleStatus(HeroSlide $heroSlide): RedirectResponse
    {
        Gate::authorize('update', $heroSlide);
        $heroSlide->update([
            'status' => $heroSlide->status === HeroSlide::STATUS_PUBLISHED
                ? HeroSlide::STATUS_DRAFT
                : HeroSlide::STATUS_PUBLISHED,
        ]);

        return back()->with('success', $heroSlide->status === HeroSlide::STATUS_PUBLISHED
            ? 'Hero banner activated.'
            : 'Hero banner deactivated.');
    }

    public function reorder(ReorderHeroSlidesRequest $request): JsonResponse
    {
        $slideIds = $request->validated('slide_ids');
        $existingIds = HeroSlide::query()->orderBy('display_order')->orderBy('id')->pluck('id')->all();
        $requestedIds = array_map('intval', $slideIds);

        if (count($requestedIds) !== count($existingIds) || array_diff($existingIds, $requestedIds) !== []) {
            return response()->json(['message' => 'The slide list changed. Reload the page and try again.'], 422);
        }

        DB::transaction(function () use ($requestedIds): void {
            foreach ($requestedIds as $order => $slideId) {
                HeroSlide::query()->whereKey($slideId)->update(['display_order' => $order]);
            }
        });

        return response()->json(['message' => 'Slide order saved.']);
    }

    /**
     * @param  list<string>  $storedPaths
     */
    private function storeImage(UploadedFile $file, array &$storedPaths): string
    {
        $path = $file->storePublicly('hero-slides', 'public');

        if (! is_string($path)) {
            throw new RuntimeException('The hero image could not be stored.');
        }

        $storedPaths[] = $path;

        return $path;
    }

    /**
     * @param  list<string>  $paths
     */
    private function deleteManagedFiles(array $paths): void
    {
        $paths = array_values(array_filter(
            $paths,
            fn (string $path): bool => str_starts_with($path, 'hero-slides/')
                && ! str_contains($path, '..')
                && ! str_contains($path, '\\'),
        ));

        if ($paths !== [] && ! Storage::disk('public')->delete($paths)) {
            throw new RuntimeException('A hero image could not be removed from storage.');
        }
    }

    /**
     * @param  list<string>  $storedPaths
     */
    private function cleanupAfterFailure(array $storedPaths, Throwable $exception): void
    {
        if ($storedPaths !== [] && ! Storage::disk('public')->delete($storedPaths)) {
            Log::error('Failed to clean up hero slide uploads after a database error.', [
                'paths' => $storedPaths,
                'exception' => $exception,
            ]);
        }
    }
}
