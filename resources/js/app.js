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

function initPasswordToggles() {
    document.querySelectorAll('[data-password-toggle]').forEach((toggle) => {
        toggle.addEventListener('click', () => {
            const input = document.getElementById(toggle.dataset.passwordToggle);
            if (!input) return;

            const shouldShow = input.type === 'password';
            input.type = shouldShow ? 'text' : 'password';
            toggle.setAttribute('aria-label', shouldShow ? 'Hide password' : 'Show password');
            toggle.innerHTML = shouldShow
                ? '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7"></path><circle cx="12" cy="12" r="3"></circle></svg>'
                : '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m3 3 18 18"></path><path d="M10.6 10.6a2 2 0 0 0 2.8 2.8"></path><path d="M9.9 4.2A10.7 10.7 0 0 1 12 4c7 0 10 8 10 8a16.7 16.7 0 0 1-3.1 4.4M6.6 6.6C3.7 8.5 2 12 2 12s3 7 10 7a10.7 10.7 0 0 0 2.1-.2"></path></svg>';
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
                dot.classList.toggle('bg-cg-border', i !== index);
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
 * Rotate featured movie images and let visitors choose a slide.
 */
function initHeroPagination() {
    document.querySelectorAll('[data-hero-pagination]').forEach((hero) => {
        const pages = Array.from(hero.querySelectorAll('[data-hero-page]'));
        const artLayers = Array.from(hero.querySelectorAll('[data-hero-art]'));
        const title = hero.querySelector('[data-hero-title]');
        const synopsis = hero.querySelector('[data-hero-synopsis]');
        const badges = hero.querySelector('[data-hero-badges]');
        const metadata = hero.querySelector('[data-hero-metadata]');
        const actions = hero.querySelector('[data-hero-actions]');
        const bookLink = hero.querySelector('[data-hero-book-link]');
        const duration = hero.querySelector('[data-hero-duration]');
        const language = hero.querySelector('[data-hero-language]');
        const releaseDate = hero.querySelector('[data-hero-release-date]');
        const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        if (pages.length < 2 || artLayers.length < 2) return;

        const interval = 7000;
        let activeArtIndex = artLayers.findIndex((layer) => layer.classList.contains('cg-home-hero-art-active'));
        let timer = null;
        let selection = 0;

        const stop = () => {
            if (timer) {
                window.clearInterval(timer);
                timer = null;
            }
        };

        const start = () => {
            stop();
            if (reducedMotion) return;

            timer = window.setInterval(() => {
                const activeIndex = pages.findIndex((page) => page.getAttribute('aria-current') === 'true');
                show((activeIndex + 1) % pages.length);
            }, interval);
        };

        const show = (index) => {
            const page = pages[index];
            const request = ++selection;
            const image = new Image();

            image.onload = () => {
                if (request !== selection) return;

                const nextArtIndex = (activeArtIndex + 1) % artLayers.length;
                const currentArt = artLayers[activeArtIndex];
                const nextArt = artLayers[nextArtIndex];
                const hasDetails = page.dataset.heroDetails === 'true';
                const pageSynopsis = page.dataset.heroSynopsis ?? '';

                nextArt.style.backgroundImage = `url("${page.dataset.heroImage}")`;
                nextArt.classList.add('cg-home-hero-art-active');
                currentArt.classList.remove('cg-home-hero-art-active');
                activeArtIndex = nextArtIndex;

                hero.setAttribute('aria-label', `Featured movie: ${page.dataset.heroLabel}`);
                if (title) title.textContent = page.dataset.heroTitle;

                if (synopsis) {
                    synopsis.textContent = pageSynopsis;
                    synopsis.hidden = !pageSynopsis;
                }

                if (badges) badges.hidden = page.dataset.heroBadges !== 'true';
                if (metadata) metadata.hidden = !hasDetails;

                if (actions) {
                    actions.hidden = !hasDetails;
                }

                if (hasDetails) {
                    if (duration) duration.textContent = page.dataset.heroDuration;
                    if (language) language.textContent = page.dataset.heroLanguage;
                    if (releaseDate) releaseDate.textContent = page.dataset.heroReleaseDate;
                    if (bookLink) {
                        bookLink.href = page.dataset.heroBookUrl;
                        bookLink.setAttribute('aria-label', `Book ${page.dataset.heroBookTitle} tickets`);
                    }
                }

                pages.forEach((paginationPage) => {
                    const isCurrent = paginationPage === page;
                    const indicator = paginationPage.querySelector('[data-hero-indicator]');

                    paginationPage.setAttribute('aria-current', String(isCurrent));
                    indicator.classList.toggle('w-8', isCurrent);
                    indicator.classList.toggle('w-3', !isCurrent);
                    indicator.classList.toggle('bg-cg-red', isCurrent);
                    indicator.classList.toggle('bg-white/45', !isCurrent);
                });
            };

            image.onerror = () => {
                console.error(`Unable to load hero image: ${page.dataset.heroImage}`);
            };
            image.src = page.dataset.heroImage;
        };

        pages.forEach((page, index) => {
            page.addEventListener('click', () => {
                show(index);
                start();
            });
        });

        hero.addEventListener('mouseenter', stop);
        hero.addEventListener('mouseleave', () => {
            if (!hero.contains(document.activeElement)) start();
        });
        hero.addEventListener('focusin', stop);
        hero.addEventListener('focusout', (event) => {
            if (!hero.contains(event.relatedTarget) && !hero.matches(':hover')) start();
        });
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                stop();
            } else {
                start();
            }
        });

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
            const trailerUrl = trigger.dataset.trailer;
            if (!trailerUrl || !frame) return;

            let url;
            try {
                url = new URL(trailerUrl);
            } catch (error) {
                console.error('Unable to open invalid trailer URL:', trailerUrl, error);
                return;
            }

            const hostname = url.hostname.toLowerCase().replace(/^www\./, '');
            const isYouTube = hostname === 'youtu.be' || hostname.endsWith('youtube.com') || hostname.endsWith('youtube-nocookie.com');
            const isVimeo = hostname === 'vimeo.com' || hostname === 'player.vimeo.com';

            if (isYouTube) {
                const videoId = hostname === 'youtu.be'
                    ? url.pathname.split('/').filter(Boolean)[0]
                    : url.searchParams.get('v') ?? url.pathname.match(/^\/(?:embed|shorts|live)\/([^/]+)/)?.[1];

                if (!videoId || !/^[\w-]+$/.test(videoId)) {
                    console.error('Unable to open trailer without a valid YouTube video ID:', trailerUrl);
                    return;
                }

                url = new URL(`https://www.youtube.com/embed/${encodeURIComponent(videoId)}`);
                url.searchParams.set('origin', window.location.origin);
            } else if (isVimeo) {
                const videoId = url.pathname.match(/\/(?:video\/)?(\d+)/)?.[1];

                if (!videoId) {
                    console.error('Unable to open trailer without a valid Vimeo video ID:', trailerUrl);
                    return;
                }

                url = new URL(`https://player.vimeo.com/video/${videoId}`);
            }

            url.searchParams.set('autoplay', '1');
            frame.src = url.toString();
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
    const modal = document.getElementById('admin-delete-modal');
    const message = modal?.querySelector('[data-admin-delete-message]');
    const cancel = modal?.querySelector('[data-admin-delete-cancel]');
    const confirm = modal?.querySelector('[data-admin-delete-confirm]');
    let pendingForm = null;

    const closeModal = () => {
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        pendingForm = null;
    };

    cancel?.addEventListener('click', closeModal);
    modal?.addEventListener('click', (event) => {
        if (event.target === modal) closeModal();
    });
    confirm?.addEventListener('click', () => {
        const form = pendingForm;
        closeModal();
        form?.submit();
    });

    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (event.defaultPrevented) return;
            if (!modal || !message) {
                if (!window.confirm(form.dataset.confirm)) event.preventDefault();
                return;
            }

            event.preventDefault();
            pendingForm = form;
            message.textContent = form.dataset.confirm;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        });
    });
}

