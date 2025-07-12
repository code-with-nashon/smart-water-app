<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Overall Consumption Report') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Water Consumption Overview</h3>

                    {{-- Date Range Filter Form --}}
                    <form method="GET" action="{{ route('admin.reports.consumption') }}" class="mb-6 flex items-end space-x-4">
                        <div>
                            <x-input-label for="start_date" :value="__('Start Date')" />
                            <x-text-input id="start_date" name="start_date" type="date" class="mt-1 block w-full" value="{{ $startDate->format('Y-m-d') }}" />
                        </div>
                        <div>
                            <x-input-label for="end_date" :value="__('End Date')" />
                            <x-text-input id="end_date" name="end_date" type="date" class="mt-1 block w-full" value="{{ $endDate->format('Y-m-d') }}" />
                        </div>
                        <x-primary-button>{{ __('Filter') }}</x-primary-button>
                    </form>

                    <div class="mb-6">
                        <h4 class="text-md font-semibold text-gray-800 mb-2">Daily System-Wide Consumption ({{ $startDate->format('Y-m-d') }} to {{ $endDate->format('Y-m-d') }})</h4>
                        {{-- NEW: Added h-96 and w-full classes to control chart size --}}
                        <div class="relative h-96 w-full">
                            <canvas id="overallConsumptionChart"></canvas>
                        </div>
                    </div>

                    <div class="mt-8">
                        <h4 class="text-md font-semibold text-gray-800 mb-2">Consumption by Meter ({{ $startDate->format('Y-m-d') }} to {{ $endDate->format('Y-m-d') }})</h4>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Meter ID
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Total Consumption (Liters)
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse ($meterConsumptionData as $meter)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $meter->meter_id }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ number_format($meter->total_consumption, 2) }} L
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                                No consumption data available for this period.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-300 focus:bg-gray-300 active:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            {{ __('Back to Admin Dashboard') }}
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
            const ctx = document.getElementById('overallConsumptionChart').getContext('2d');
            const labels = @json($labels);
            const data = @json($data);

            new Chart(ctx, {
                type: 'line', // Line chart for overall consumption over time
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Total Daily Consumption (Liters)',
                        data: data,
                        backgroundColor: 'rgba(54, 162, 235, 0.6)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 2,
                        fill: false,
                        tension: 0.1 // Smoothen the line
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false, // Ensure this is false for custom height
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
        });
    </script>
    @endpush
</x-app-layout>