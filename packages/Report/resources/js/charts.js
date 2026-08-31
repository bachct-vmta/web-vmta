import {
    Chart,
    LineController,
    LineElement,
    PointElement,
    LinearScale,
    CategoryScale,
    Tooltip,
    Legend,
    Filler,
    DoughnutController,
    ArcElement,
} from 'chart.js';

Chart.register(
    LineController,
    LineElement,
    PointElement,
    LinearScale,
    CategoryScale,
    Tooltip,
    Legend,
    Filler,
    DoughnutController,
    ArcElement,
);

const payloadEl = document.getElementById('report-chart-data');
if (payloadEl) {
    const payload = JSON.parse(payloadEl.textContent);

    const shortDate = (iso) => iso.slice(5); // 'YYYY-MM-DD' → 'MM-DD'

    const lineOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    // restore full ISO date in tooltip (we strip year from x-tick labels for density)
                    title: (items) => items[0]?.dataset.fullLabels?.[items[0].dataIndex] ?? items[0]?.label,
                },
            },
        },
        scales: {
            x: { ticks: { maxRotation: 0, autoSkip: true, maxTicksLimit: 10 } },
            y: { beginAtZero: true, ticks: { precision: 0 } },
        },
    };

    const mountLine = (id, series, color) => {
        const el = document.getElementById(id);
        if (!el) return;
        new Chart(el, {
            type: 'line',
            data: {
                labels: series.labels.map(shortDate),
                datasets: [
                    {
                        label: id,
                        data: series.data,
                        fullLabels: series.labels,
                        borderColor: color,
                        backgroundColor: `${color}33`,
                        fill: true,
                        tension: 0.3,
                        pointRadius: 2,
                    },
                ],
            },
            options: lineOptions,
        });
    };

    mountLine('chart-pageview', payload.pageview, '#0b7f7c');
    mountLine('chart-lead', payload.lead, '#059669');
    mountLine('chart-chatbot', payload.chatbot, '#7c3aed');

    const breakdownEl = document.getElementById('chart-lead-breakdown');
    if (breakdownEl && payload.leadBreakdown.data.length > 0) {
        const palette = ['#0b7f7c', '#f59e0b', '#ef4444', '#3b82f6', '#a855f7', '#10b981'];
        new Chart(breakdownEl, {
            type: 'doughnut',
            data: {
                labels: payload.leadBreakdown.labels,
                datasets: [
                    {
                        data: payload.leadBreakdown.data,
                        backgroundColor: payload.leadBreakdown.labels.map((_, i) => palette[i % palette.length]),
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } },
            },
        });
    }
}
