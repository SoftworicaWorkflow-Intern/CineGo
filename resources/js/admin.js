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

    const inputs = Array.from(form.querySelectorAll('[data-image-upload]'));
    const originalImageSources = new Map(
        Array.from(form.querySelectorAll('[data-preview-image]')).map((image) => [
            image.dataset.previewImage,
            image.getAttribute('src') || '',
        ]),
    );
    const selectedImageUrls = new Map();

    const currentImageSource = (field) => {
        const selectedSource = selectedImageUrls.get(field);
        if (selectedSource) return selectedSource;

        const removeCheckbox = form.querySelector(`[data-remove-image="${field}"]`);
        if (removeCheckbox?.checked) return '';

        const previewType = field === 'mobile_image' ? 'mobile' : 'desktop';
        return originalImageSources.get(previewType) || '';
    };

    const displayImage = (image, source) => {
        if (!image) return;
        image.src = source;
        image.classList.toggle('hidden', !source);
        image.classList.toggle('block', Boolean(source));
    };

    const setPreviewImage = (field, source) => {
        const imageType = field === 'mobile_image' ? 'mobile' : 'desktop';
        const previewImage = form.querySelector(`[data-preview-image="${imageType}"]`);
        const thumbnail = form.querySelector(`[data-upload-thumbnail="${field}"]`);

        displayImage(thumbnail, source);

        displayImage(previewImage, source);
        const mobilePreview = form.querySelector('[data-preview-image="mobile"]');
        const mobileSource = currentImageSource('mobile_image') || currentImageSource('desktop_image');
        displayImage(mobilePreview, mobileSource);
    };

    const updateUpload = (input) => {
        const file = input.files[0];
        const filename = form.querySelector(`[data-file-name="${input.dataset.imageUpload}"]`);
        const removeCheckbox = form.querySelector(`[data-remove-image="${input.dataset.imageUpload}"]`);

        if (file) {
            const previousUrl = selectedImageUrls.get(input.dataset.imageUpload);
            if (previousUrl) URL.revokeObjectURL(previousUrl);

            const previewUrl = URL.createObjectURL(file);
            selectedImageUrls.set(input.dataset.imageUpload, previewUrl);
            setPreviewImage(input.dataset.imageUpload, previewUrl);
            filename.textContent = file.name;
            if (removeCheckbox) removeCheckbox.checked = false;
        } else {
            const previousUrl = selectedImageUrls.get(input.dataset.imageUpload);
            if (previousUrl) URL.revokeObjectURL(previousUrl);
            selectedImageUrls.delete(input.dataset.imageUpload);
            const originalSource = currentImageSource(input.dataset.imageUpload);
            setPreviewImage(input.dataset.imageUpload, originalSource);
            filename.textContent = originalSource ? 'Current image saved' : 'No image selected';
        }
    };

    inputs.forEach((input) => {
        input.addEventListener('change', () => updateUpload(input));
        const zone = input.closest('[data-drop-zone]');
        const target = zone.querySelector('[data-drop-target]');

        target.addEventListener('dragover', (event) => {
            event.preventDefault();
            target.classList.add('border-cg-red');
        });
        target.addEventListener('dragleave', () => target.classList.remove('border-cg-red'));
        target.addEventListener('drop', (event) => {
            event.preventDefault();
            target.classList.remove('border-cg-red');
            const [file] = event.dataTransfer.files;
            if (!file) return;

            const transfer = new DataTransfer();
            transfer.items.add(file);
            input.files = transfer.files;
            updateUpload(input);
        });
    });

    form.querySelectorAll('[data-remove-image]').forEach((checkbox) => {
        checkbox.addEventListener('change', () => {
            const input = form.querySelector(`[data-image-upload="${checkbox.dataset.removeImage}"]`);
            if (checkbox.checked && !input.files.length) {
                setPreviewImage(checkbox.dataset.removeImage, '');
            } else if (!checkbox.checked && !input.files.length) {
                updateUpload(input);
            }
        });
    });

    let previewLocale = 'en';
    const previewKeys = new Set();
    form.querySelectorAll('[data-preview-text]').forEach((input) => {
        const key = input.dataset.previewText;
        previewKeys.add(key);
        input.addEventListener('input', () => updatePreviewText(key));
    });

    function updatePreviewText(key) {
        const inputsForKey = Array.from(form.querySelectorAll(`[data-preview-text="${key}"]`));
        const englishInput = inputsForKey.find((input) => input.dataset.previewLocale !== 'np');
        const nepaliInput = inputsForKey.find((input) => input.dataset.previewLocale === 'np');
        const localizedValue = previewLocale === 'np' ? nepaliInput?.value.trim() : '';
        const value = localizedValue || englishInput?.value.trim() || '';

        form.querySelectorAll(`[data-preview-value="${key}"]`).forEach((preview) => {
            preview.textContent = value;
            if (key === 'secondary') preview.hidden = !value;
        });
    }

    previewKeys.forEach((key) => updatePreviewText(key));

    form.querySelectorAll('[data-preview-mode-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const selectedMode = button.dataset.previewModeToggle;
            form.querySelectorAll('[data-preview-frame]').forEach((frame) => {
                frame.classList.toggle('hidden', frame.dataset.previewFrame !== selectedMode);
            });
            form.querySelectorAll('[data-preview-mode-toggle]').forEach((toggle) => {
                const isSelected = toggle === button;
                toggle.setAttribute('aria-pressed', String(isSelected));
                toggle.classList.toggle('bg-cg-red', isSelected);
                toggle.classList.toggle('text-white', isSelected);
                toggle.classList.toggle('text-cg-muted', !isSelected);
            });
        });
    });

    form.querySelectorAll('[data-preview-locale-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            previewLocale = button.dataset.previewLocaleToggle;
            form.querySelectorAll('[data-preview-locale-toggle]').forEach((toggle) => {
                const isSelected = toggle === button;
                toggle.setAttribute('aria-pressed', String(isSelected));
                toggle.classList.toggle('bg-cg-red', isSelected);
                toggle.classList.toggle('text-white', isSelected);
                toggle.classList.toggle('text-cg-muted', !isSelected);
            });
            previewKeys.forEach((key) => updatePreviewText(key));
        });
    });
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

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        mountCharts();
        mountHeroSlideForm();
        mountHeroSlideReordering();
    });
} else {
    mountCharts();
    mountHeroSlideForm();
    mountHeroSlideReordering();
}
