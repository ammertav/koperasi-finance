$(document).ready(function () {
    const canvas = $('#cashChart');

    if (!canvas.length) {
        return;
    }

    new Chart(canvas[0], {
        type: 'bar',
        data: {
            labels: canvas.data('labels'),
            datasets: [
                { label: 'Kas Brankas', data: canvas.data('vault'), backgroundColor: '#0d9488' },
                { label: 'Kas Teller', data: canvas.data('teller'), backgroundColor: '#5eead4' },
            ],
        },
        options: {
            plugins: {
                legend: { position: 'bottom' },
                tooltip: {
                    callbacks: {
                        label: (context) => `${context.dataset.label}: Rp ${context.parsed.y.toLocaleString('id-ID')}`,
                    },
                },
            },
            scales: {
                x: { stacked: true },
                y: {
                    stacked: true,
                    ticks: { callback: (value) => 'Rp ' + value.toLocaleString('id-ID') },
                },
            },
        },
    });
});