/**
 * Reveal page content once it approaches the viewport.
 */
function initScrollReveal() {
    if (
        window.matchMedia('(prefers-reduced-motion: reduce)').matches ||
        !('IntersectionObserver' in window) ||
        document.querySelector('main[data-no-scroll-reveal]')
    ) {
        return;
    }

    const targets = new Set(
        Array.from(document.querySelectorAll(
            'main > section, main section, main article, main .cg-card, main .card, main form, main h1, main h2, main h3, main [class*="banner"], main img, footer > div',
        )).filter((element) => !element.closest('[data-no-scroll-reveal]')),
    );

    document.querySelectorAll('main [data-slider], main [class*="banner"], main img').forEach((element) => {
        element.dataset.scrollReveal = 'zoom';
    });

    document.querySelectorAll('main .grid').forEach((grid) => {
        Array.from(grid.children)
            .filter((child) => child.matches('article, .cg-card, .card'))
            .forEach((card, index) => {
                card.style.setProperty('--scroll-reveal-delay', `${Math.min(index * 100, 500)}ms`);
            });
    });

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;

                entry.target.dataset.scrollRevealed = 'true';
                observer.unobserve(entry.target);
            });
        },
        {
            threshold: 0.15,
        },
    );

    targets.forEach((element) => {
        if (!element.dataset.scrollReveal) {
            element.dataset.scrollReveal = 'up';
        }

        observer.observe(element);
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
    initPasswordToggles();
    initConfirmForms();
    initSliders();
    initHeroPagination();
    initTrailers();
    initCopyButtons();
    initScrollReveal();
    initServiceWorker();
});
