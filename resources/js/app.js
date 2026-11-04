// CineGo – shared UI behaviour

/**
 * Toggle an element that is shown/hidden via the `hidden` attribute.
 * Usage: <button data-toggle="target-id">…</button>
 */
function initToggles() {
    document.querySelectorAll('[data-toggle]').forEach((trigger) => {
        trigger.addEventListener('click', (event) => {
            event.stopPropagation();
            const target = document.getElementById(trigger.dataset.toggle);
            if (!target) return;
            target.classList.toggle('hidden');
            const expanded = !target.classList.contains('hidden');
            trigger.setAttribute('aria-expanded', String(expanded));
        });
    });

    // Close open dropdowns when clicking anywhere else.
    document.addEventListener('click', (event) => {
        if (event.target.closest('[data-dropdown]')) return;
        document
            .querySelectorAll('[data-dropdown-menu]:not(.hidden)')
            .forEach((menu) => menu.classList.add('hidden'));
    });

    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') return;
        document
            .querySelectorAll('[data-dropdown-menu]:not(.hidden)')
            .forEach((menu) => menu.classList.add('hidden'));
        document.querySelectorAll('[data-modal]:not(.hidden)').forEach((modal) => modal.classList.add('hidden'));
    });

    // Close modals via backdrop / close buttons.
    document.querySelectorAll('[data-modal]').forEach((modal) => {
        modal.addEventListener('click', (event) => {
            if (event.target === modal || event.target.closest('[data-modal-close]')) {
                modal.classList.add('hidden');
            }
        });
    });
}

/**
 * Auto-submit the enclosing form when a select changes.
 * Usage: <form data-autosubmit><select name="city_id">…</select></form>
 */
function initAutoSubmit() {
    document.querySelectorAll('[data-autosubmit]').forEach((form) => {
        form.querySelectorAll('select').forEach((select) => {
            select.addEventListener('change', () => form.submit());
        });
    });
}

/**
 * Cinematic hero slider with autoplay, dots and arrows.
 * Usage: <div data-slider data-interval="6000"> … slides … </div>
 */
function initSliders() {
    document.querySelectorAll('[data-slider]').forEach((slider) => {
        const slides = Array.from(slider.querySelectorAll('[data-slide]'));
        if (slides.length < 2) return;

        const dots = Array.from(slider.querySelectorAll('[data-slide-dot]'));
        const interval = parseInt(slider.dataset.interval ?? '6500', 10);
        let index = slides.findIndex((slide) => slide.classList.contains('cg-slide-active'));
        if (index < 0) index = 0;
        let timer = null;

        const show = (next) => {
            index = (next + slides.length) % slides.length;
            slides.forEach((slide, i) => slide.classList.toggle('cg-slide-active', i === index));
            dots.forEach((dot, i) => {
                dot.classList.toggle('bg-cg-red', i === index);
                dot.classList.toggle('bg-white/30', i !== index);
                dot.setAttribute('aria-current', i === index ? 'true' : 'false');
            });
        };

        const start = () => {
            stop();
            timer = window.setInterval(() => show(index + 1), interval);
        };

        const stop = () => {
            if (timer) window.clearInterval(timer);
            timer = null;
        };

        slider.querySelectorAll('[data-slide-next]').forEach((el) =>
            el.addEventListener('click', () => {
                show(index + 1);
                start();
            }),
        );
        slider.querySelectorAll('[data-slide-prev]').forEach((el) =>
            el.addEventListener('click', () => {
                show(index - 1);
                start();
            }),
        );
        dots.forEach((dot, i) =>
            dot.addEventListener('click', () => {
                show(i);
                start();
            }),
        );

        slider.addEventListener('mouseenter', stop);
        slider.addEventListener('mouseleave', start);
        slider.addEventListener('focusin', stop);
        slider.addEventListener('focusout', start);

        show(index);
        start();
    });
}

/**
 * Open a trailer modal populated from data-trailer on the trigger element.
 */
function initTrailers() {
    const modal = document.getElementById('trailer-modal');
    if (!modal) return;
    const frame = modal.querySelector('[data-trailer-frame]');

    document.querySelectorAll('[data-trailer]').forEach((trigger) => {
        trigger.addEventListener('click', () => {
            const url = trigger.dataset.trailer;
            if (!url || !frame) return;
            frame.src = `${url}${url.includes('?') ? '&' : '?'}autoplay=1`;
            modal.classList.remove('hidden');
        });
    });

    modal.addEventListener('click', (event) => {
        if (event.target === modal || event.target.closest('[data-modal-close]')) {
            frame.src = '';
        }
    });
}

/**
 * Copy the element's text (data-copy) and flash the label.
 */
function initCopyButtons() {
    document.querySelectorAll('[data-copy]').forEach((button) => {
        button.addEventListener('click', async () => {
            try {
                await navigator.clipboard.writeText(button.dataset.copy);
                const original = button.textContent;
                button.textContent = 'Copied!';
                window.setTimeout(() => {
                    button.textContent = original;
                }, 1600);
            } catch {
                /* clipboard unavailable */
            }
        });
    });
}

/**
 * Confirm destructive / irreversible form submissions.
 * Usage: <form data-confirm="Delete this item?">…</form>
 */
function initConfirmForms() {
    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (event.defaultPrevented) return;
            if (!window.confirm(form.dataset.confirm)) {
                event.preventDefault();
            }
        });
    });
}

/**
 * Register the service worker (production builds only — the dev server
 * relies on network requests so HMR keeps working).
 */
function initServiceWorker() {
    if (!import.meta.env.PROD || !('serviceWorker' in navigator)) return;

    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch(() => {
            /* offline support unavailable — the site still works online */
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initToggles();
    initAutoSubmit();
    initConfirmForms();
    initSliders();
    initTrailers();
    initCopyButtons();
    initServiceWorker();
});
