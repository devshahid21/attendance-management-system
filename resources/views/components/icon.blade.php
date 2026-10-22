@props(['name'=>'grid','size'=>20])
@php
$paths=[
'grid'=>'<rect x="3" y="3" width="7" height="7" rx="2"/><rect x="14" y="3" width="7" height="7" rx="2"/><rect x="3" y="14" width="7" height="7" rx="2"/><rect x="14" y="14" width="7" height="7" rx="2"/>',
'users'=>'<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M22 21v-2a4 4 0 0 0-3-3.87"/><circle cx="9" cy="7" r="4"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
'building'=>'<rect x="4" y="3" width="16" height="18" rx="2"/><path d="M9 21v-4h6v4M8 7h1m6 0h1M8 11h1m6 0h1"/>',
'clock'=>'<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
'calendar'=>'<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 11h18m-13 4h2m4 0h2"/>',
'chart'=>'<path d="M4 3v18h17M8 16v-5m5 5V6m5 10V9"/>',
'shield'=>'<path d="m12 3 8 4v6c0 5-8 9-8 9s-8-4-8-9V7l8-4Z"/><path d="m8 12 3 3 5-6"/>',
'user'=>'<circle cx="12" cy="8" r="4"/><path d="M4 21v-2a8 8 0 0 1 16 0v2"/>',
'arrow'=>'<path d="M5 12h14m-6-6 6 6-6 6"/>',
'plus'=>'<path d="M12 5v14M5 12h14"/>',
'check'=>'<path d="m5 12 4 4L19 6"/>',
'logout'=>'<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4m7 14 5-5-5-5m5 5H9"/>',
'menu'=>'<path d="M4 6h16M4 12h16M4 18h16"/>',
'lock'=>'<rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3m-4 5v2"/>',
'leaf'=>'<path d="M20 4c-8-2-16 1-16 8a7 7 0 0 0 7 7c7 0 10-8 9-15ZM4 20l10-10"/>',
'download'=>'<path d="M12 3v12m-5-5 5 5 5-5M4 17v4h16v-4"/>',
'chevron'=>'<path d="m9 5 7 7-7 7"/>',
'mail'=>'<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 6 9 7 9-7"/>',
];
@endphp
<svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" {{ $attributes }}>{!! $paths[$name]??$paths['grid'] !!}</svg>
