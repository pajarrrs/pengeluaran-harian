<x-mail::message>
# 📊 Ringkasan Harian

{{ $summary }}

@if ($budgetAlerts)
---

{{ $budgetAlerts }}
@endif

<x-mail::button :url="config('app.url')">
Buka Dashboard
</x-mail::button>
</x-mail::message>
