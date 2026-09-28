@props([
    'id' => 'map-' . uniqid(),
    'height' => 'h-80',
    'center' => [40.7128, -74.0060],
    'zoom' => 12,
    'markers' => [],
    'draggable' => false,
    'draggablePin' => null,
    'latInput' => null,
    'lngInput' => null,
])

<div id="{{ $id }}" {{ $attributes->merge(['class' => "w-full rounded-2xl overflow-hidden border border-stone-200 dark:border-leaf-800 shadow-soft $height"]) }}></div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const el = document.getElementById(@json($id));
    if (!el || typeof window.initLeafletMap !== 'function') return;

    const markers = @json($markers);
    const handle = window.initLeafletMap(el, {
        center: @json($center),
        zoom: {{ $zoom }},
        markers: markers,
        draggablePin: {{ $draggablePin ? 'true' : 'false' }} ? (@json($draggablePin ?? [])) : null,
    });

    @if ($draggable && $latInput && $lngInput)
    const latEl = document.querySelector(@json($latInput));
    const lngEl = document.querySelector(@json($lngInput));

    el.addEventListener('pin-moved', (e) => {
        if (latEl) latEl.value = e.detail.lat.toFixed(7);
        if (lngEl) lngEl.value = e.detail.lng.toFixed(7);
    });

    // Typing coordinates moves the pin too (silent, so the fields are not reformatted mid-edit).
    const syncFromInputs = () => {
        const lat = parseFloat(latEl && latEl.value);
        const lng = parseFloat(lngEl && lngEl.value);
        if (!Number.isFinite(lat) || !Number.isFinite(lng)) return;
        if (lat < -90 || lat > 90 || lng < -180 || lng > 180) return;
        if (handle && handle.setPin) handle.setPin(L.latLng(lat, lng));
    };
    if (latEl) latEl.addEventListener('input', syncFromInputs);
    if (lngEl) lngEl.addEventListener('input', syncFromInputs);
    @endif
});
</script>
@endpush
