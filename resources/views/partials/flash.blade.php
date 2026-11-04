@php
    $flashMessages = collect([
        ['type' => 'success', 'text' => session('success')],
        ['type' => 'info', 'text' => session('status')],
    ])->filter(fn ($item) => ! empty($item['text']));

    $errorMessages = $errors->all();
@endphp

@if ($flashMessages->isNotEmpty() || $errorMessages)
    <div
        id="toast-stack"
        class="pointer-events-none fixed inset-x-4 bottom-4 z-50 flex flex-col items-center gap-2 sm:inset-x-auto sm:right-4 sm:items-end"
        role="status"
        aria-live="polite"
    >
        @foreach ($flashMessages as $flash)
            <div class="toast pointer-events-auto flex w-full max-w-md items-start gap-3 rounded-xl border border-cg-border bg-cg-card px-4 py-3.5 text-sm shadow-2xl shadow-black/60 sm:w-auto">
                <span class="mt-0.5 text-cg-green"><x-icon name="check-circle" :size="18" /></span>
                <p class="text-cg-white">{{ $flash['text'] }}</p>
            </div>
        @endforeach

        @foreach ($errorMessages as $error)
            <div class="toast pointer-events-auto flex w-full max-w-md items-start gap-3 rounded-xl border border-cg-red/50 bg-cg-card px-4 py-3.5 text-sm shadow-2xl shadow-black/60 sm:w-auto">
                <span class="mt-0.5 text-cg-red"><x-icon name="alert" :size="18" /></span>
                <p class="text-cg-white">{{ $error }}</p>
            </div>
        @endforeach
    </div>

    <script>
        (function () {
            var stack = document.getElementById('toast-stack');
            if (!stack) return;
            window.setTimeout(function () {
                stack.style.transition = 'opacity .4s ease';
                stack.style.opacity = '0';
                window.setTimeout(function () { stack.remove(); }, 450);
            }, 5200);
        })();
    </script>
@endif
