@props([
    'name',
    'class' => 'h-5 w-5',
])

@php
$paths = [
        'alert-triangle' => '<path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"/><path d="M12 9v4M12 17h.01"/>',
        'check' => '<path d="M4 12.5 9.5 18 20 6.5"/>',
        'dashboard' => '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>',
        'edit' => '<path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/>',
        'eye' => '<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>',
        'file-text' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6M8 13h8M8 17h8M8 9h2"/>',
        'info' => '<circle cx="12" cy="12" r="9"/><path d="M12 16v-4M12 8h.01"/>',
        'products' => '<path d="m12 3 8 4.5v9L12 21l-8-4.5v-9L12 3Z"/><path d="m4.5 7.5 7.5 4.25 7.5-4.25M12 11.75V21"/>',
        'suppliers' => '<path d="M3 6h11v11H3z"/><path d="M14 10h4l3 3v4h-7z"/><circle cx="7.5" cy="19" r="2"/><circle cx="17.5" cy="19" r="2"/>',
        'customers' => '<circle cx="9" cy="8" r="3"/><path d="M3 19c0-3 2.5-5 6-5s6 2 6 5"/><path d="M16 6.5a3 3 0 0 1 0 5.8"/><path d="M19 14c1.4.8 2 2.1 2 4"/>',
        'purchasing' => '<path d="M3 4h2l2 11h10l2-8H6"/><circle cx="9" cy="19" r="1.5"/><circle cx="17" cy="19" r="1.5"/>',
        'users' => '<circle cx="9" cy="8" r="3"/><circle cx="17" cy="9" r="2.5"/><path d="M3 20c0-3.2 2.5-5 6-5s6 1.8 6 5"/><path d="M14.5 15c3 .1 5.5 1.7 5.5 5"/>',
        'settings' => '<path d="M12 3v2M12 19v2M3 12h2M19 12h2M5.6 5.6 7 7M17 17l1.4 1.4M18.4 5.6 17 7M7 17l-1.4 1.4"/><circle cx="12" cy="12" r="4"/>',
        'sun' => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/>',
        'moon' => '<path d="M20.5 14.5A8.5 8.5 0 0 1 9.5 3.5 8.5 8.5 0 1 0 20.5 14.5Z"/>',
        'system' => '<rect x="3" y="4" width="18" height="13" rx="2"/><path d="M8 21h8M12 17v4"/>',
        'bell' => '<path d="M18 9a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M10 21h4"/>',
        'categories' => '<path d="M9.5 3H16v6.5L9 17l-6-6 6.5-8Z"/><circle cx="13.25" cy="6.25" r="1.1" fill="currentColor" stroke="none"/>',
        'receipt' => '<path d="M6 2h12v20l-2-1.2L14 22l-2-1.2L10 22l-2-1.2L6 22V2Z"/><path d="M9 7h6M9 11h6M9 15h4"/>',        'credit-card' => '<rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20M6 15h4"/>',
        'logout' => '<path d="M10 17l5-5-5-5"/><path d="M15 12H3"/><path d="M14 4h4a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-4"/>',
        'chevron-left' => '<path d="m15 18-6-6 6-6"/>',
        'chevron-right' => '<path d="m9 18 6-6-6-6"/>',
'menu' => '<path d="M4 6h16M4 12h16M4 18h16"/>',
        'plus' => '<path d="M12 5v14M5 12h14"/>',
        'shopping-cart' => '<circle cx="9" cy="20" r="1.5"/><circle cx="17" cy="20" r="1.5"/><path d="M3 3h2l2.6 12.4a2 2 0 0 0 2 1.6h7.7a2 2 0 0 0 2-1.6L21 7H6"/>',
        'trash-2' => '<path d="M3 6h18M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6M10 11v6M14 11v6"/>',
        'reports' => '<path d="M5 20V4"/><path d="M10 20v-9M15 20V7M20 20V2"/><path d="M3 20h18"/>',
        'ledger' => '<rect x="3" y="3" width="18" height="18" rx="2"/><path d="M7 8h10M7 12h10M7 16h6"/>',
        'user' => '<circle cx="12" cy="8" r="3"/><path d="M5 21c.8-4 3.1-6 7-6s6.2 2 7 6"/>',
        'x' => '<path d="M6 6l12 12M18 6 6 18"/>',
        'x-circle' => '<circle cx="12" cy="12" r="9"/><path d="m15 9-6 6M9 9l6 6"/>',
    ];

    $path = $paths[$name] ?? null;
@endphp

<svg
    xmlns="http://www.w3.org/2000/svg"
    viewBox="0 0 24 24"
    fill="none"
    stroke="currentColor"
    stroke-width="1.8"
    stroke-linecap="round"
    stroke-linejoin="round"
    class="{{ $class }}"
    aria-hidden="true"
>
    @if ($path)
        {!! $path !!}
    @endif
</svg>
