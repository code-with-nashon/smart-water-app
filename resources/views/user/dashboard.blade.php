<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('User Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Your Water Meters</h3>

                    {{-- Leak Alert Section for User Dashboard --}}
                    @foreach ($metersData as $meter)
                        @if ($meter['has_potential_leak'])
                            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                                <strong class="font-bold">Potential Leak Detected!</strong>
                                <span class="block sm:inline">Meter ID: {{ $meter['meter_id'] }} shows unusually high consumption today. Please check your plumbing.</span>
                            </div>
                        @endif
                    @endforeach
                    {{-- End Leak Alert Section --}}

                    {{-- High Consumption Alert Section for User Dashboard --}}
                    @foreach ($metersData as $meter)
                        @if ($meter['has_high_consumption'])
                            <div class="bg-orange-100 border border-orange-400 text-orange-700 px-4 py-3 rounded relative mb-4" role="alert">
                                <strong class="font-bold">High Consumption Alert!</strong>
                                <span class="block sm:inline">Meter ID: {{ $meter['meter_id'] }} has exceeded your daily consumption threshold of {{ Auth::user()->daily_consumption_alert_threshold }} Liters. Today's consumption: {{ $meter['current_daily_consumption'] }} Liters.</span>
                            </div>
                        @endif
                    @endforeach
                    {{-- End High Consumption Alert Section --}}

                    <div class="overflow-x-auto mt-4">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Meter ID
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Latest Reading (Liters)
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Latest Reading At
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Daily Consumption (Today)
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Daily Consumption (Yesterday)
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($metersData as $meter)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $meter['meter_id'] }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $meter['latest_consumption_liters'] }} L
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $meter['latest_reading_at'] }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $meter['current_daily_consumption'] }} L
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $meter['previous_daily_consumption'] }} L
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route('user.meter', $meter['meter_id']) }}" class="text-indigo-600 hover:text-indigo-900">View Details</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                            No meters assigned to your account yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>