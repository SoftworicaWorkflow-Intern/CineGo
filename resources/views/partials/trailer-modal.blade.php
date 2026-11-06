{{-- Trailer modal, populated by data-trailer triggers (see resources/js/app.js) --}}
<div
    id="trailer-modal"
    data-modal
    class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm"
    role="dialog"
    aria-modal="true"
    aria-label="Trailer"
>
    <div class="w-full max-w-4xl rounded-xl border border-cg-border bg-cg-card p-3 shadow-2xl shadow-black/20 sm:p-4">
        <div class="mb-3 flex items-center justify-between">
            <span class="text-sm font-semibold uppercase tracking-widest text-cg-muted">Trailer</span>
            <button
                type="button"
                data-modal-close
                class="flex h-10 w-10 items-center justify-center rounded-lg border border-cg-border bg-cg-charcoal text-cg-white transition hover:border-cg-red"
                aria-label="{{ ui('close', 'Close') }}"
            >
                <x-icon name="x" :size="20" />
            </button>
        </div>
        <div class="relative aspect-video w-full overflow-hidden rounded-xl border border-cg-border bg-black">
            <iframe
                data-trailer-frame
                class="absolute inset-0 h-full w-full"
                src=""
                title="Movie trailer"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                referrerpolicy="strict-origin-when-cross-origin"
                allowfullscreen
            ></iframe>
        </div>
    </div>
</div>
