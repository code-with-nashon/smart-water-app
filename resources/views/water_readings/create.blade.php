<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Submit New Water Reading') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if (session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <strong class="font-bold">Success!</strong>
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('user.readings.store') }}">
                        @csrf

                        <div>
                            <x-input-label for="meter_id" :value="__('Meter ID')" />
                            <x-text-input id="meter_id" class="block mt-1 w-full" type="text" name="meter_id" :value="old('meter_id')" required autofocus />
                            <x-input-error :messages="$errors->get('meter_id')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="consumption_liters" :value="__('Consumption (Liters)')" />
                            <x-text-input id="consumption_liters" class="block mt-1 w-full" type="number" step="0.01" name="consumption_liters" :value="old('consumption_liters')" required />
                            <x-input-error :messages="$errors->get('consumption_liters')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="reading_at" :value="__('Reading Date and Time')" />
                            <x-text-input id="reading_at" class="block mt-1 w-full" type="datetime-local" name="reading_at" :value="old('reading_at')" required />
                            <x-input-error :messages="$errors->get('reading_at')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button class="ms-4">
                                {{ __('Submit Reading') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>