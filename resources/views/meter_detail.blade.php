<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Meter Details: ') . $meterId }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Consumption Data for Meter ID: {{ $meterId }}</h3>

                    {{-- Date Range Selection --}}
                    <div class="mb-4 flex items-center space-x-4">
                        <label for="dateRange" class="block text-sm font-medium text-gray-700">View Data For:</label>
                        <select id="dateRange" name="dateRange" class="mt-1 block w-auto pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                            <option value="7">Last 7 Days</option>
                            <option value="30" selected>Last 30 Days</option>
                            <option value="90">Last 90 Days</option>
                            <option value="365">Last 365 Days</option>
                        </select>
                    </div>

                    <div class="mb-6">
                        <canvas id="consumptionChart"></canvas>
                    </div>

                    <div class="mt-4 flex space-x-4"> {{-- Added flex and space-x-4 for button layout --}}
                        <a href="{{ route('user.dashboard') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-300 focus:bg-gray-300 active:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            {{ __('Back to Dashboard') }}
                        </a>

                        {{-- NEW: Export Data Button --}}
                        <a href="{{ route('user.meter.export-data', ['meterId' => $meterId, 'days' => request('days', 30)]) }}" id="exportDataButton" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            {{ __('Export Data (CSV)') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const meterId = "{{ $meterId }}";
            const ctx = document.getElementById('consumptionChart').getContext('2d');
            const dateRangeSelect = document.getElementById('dateRange');
            const exportDataButton = document.getElementById('exportDataButton'); // Get the export button
            let consumptionChart;

            function fetchAndRenderChart(days = 30) {
                fetch(`/user/meter/${meterId}/consumption-data?days=${days}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) {
                            console.error('Error fetching data:', data.error);
                            alert('Error fetching consumption data: ' + data.error);
                            return;
                        }

                        if (consumptionChart) {
                            consumptionChart.destroy();
                        }

                        // Prepare datasets
                        const datasets = [
                            {
                                label: 'Daily Consumption (Liters)',
                                data: data.data,
                                backgroundColor: 'rgba(75, 192, 192, 0.6)',
                                borderColor: 'rgba(75, 192, 192, 1)',
                                borderWidth: 1,
                                type: 'bar'
                            }
                        ];

                        // Add predicted data if available
                        if (data.predictedLabels && data.predictedData) {
                            // Combine historical and predicted labels for the x-axis
                            const combinedLabels = data.labels.concat(data.predictedLabels);

                            // Create a full data array for prediction, filling historical gaps with null
                            const fullPredictedData = Array(data.labels.length).fill(null).concat(data.predictedData);

                            datasets.push({
                                label: 'Predicted Consumption (Liters)',
                                data: fullPredictedData,
                                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                                borderColor: 'rgba(255, 99, 132, 1)',
                                borderWidth: 2,
                                borderDash: [5, 5],
                                type: 'line',
                                fill: false,
                                pointRadius: 3,
                                pointHoverRadius: 5,
                            });
                            data.labels = combinedLabels; // Update labels for the chart
                        }


                        consumptionChart = new Chart(ctx, {
                            data: {
                                labels: data.labels,
                                datasets: datasets
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        title: {
                                            display: true,
                                            text: 'Liters'
                                        }
                                    },
                                    x: {
                                        title: {
                                            display: true,
                                            text: 'Date'
                                        }
                                    }
                                },
                                plugins: {
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                let label = context.dataset.label || '';
                                                if (label) {
                                                    label += ': ';
                                                }
                                                if (context.parsed.y !== null) {
                                                    label += context.parsed.y + ' L';
                                                }
                                                return label;
                                            }
                                        }
                                    }
                                }
                            }
                        });

                        // Update the export button's href with the current date range
                        exportDataButton.href = `/user/meter/${meterId}/export-data?days=${days}`;
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while loading chart data.');
                    });
            }

            // Initial chart load
            fetchAndRenderChart(dateRangeSelect.value);

            // Add event listener for date range change
            dateRangeSelect.addEventListener('change', function() {
                fetchAndRenderChart(this.value);
            });
        });
    </script>
    @endpush
</x-app-layout>