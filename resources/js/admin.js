/**
 * CineGo admin reporting — Chart.js dashboards.
 * Only loaded on admin pages (see vite.config.js input).
 */
import Chart from 'chart.js/auto';

function readChartData(id) {
    const node = document.getElementById(id);
    if (!node) return null;

    try {
        return JSON.parse(node.textContent);
    } catch (error) {
        return null;
    }
}

function mountCharts() {
    const data = readChartData('chart-data');
    const canvas = document.getElementById('bookings-chart');

    if (!data || !canvas) return;

    const accent = '#E50914';
    const muted = '#666666';
    const grid = 'rgba(23,23,23,0.08)';

    new Chart(canvas, {
        data: {
            labels: data.labels,
            datasets: [
                {
                    type: 'bar',
                    label: 'Bookings',
                    data: data.bookings,
                    backgroundColor: 'rgba(229,9,20,0.55)',
                    borderColor: accent,
                    borderWidth: 1,
                    borderRadius: 6,
                    yAxisID: 'y',
                    maxBarThickness: 26,
                },
                {
                    type: 'line',
                    label: 'Revenue (Rs.)',
                    data: data.revenue,
                    borderColor: '#16a34a',
                    backgroundColor: 'rgba(22,163,74,0.12)',
                    fill: true,
                    tension: 0.35,
                    pointRadius: 3,
                    pointBackgroundColor: '#16a34a',
                    borderWidth: 2,
                    yAxisID: 'y1',
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: {
                    labels: { color: muted, boxWidth: 12, boxHeight: 12, usePointStyle: true },
                },
                tooltip: {
                    backgroundColor: '#ffffff',
                    borderColor: '#e5e7eb',
                    borderWidth: 1,
                    titleColor: '#ffffff',
                    bodyColor: '#666666',
                    padding: 10,
                    cornerRadius: 8,
                },
            },
            scales: {
                x: {
                    ticks: { color: muted, maxRotation: 0, autoSkip: true },
                    grid: { color: grid },
                },
                y: {
                    position: 'left',
                    beginAtZero: true,
                    ticks: { color: muted, precision: 0 },
                    grid: { color: grid },
                    title: { display: true, text: 'Bookings', color: muted },
                },
                y1: {
                    position: 'right',
                    beginAtZero: true,
                    ticks: { color: muted },
                    grid: { drawOnChartArea: false },
                    title: { display: true, text: 'Revenue (Rs.)', color: muted },
                },
            },
        },
    });
}

function mountHeroSlideForm() {
    const form = document.querySelector('[data-hero-slide-form]');
    if (!form) return;

    const movieSelect = form.querySelector('[data-movie-select]');
    const previewFrame = form.querySelector('[data-movie-preview]');
    const selectedImageUrls = new Map();
    const savedImageSources = new Map(
        Array.from(form.querySelectorAll('[data-preview-image]')).map((image) => [
            image.dataset.previewImage,
            image.getAttribute('src') || '',
        ]),
    );
    let previewMode = 'desktop';

    const displayImage = (image, source) => {
        if (!image) return;
        image.src = source;
        image.classList.toggle('hidden', !source);
        image.classList.toggle('block', Boolean(source));
    };

    const imageSource = (field) => {
        const selectedSource = selectedImageUrls.get(field);
        if (selectedSource) return selectedSource;

        const removeCheckbox = form.querySelector(`[data-remove-image="${field}"]`);
        if (removeCheckbox?.checked) return '';

        if (field === 'mobile_image') {
            return savedImageSources.get('mobile') || imageSource('desktop_image');
        }

        return savedImageSources.get('desktop') || '';
    };

    const updatePreviewMode = (mode) => {
        previewMode = mode;
        if (previewFrame) {
            previewFrame.classList.toggle('aspect-video', mode === 'desktop');
            previewFrame.classList.toggle('w-full', mode === 'desktop');
            previewFrame.classList.toggle('aspect-[9/16]', mode === 'mobile');
            previewFrame.classList.toggle('max-w-[280px]', mode === 'mobile');
        }

        form.querySelectorAll('[data-preview-mode-toggle]').forEach((button) => {
            const active = button.dataset.previewModeToggle === mode;
            button.setAttribute('aria-pressed', String(active));
            button.classList.toggle('bg-cg-red', active);
            button.classList.toggle('text-white', active);
            button.classList.toggle('text-cg-muted', !active);
        });

        form.querySelectorAll('[data-preview-image-layer]').forEach((image) => {
            const isVisible = image.dataset.previewImage === mode;
            const field = mode === 'desktop' ? 'desktop_image' : 'mobile_image';
            const source = imageSource(field);
            image.src = source;
            image.classList.toggle('hidden', !isVisible);
            image.classList.toggle('block', isVisible && Boolean(source));
        });
    };

    const updateUpload = (input) => {
        const field = input.dataset.imageUpload;
        const file = input.files[0];
        const filename = form.querySelector(`[data-file-name="${field}"]`);
        const removeCheckbox = form.querySelector(`[data-remove-image="${field}"]`);
        const thumbnail = form.querySelector(`[data-upload-thumbnail="${field}"]`);
        const previewImage = form.querySelector(`[data-preview-image="${field === 'desktop_image' ? 'desktop' : 'mobile'}"]`);

        if (file) {
            const previousUrl = selectedImageUrls.get(field);
            if (previousUrl) URL.revokeObjectURL(previousUrl);

            const previewUrl = URL.createObjectURL(file);
            selectedImageUrls.set(field, previewUrl);
            displayImage(thumbnail, previewUrl);
            displayImage(previewImage, previewUrl);
            if (filename) filename.textContent = file.name;
            if (removeCheckbox) removeCheckbox.checked = false;
        } else {
            const previousUrl = selectedImageUrls.get(field);
            if (previousUrl) URL.revokeObjectURL(previousUrl);
            selectedImageUrls.delete(field);

            const source = imageSource(field);
            displayImage(thumbnail, field === 'mobile_image' && !savedImageSources.get('mobile') ? '' : source);
            displayImage(previewImage, source);
            if (filename) {
                filename.textContent = source
                    ? (field === 'mobile_image' && !savedImageSources.get('mobile') ? 'No mobile image; desktop image will be used' : 'Current image saved')
                    : 'No image selected';
            }
        }

        updatePreviewMode(previewMode);
    };

    form.querySelectorAll('[data-image-upload]').forEach((imageInput) => {
        imageInput.addEventListener('change', () => updateUpload(imageInput));
        const zone = imageInput.closest('[data-drop-zone]');
        const target = zone?.querySelector('[data-drop-target]');

        target?.addEventListener('dragover', (event) => {
            event.preventDefault();
            target.classList.add('border-cg-red');
        });
        target?.addEventListener('dragleave', () => target.classList.remove('border-cg-red'));
        target?.addEventListener('drop', (event) => {
            event.preventDefault();
            target.classList.remove('border-cg-red');
            const [file] = event.dataTransfer.files;
            if (!file) return;

            const transfer = new DataTransfer();
            transfer.items.add(file);
            imageInput.files = transfer.files;
            updateUpload(imageInput);
        });
    });

    form.querySelectorAll('[data-remove-image]').forEach((checkbox) => {
        checkbox.addEventListener('change', () => {
            const input = form.querySelector(`[data-image-upload="${checkbox.dataset.removeImage}"]`);
            if (input) updateUpload(input);
        });
    });

    form.querySelectorAll('[data-preview-mode-toggle]').forEach((button) => {
        button.addEventListener('click', () => updatePreviewMode(button.dataset.previewModeToggle));
    });
    updatePreviewMode(previewMode);

    const updateMoviePreview = () => {
        if (!movieSelect) return;

        const option = movieSelect.selectedOptions[0];
        const hasMovie = Boolean(option?.value);
        const localizedTitle = form.dataset.locale === 'np' && option?.dataset.titleNp
            ? option.dataset.titleNp
            : option?.dataset.titleEn;
        const localizedSynopsis = form.dataset.locale === 'np' && option?.dataset.synopsisNp
            ? option.dataset.synopsisNp
            : option?.dataset.synopsisEn;
        const setText = (selector, value) => {
            const element = form.querySelector(selector);
            if (element) element.textContent = value || '';
        };
        setText('[data-preview-label]', hasMovie ? 'Now Showing' : 'Select a movie');
        setText('[data-preview-title]', localizedTitle || 'Movie title');
        setText('[data-preview-genres]', option?.dataset.genres);
        setText('[data-preview-duration]', option?.dataset.duration);
        setText('[data-preview-language]', option?.dataset.language);
        setText('[data-preview-certificate]', option?.dataset.certificate);
        setText('[data-preview-release-date]', option?.dataset.releaseDate);
        setText('[data-preview-synopsis]', localizedSynopsis);

        const bookLink = form.querySelector('[data-preview-book]');
        if (bookLink) bookLink.href = hasMovie ? option.dataset.movieUrl : '#';

        const trailerLink = form.querySelector('[data-preview-trailer]');
        if (trailerLink) {
            const trailerUrl = option?.dataset.trailer || '';
            trailerLink.href = trailerUrl || '#';
            trailerLink.classList.toggle('hidden', !trailerUrl);
            trailerLink.classList.toggle('inline-flex', Boolean(trailerUrl));
        }

        const certificateSeparator = form.querySelector('[data-preview-certificate-separator]');
        if (certificateSeparator) certificateSeparator.classList.toggle('hidden', !option?.dataset.certificate);
    };

    movieSelect?.addEventListener('change', updateMoviePreview);
    updateMoviePreview();
}

function mountHeroSlideReordering() {
    const table = document.querySelector('[data-slide-list]');
    if (!table) return;

    const body = table.querySelector('tbody');
    const feedback = document.querySelector('[data-reorder-feedback]');
    let draggedRow = null;
    let startingOrder = [];

    const rows = () => Array.from(body.querySelectorAll('[data-slide-row]'));

    table.addEventListener('dragstart', (event) => {
        draggedRow = event.target.closest('[data-slide-row]');
        if (!draggedRow || !event.target.closest('[data-drag-handle]')) {
            event.preventDefault();
            draggedRow = null;
            return;
        }

        startingOrder = rows().map((row) => row.dataset.slideId);
        event.dataTransfer.effectAllowed = 'move';
        event.dataTransfer.setData('text/plain', draggedRow.dataset.slideId);
        draggedRow.classList.add('opacity-50');
    });

    table.addEventListener('dragover', (event) => {
        if (!draggedRow) return;
        event.preventDefault();

        const targetRow = event.target.closest('[data-slide-row]');
        if (!targetRow || targetRow === draggedRow) return;

        const bounds = targetRow.getBoundingClientRect();
        const insertAfter = event.clientY > bounds.top + bounds.height / 2;
        body.insertBefore(draggedRow, insertAfter ? targetRow.nextSibling : targetRow);
    });

    table.addEventListener('dragend', async () => {
        if (!draggedRow) return;

        draggedRow.classList.remove('opacity-50');
        draggedRow = null;
        const slideIds = rows().map((row) => row.dataset.slideId);
        if (slideIds.every((slideId, index) => slideId === startingOrder[index])) return;

        feedback.textContent = 'Saving new order…';
        try {
            const response = await fetch(table.dataset.reorderUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ slide_ids: slideIds }),
            });
            const result = await response.json();

            if (!response.ok) throw new Error(result.message || 'The slide order could not be saved.');
            feedback.textContent = result.message;
        } catch (error) {
            window.alert(error.message || 'The slide order could not be saved.');
            window.location.reload();
        }
    });
}

