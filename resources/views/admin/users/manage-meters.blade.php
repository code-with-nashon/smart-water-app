<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manage Meters for ') }} {{ $user->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Assign/Unassign Meters for {{ $user->name }} ({{ $user->email }})</h3>

                    {{-- Session messages for success/info --}}
                    @if (session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif
                    @if (session('info'))
                        <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline">{{ session('info') }}</span>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Section for Assigned Meters --}}
                        <div>
                            <h4 class="font-semibold text-gray-800 mb-2">Assigned Meters:</h4>
                            @if ($assignedMeters->isEmpty())
                                <p class="text-gray-600">No meters currently assigned to this user.</p>
                            @else
                                <ul class="list-disc list-inside space-y-1">
                                    @foreach ($assignedMeters as $meterId)
                                        <li class="flex items-center justify-between bg-gray-50 p-2 rounded-md">
                                            <span>{{ $meterId }}</span>
                                            <form action="{{ route('admin.users.unassign-meter', $user->id) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="meter_id" value="{{ $meterId }}">
                                                <button type="submit" class="text-red-600 hover:text-red-900 text-sm">Unassign</button>
                                            </form>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>

                        {{-- Section for Available Meters to Assign --}}
                        <div>
                            <h4 class="font-semibold text-gray-800 mb-2">Available Meters to Assign:</h4>
                            @php
                                $unassignedMeters = $allMeters->diff($assignedMeters);
                            @endphp

                            @if ($unassignedMeters->isEmpty())
                                <p class="text-gray-600">No unassigned meters available.</p>
                            @else
                                <ul class="list-disc list-inside space-y-1">
                                    @foreach ($unassignedMeters as $meterId)
                                        <li class="flex items-center justify-between bg-gray-50 p-2 rounded-md">
                                            <span>{{ $meterId }}</span>
                                            <form action="{{ route('admin.users.assign-meter', $user->id) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="meter_id" value="{{ $meterId }}">
                                                <button type="submit" class="text-green-600 hover:text-green-900 text-sm">Assign</button>
                                            </form>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>

                    <div class="mt-8">
                        <x-primary-button onclick="window.history.back()">
                            {{ __('Back to User List') }}
                        </x-primary-button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>