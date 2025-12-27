/**
 * Peanut License Server - Analytics Charts
 */
(function($) {
    'use strict';

    // Color palette matching Peanut Suite
    const colors = {
        primary: '#0073aa',
        success: '#10b981',
        warning: '#f59e0b',
        error: '#ef4444',
        purple: '#8b5cf6',
        blue: '#3b82f6',
        cyan: '#06b6d4',
        pink: '#ec4899',
        gray: '#64748b'
    };

    const chartColors = [
        colors.primary,
        colors.success,
        colors.warning,
        colors.purple,
        colors.blue,
        colors.cyan,
        colors.pink,
        colors.error
    ];

    // Wait for DOM and data
    $(document).ready(function() {
        if (typeof peanutAnalyticsData === 'undefined') {
            return;
        }

        initLicensesChart();
        initValidationsChart();
        initTierChart();
        initProductChart();
        initVersionChart();
    });

    /**
     * Licenses timeline chart
     */
    function initLicensesChart() {
        const ctx = document.getElementById('licensesChart');
        if (!ctx || !peanutAnalyticsData.timeline) return;

        const data = peanutAnalyticsData.timeline;

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.map(d => d.date),
                datasets: [{
                    label: 'New Licenses',
                    data: data.map(d => d.count),
                    borderColor: colors.primary,
                    backgroundColor: hexToRgba(colors.primary, 0.1),
                    fill: true,
                    tension: 0.4,
                    pointRadius: 2,
                    pointHoverRadius: 5
                }, {
                    label: 'Cumulative',
                    data: data.map(d => d.cumulative),
                    borderColor: colors.success,
                    backgroundColor: 'transparent',
                    borderDash: [5, 5],
                    tension: 0.4,
                    pointRadius: 0,
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 15
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false }
                    },
                    y: {
                        beginAtZero: true,
                        position: 'left',
                        grid: { color: '#f1f5f9' }
                    },
                    y1: {
                        beginAtZero: true,
                        position: 'right',
                        grid: { display: false }
                    }
                }
            }
        });
    }

    /**
     * Validations timeline chart
     */
    function initValidationsChart() {
        const ctx = document.getElementById('validationsChart');
        if (!ctx || !peanutAnalyticsData.validations) return;

        const data = peanutAnalyticsData.validations;

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.map(d => d.date),
                datasets: [{
                    label: 'Successful',
                    data: data.map(d => d.success || 0),
                    backgroundColor: colors.success,
                    borderRadius: 4
                }, {
                    label: 'Failed',
                    data: data.map(d => d.failed || 0),
                    backgroundColor: colors.error,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 15
                        }
                    }
                },
                scales: {
                    x: {
                        stacked: true,
                        grid: { display: false }
                    },
                    y: {
                        stacked: true,
                        beginAtZero: true,
                        grid: { color: '#f1f5f9' }
                    }
                }
            }
        });
    }

    /**
     * Tier distribution doughnut chart
     */
    function initTierChart() {
        const ctx = document.getElementById('tierChart');
        if (!ctx || !peanutAnalyticsData.tierDistribution) return;

        const data = peanutAnalyticsData.tierDistribution;
        const tierColors = {
            free: colors.gray,
            pro: colors.primary,
            agency: colors.warning
        };

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: data.map(d => d.tier.charAt(0).toUpperCase() + d.tier.slice(1)),
                datasets: [{
                    data: data.map(d => d.count),
                    backgroundColor: data.map(d => tierColors[d.tier] || colors.gray),
                    borderWidth: 0,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                cutout: '65%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 15
                        }
                    }
                }
            }
        });
    }

    /**
     * Product distribution chart
     */
    function initProductChart() {
        const ctx = document.getElementById('productChart');
        if (!ctx || !peanutAnalyticsData.productDistribution) return;

        const data = peanutAnalyticsData.productDistribution;

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: data.map(d => d.product_name || 'Unknown'),
                datasets: [{
                    data: data.map(d => d.count),
                    backgroundColor: chartColors.slice(0, data.length),
                    borderWidth: 0,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                cutout: '65%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 15
                        }
                    }
                }
            }
        });
    }

    /**
     * Version adoption chart
     */
    function initVersionChart() {
        const ctx = document.getElementById('versionChart');
        if (!ctx || !peanutAnalyticsData.versionAdoption) return;

        const data = peanutAnalyticsData.versionAdoption;

        new Chart(ctx, {
            type: 'polarArea',
            data: {
                labels: data.map(d => 'v' + d.version),
                datasets: [{
                    data: data.map(d => d.count),
                    backgroundColor: chartColors.slice(0, data.length).map(c => hexToRgba(c, 0.7)),
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 15
                        }
                    }
                },
                scales: {
                    r: {
                        display: false
                    }
                }
            }
        });
    }

    /**
     * Convert hex color to rgba
     */
    function hexToRgba(hex, alpha) {
        const r = parseInt(hex.slice(1, 3), 16);
        const g = parseInt(hex.slice(3, 5), 16);
        const b = parseInt(hex.slice(5, 7), 16);
        return `rgba(${r}, ${g}, ${b}, ${alpha})`;
    }

})(jQuery);
