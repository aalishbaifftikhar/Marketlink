@props([
    'lat',
    'lng',
    'label' => 'Get directions',
])

<a href="https://www.openstreetmap.org/directions?engine=fossgis_osrm_car&route=;{{ (float) $lat }},{{ (float) $lng }}"
   target="_blank" rel="noopener noreferrer"
   {{ $attributes->merge(['class' => 'btn-secondary btn-sm']) }}>
    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498l4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.331.165-.694.165-1.026 0L9.5 3.68a1.14 1.14 0 00-1.026 0L3.6 6.116C3.22 6.306 2.98 6.696 2.98 7.122v10.937c0 .836.88 1.38 1.628 1.006l3.869-1.934c.331-.165.694-.165 1.026 0l4.877 2.438c.331.165.694.165 1.026 0z"/></svg>
    {{ $label }}
</a>
