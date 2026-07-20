document.addEventListener('DOMContentLoaded', () => {
    const chartEl = document.getElementById('attendanceChart');
    if (!chartEl) return;

    const rawData = JSON.parse(chartEl.dataset.chartData || '[]');

    const categories = rawData.map(d => d.label);
    const hadirData = rawData.map(d => d.hadir);
    const terlambatData = rawData.map(d => d.terlambat);
    const izinData = rawData.map(d => d.izin);
    const absenData = rawData.map(d => d.absen);

    const isDarkMode = () => {
        return document.body.classList.contains('dark-theme') || 
               document.documentElement.getAttribute('data-theme') === 'dark';
    };

    const getThemeColors = () => {
        const dark = isDarkMode();
        return {
            textColor: dark ? '#cbd5e1' : '#475569',
            gridColor: dark ? 'rgba(255, 255, 255, 0.08)' : 'rgba(0, 0, 0, 0.06)'
        };
    };

    let theme = getThemeColors();

    const options = {
        series: [
            {
                name: 'Hadir',
                data: hadirData,
                color: '#10b981'
            },
            {
                name: 'Terlambat',
                data: terlambatData,
                color: '#f59e0b'
            },
            {
                name: 'Izin / Sakit',
                data: izinData,
                color: '#3b82f6'
            },
            {
                name: 'Tanpa Keterangan',
                data: absenData,
                color: '#ef4444'
            }
        ],
        chart: {
            type: 'bar',
            height: '100%',
            stacked: true,
            toolbar: {
                show: false
            },
            background: 'transparent',
            foreColor: theme.textColor,
            fontFamily: 'Outfit, sans-serif'
        },
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '45%',
                borderRadius: 4
            }
        },
        dataLabels: {
            enabled: false
        },
        xaxis: {
            categories: categories,
            axisBorder: {
                show: false
            },
            axisTicks: {
                show: false
            },
            labels: {
                style: {
                    colors: theme.textColor,
                    fontSize: '12px'
                }
            }
        },
        yaxis: {
            labels: {
                style: {
                    colors: theme.textColor,
                    fontSize: '12px'
                }
            }
        },
        grid: {
            borderColor: theme.gridColor,
            strokeDashArray: 4,
            xaxis: {
                lines: {
                    show: false
                }
            },
            yaxis: {
                lines: {
                    show: true
                }
            }
        },
        legend: {
            position: 'top',
            horizontalAlign: 'right',
            offsetY: -10,
            labels: {
                colors: theme.textColor
            },
            markers: {
                radius: 12
            }
        },
        fill: {
            opacity: 1
        },
        tooltip: {
            theme: isDarkMode() ? 'dark' : 'light',
            y: {
                formatter: function (val) {
                    return val + " orang";
                }
            }
        },
        responsive: [
            {
                breakpoint: 1025,
                options: {
                    legend: {
                        position: 'bottom',
                        horizontalAlign: 'center',
                        offsetY: 0
                    }
                }
            }
        ]
    };

    const chart = new ApexCharts(chartEl, options);
    chart.render();

    const updateChartTheme = () => {
        const nextTheme = getThemeColors();
        chart.updateOptions({
            chart: {
                foreColor: nextTheme.textColor
            },
            xaxis: {
                labels: {
                    style: {
                        colors: nextTheme.textColor
                    }
                }
            },
            yaxis: {
                labels: {
                    style: {
                        colors: nextTheme.textColor
                    }
                }
            },
            grid: {
                borderColor: nextTheme.gridColor
            },
            legend: {
                labels: {
                    colors: nextTheme.textColor
                }
            },
            tooltip: {
                theme: isDarkMode() ? 'dark' : 'light'
            }
        });
    };

    const observer = new MutationObserver(updateChartTheme);
    observer.observe(document.documentElement, { attributes: true, attributeFilter: ['data-theme'] });
    observer.observe(document.body, { attributes: true, attributeFilter: ['class'] });
});
