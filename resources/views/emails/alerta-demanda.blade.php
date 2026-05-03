<x-mail::message>
# Alerta de Demandas

@if($atrasadas->isNotEmpty())
## 🔴 Atrasadas ({{ $atrasadas->count() }})

@foreach($atrasadas as $d)
- **[{{ $d->categoria }}]** {{ $d->titulo }} — *{{ $d->prazo_label }}*
@endforeach

@endif

@if($urgentes->isNotEmpty())
## 🟠 Urgentes ({{ $urgentes->count() }})

@foreach($urgentes as $d)
- **[{{ $d->categoria }}]** {{ $d->titulo }} — *{{ $d->prazo_label }}*
@endforeach

@endif

@if($proximas->isNotEmpty())
## 🟡 Vencendo em breve ({{ $proximas->count() }})

@foreach($proximas as $d)
- **[{{ $d->categoria }}]** {{ $d->titulo }} — *{{ $d->prazo_label }}*
@endforeach

@endif

<x-mail::button :url="config('app.url')">
Abrir Sistema
</x-mail::button>

{{ config('app.name') }}
</x-mail::message>
