<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Admin Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Welcome, Admin!</h3>

                    {{-- Admin Leak Alert Section --}}
                    @if ($hasSystemLeak)
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <strong class="font-bold">System Alert: Potential Leak Detected!</strong>
                            <span class="block sm:inline">Unusually high consumption detected for meter(s): {{ implode(', ', $leakingMeters) }}. Investigation recommended.</span>
                        </div>
                    @endif
                    {{-- End Admin Leak Alert Section --}}

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div class="bg-blue-100 p-4 rounded-lg shadow">
                            <h4 class="text-md font-semibold text-blue-800">Total Registered Users</h4>
                            <p class="text-3xl font-bold text-blue-900">{{ $totalUsers }}</p>
                        </div>
                        <div class="bg-green-100 p-4 rounded-lg shadow">
                            <h4 class="text-md font-semibold text-green-800">Total Water Meters</h4>
                            <p class="text-3xl font-bold text-green-900">{{ $totalMeters }}</p>
                        </div>
                        <div class="bg-yellow-100 p-4 rounded-lg shadow">
                            <h4 class="text-md font-semibold text-yellow-800">Pending Approvals</h4>
                            <p class="text-3xl font-bold text-yellow-900">0</p>
                        </div>
                    </div>

                    <div class="mt-8">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Admin Actions</h3>
                        <div class="flex space-x-4 flex-wrap gap-y-4"> {{-- Added flex-wrap and gap-y for better layout on smaller screens --}}
                            <a href="{{ route('admin.users.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Manage Users
                            </a>
                            <a href="{{ route('admin.meters.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Manage Meters
                            </a>
                            <a href="{{ route('admin.reports.consumption') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Consumption Report
                            </a>
                            {{-- NEW: Link to Anomaly Report --}}
                            <a href="{{ route('admin.reports.anomaly') }}" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Anomaly Report
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>