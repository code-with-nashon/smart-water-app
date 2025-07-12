@component('mail::message')
# Water Consumption Alert

@if ($alertType === 'leak')
## 💧 Potential Leak Detected for Meter ID: {{ $meterId }}

We've detected unusually high water consumption for your meter **{{ $meterId }}** today.

* **Today's Consumption:** {{ $currentConsumption }} Liters
* **Yesterday's Consumption:** {{ $previousConsumption }} Liters

This pattern suggests a potential leak. Please check your plumbing and water fixtures as soon as possible to avoid further water loss and unexpected bills.

@elseif ($alertType === 'high_consumption')
## 📈 High Consumption Alert for Meter ID: {{ $meterId }}

Your water consumption for meter **{{ $meterId }}** has exceeded your set daily threshold.

* **Today's Consumption:** {{ $currentConsumption }} Liters
* **Your Threshold:** {{ $threshold }} Liters

This is an informational alert to help you manage your water usage. If this consumption is unexpected, you might want to investigate the cause.

@endif

Thanks,<br>
{{ config('app.name') }}
@endcomponent