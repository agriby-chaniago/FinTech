<x-app-layout>
    <div class="fin-surface-card p-6 md:p-8 space-y-6 md:space-y-8">

        <!-- Grafik Batang -->
        <div class="fin-surface-panel p-4 md:p-5">
            <div id="bar-chart"></div>
        </div>

        <!-- Grafik Garis (Saldo) -->
        <div class="fin-surface-panel p-4 md:p-5">
            <div id="line-chart"></div>
        </div>

        <!-- Grafik Donut (Komposisi Kategori) -->
        <div class="fin-surface-panel p-4 md:p-5">
            <div id="donut-chart"></div>
        </div>
    </div>

    <!-- ApexCharts Data -->
    <script id="stats-data" type="application/json">
        {!! json_encode([
            'income' => $income,
            'expense' => $expense,
            'days' => $days,
            'saldo' => $saldo,
            'kategoriValues' => $kategoriValues,
            'kategoriLabels' => $kategoriLabels,
        ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}
    </script>
    <script>
        const statsPayload = (() => {
            const fallback = {
                income: [],
                expense: [],
                days: [],
                saldo: [],
                kategoriValues: [],
                kategoriLabels: []
            };

            const payloadElement = document.getElementById('stats-data');

            if (!payloadElement) {
                return fallback;
            }

            try {
                const parsedPayload = JSON.parse(payloadElement.textContent);

                return {
                    ...fallback,
                    ...parsedPayload
                };
            } catch {
                return fallback;
            }
        })();

        const readThemeColor = (name, fallback) => {
            const value = getComputedStyle(document.documentElement)
                .getPropertyValue(name)
                .trim();

            return value || fallback;
        };

        const formatCurrency = (value) => {
            const numericValue = Number(value ?? 0);

            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                maximumFractionDigits: 0
            }).format(Number.isFinite(numericValue) ? numericValue : 0);
        };

        const formatCompactRupiah = (value) => {
            const numericValue = Number(value ?? 0);

            if (!Number.isFinite(numericValue)) {
                return 'Rp0';
            }

            const sign = numericValue < 0 ? '-' : '';
            const absoluteValue = Math.abs(numericValue);

            if (absoluteValue >= 1_000_000_000) {
                return `${sign}Rp${(absoluteValue / 1_000_000_000).toFixed(1).replace('.0', '')}M`;
            }

            if (absoluteValue >= 1_000_000) {
                return `${sign}Rp${(absoluteValue / 1_000_000).toFixed(1).replace('.0', '')}jt`;
            }

            if (absoluteValue >= 1_000) {
                return `${sign}Rp${(absoluteValue / 1_000).toFixed(1).replace('.0', '')}rb`;
            }

            return `${sign}Rp${absoluteValue.toLocaleString('id-ID')}`;
        };

        const statsChartTheme = {
            text: readThemeColor('--ctp-text', '#cdd6f4'),
            muted: readThemeColor('--ctp-overlay1', '#7f849c'),
            grid: readThemeColor('--ctp-surface1', '#45475a'),
            income: readThemeColor('--ctp-blue', '#89b4fa'),
            expense: readThemeColor('--ctp-red', '#f38ba8'),
            balance: readThemeColor('--ctp-green', '#a6e3a1'),
            donut: [
                readThemeColor('--ctp-yellow', '#f9e2af'),
                readThemeColor('--ctp-peach', '#fab387'),
                readThemeColor('--ctp-teal', '#94e2d5'),
                readThemeColor('--ctp-blue', '#89b4fa'),
                readThemeColor('--ctp-mauve', '#cba6f7')
            ]
        };

        const baseNoData = {
            text: 'Data belum tersedia',
            align: 'center',
            verticalAlign: 'middle',
            style: {
                color: statsChartTheme.muted,
                fontSize: '13px'
            }
        };

        const baseGrid = {
            borderColor: statsChartTheme.grid,
            strokeDashArray: 4,
            padding: {
                left: 8,
                right: 8
            }
        };

        const baseTooltip = {
            theme: 'dark',
            y: {
                formatter: (value) => formatCurrency(value)
            }
        };

        const barOptions = {
            chart: {
                type: 'bar',
                height: 340,
                toolbar: {
                    show: false
                },
                zoom: {
                    enabled: false
                },
                foreColor: statsChartTheme.text
            },
            noData: baseNoData,
            series: [
                {
                    name: 'Pemasukan',
                    data: statsPayload.income
                },
                {
                    name: 'Pengeluaran',
                    data: statsPayload.expense
                }
            ],
            colors: [statsChartTheme.income, statsChartTheme.expense],
            plotOptions: {
                bar: {
                    borderRadius: 8,
                    columnWidth: '44%'
                }
            },
            xaxis: {
                categories: statsPayload.days,
                labels: {
                    style: {
                        colors: statsChartTheme.muted,
                        fontSize: '12px'
                    },
                    rotate: -20,
                    trim: true
                }
            },
            yaxis: {
                labels: {
                    style: {
                        colors: statsChartTheme.muted,
                        fontSize: '12px'
                    },
                    formatter: (value) => formatCompactRupiah(value)
                }
            },
            title: {
                text: 'Pemasukan vs Pengeluaran (7 Hari Terakhir)',
                align: 'left',
                style: { color: statsChartTheme.text }
            },
            legend: {
                position: 'top',
                horizontalAlign: 'right',
                labels: { colors: statsChartTheme.text }
            },
            grid: baseGrid,
            tooltip: baseTooltip,
            dataLabels: {
                enabled: false
            },
            responsive: [
                {
                    breakpoint: 768,
                    options: {
                        chart: {
                            height: 320
                        },
                        legend: {
                            position: 'bottom',
                            horizontalAlign: 'center'
                        }
                    }
                }
            ]
        };

        const lineOptions = {
            chart: {
                type: 'area',
                height: 340,
                toolbar: {
                    show: false
                },
                zoom: {
                    enabled: false
                },
                foreColor: statsChartTheme.text
            },
            noData: baseNoData,
            series: [{
                name: 'Saldo',
                data: statsPayload.saldo
            }],
            colors: [statsChartTheme.balance],
            stroke: {
                curve: 'smooth',
                width: 3
            },
            markers: {
                size: 4,
                strokeWidth: 0,
                hover: {
                    size: 6
                }
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.32,
                    opacityTo: 0.04,
                    stops: [0, 100]
                }
            },
            xaxis: {
                categories: statsPayload.days,
                labels: {
                    style: {
                        colors: statsChartTheme.muted,
                        fontSize: '12px'
                    },
                    rotate: -20,
                    trim: true
                }
            },
            yaxis: {
                labels: {
                    style: {
                        colors: statsChartTheme.muted,
                        fontSize: '12px'
                    },
                    formatter: (value) => formatCompactRupiah(value)
                }
            },
            title: {
                text: 'Saldo Harian',
                align: 'left',
                style: { color: statsChartTheme.text }
            },
            grid: baseGrid,
            tooltip: baseTooltip,
            legend: {
                show: false
            }
        };

        const donutOptions = {
            chart: {
                type: 'donut',
                height: 340,
                toolbar: {
                    show: false
                },
                foreColor: statsChartTheme.text
            },
            noData: baseNoData,
            series: statsPayload.kategoriValues,
            labels: statsPayload.kategoriLabels,
            colors: statsChartTheme.donut,
            dataLabels: {
                enabled: false
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '64%',
                        labels: {
                            show: true,
                            value: {
                                color: statsChartTheme.text,
                                formatter: (value) => formatCurrency(value)
                            },
                            total: {
                                show: true,
                                label: 'Total',
                                color: statsChartTheme.muted,
                                formatter: (w) => {
                                    const total = w.globals.seriesTotals.reduce((sum, item) => sum + item, 0);

                                    return formatCurrency(total);
                                }
                            }
                        }
                    }
                }
            },
            title: {
                text: 'Komposisi Jenis Transaksi',
                align: 'left',
                style: { color: statsChartTheme.text }
            },
            legend: {
                position: 'bottom',
                labels: { colors: statsChartTheme.text }
            },
            tooltip: baseTooltip,
            responsive: [
                {
                    breakpoint: 768,
                    options: {
                        chart: {
                            height: 320
                        },
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            ]
        };

        const barElement = document.querySelector('#bar-chart');
        const lineElement = document.querySelector('#line-chart');
        const donutElement = document.querySelector('#donut-chart');

        if (barElement) {
            new ApexCharts(barElement, barOptions).render();
        }

        if (lineElement) {
            new ApexCharts(lineElement, lineOptions).render();
        }

        if (donutElement) {
            new ApexCharts(donutElement, donutOptions).render();
        }
    </script>
</x-app-layout>
