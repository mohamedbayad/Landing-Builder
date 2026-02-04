import Chart from 'chart.js/auto';

document.addEventListener('DOMContentLoaded', () => {
    const trafficCtx = document.getElementById('trafficChart');
    const sourcesCtx = document.getElementById('trafficSourcesChart');

    if (!trafficCtx) return;

    const chartData = window.dashboardData;

    // 1. Multi-line Traffic Overview Chart (Visits + Leads)
    new Chart(trafficCtx, {
        type: 'line',
        data: {
            labels: chartData.chartLabels,
            datasets: [
                {
                    label: 'Visits',
                    data: chartData.visits,
                    borderColor: 'rgb(99, 102, 241)', // Indigo 500
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    borderWidth: 2.5,
                    tension: 0.4,
                    fill: true,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: 'rgb(99, 102, 241)',
                    pointBorderWidth: 2,
                },
                {
                    label: 'Leads',
                    data: chartData.leads,
                    borderColor: 'rgb(16, 185, 129)', // Emerald 500
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    borderWidth: 2.5,
                    tension: 0.4,
                    fill: true,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: 'rgb(16, 185, 129)',
                    pointBorderWidth: 2,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    align: 'end',
                    labels: {
                        usePointStyle: true,
                        pointStyle: 'circle',
                        padding: 20,
                        font: { size: 12, weight: '500' },
                        color: document.documentElement.classList.contains('dark') ? '#9CA3AF' : '#6B7280',
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(17, 24, 39, 0.95)',
                    titleColor: '#F9FAFB',
                    bodyColor: '#D1D5DB',
                    borderColor: 'rgba(75, 85, 99, 0.3)',
                    borderWidth: 1,
                    cornerRadius: 8,
                    padding: 12,
                    displayColors: true,
                    callbacks: {
                        label: function (context) {
                            return `${context.dataset.label}: ${context.raw.toLocaleString()}`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: document.documentElement.classList.contains('dark') ? 'rgba(75, 85, 99, 0.3)' : 'rgba(229, 231, 235, 0.8)',
                        borderDash: [2, 4],
                    },
                    ticks: {
                        color: document.documentElement.classList.contains('dark') ? '#9CA3AF' : '#6B7280',
                        font: { size: 11 },
                        callback: function (value) {
                            return value.toLocaleString();
                        }
                    }
                },
                x: {
                    grid: { display: false },
                    ticks: {
                        color: document.documentElement.classList.contains('dark') ? '#9CA3AF' : '#6B7280',
                        font: { size: 11 },
                    }
                }
            }
        }
    });

    // 2. Traffic Sources Donut Chart
    if (sourcesCtx && chartData.trafficSources) {
        new Chart(sourcesCtx, {
            type: 'doughnut',
            data: {
                labels: ['Direct', 'Social', 'Search', 'Referral'],
                datasets: [{
                    data: [
                        chartData.trafficSources.Direct,
                        chartData.trafficSources.Social,
                        chartData.trafficSources.Search,
                        chartData.trafficSources.Referral,
                    ],
                    backgroundColor: [
                        'rgb(99, 102, 241)',  // Indigo
                        'rgb(236, 72, 153)',  // Pink
                        'rgb(16, 185, 129)',  // Emerald
                        'rgb(245, 158, 11)',  // Amber
                    ],
                    borderWidth: 0,
                    hoverOffset: 8,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                cutout: '70%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(17, 24, 39, 0.95)',
                        titleColor: '#F9FAFB',
                        bodyColor: '#D1D5DB',
                        borderColor: 'rgba(75, 85, 99, 0.3)',
                        borderWidth: 1,
                        cornerRadius: 8,
                        padding: 12,
                        callbacks: {
                            label: function (context) {
                                return `${context.label}: ${context.raw}%`;
                            }
                        }
                    }
                }
            }
        });
    }
});