function mountPosterDropzone() {
    const dropzone = document.querySelector('[data-poster-dropzone]');
    const input = document.querySelector('[data-poster-input]');
    const preview = document.querySelector('[data-poster-preview]');
    const filename = document.querySelector('[data-poster-filename]');

    if (!dropzone || !input || !preview || !filename) return;

    const updateFile = (file) => {
        if (!file) return;

        const transfer = new DataTransfer();
        transfer.items.add(file);
        input.files = transfer.files;
        filename.textContent = file.name;
        filename.classList.remove('hidden');
        preview.src = URL.createObjectURL(file);
        preview.classList.remove('hidden');
    };

    input.addEventListener('change', () => updateFile(input.files[0]));

    ['dragenter', 'dragover'].forEach((eventName) => {
        dropzone.addEventListener(eventName, (event) => {
            event.preventDefault();
            dropzone.classList.add('border-cg-red', 'bg-cg-red/5');
        });
    });

    ['dragleave', 'drop'].forEach((eventName) => {
        dropzone.addEventListener(eventName, (event) => {
            event.preventDefault();
            dropzone.classList.remove('border-cg-red', 'bg-cg-red/5');
        });
    });

    dropzone.addEventListener('drop', (event) => updateFile(event.dataTransfer.files[0]));
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        mountCharts();
        mountHeroSlideForm();
        mountHeroSlideReordering();
        mountPosterDropzone();
    });
} else {
    mountCharts();
    mountHeroSlideForm();
    mountHeroSlideReordering();
    mountPosterDropzone();
}
