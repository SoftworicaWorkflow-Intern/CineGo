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
        mountPosterDropzone();
    });
} else {
    mountCharts();
    mountPosterDropzone();
}
