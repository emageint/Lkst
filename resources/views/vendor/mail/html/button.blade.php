@props([
    'url',
    'color' => 'primary',
    'align' => 'center',
])
@php
    $background = match($color) {
        'success' => '#22c55e',
        'error' => '#ef4444',
        'primary' => '#3b82f6',
        default => '#3b82f6',
    };
@endphp
<table class="action" align="{{ $align }}" width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td align="{{ $align }}">
<table width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td align="{{ $align }}">
<table border="0" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td>
    <a href="{{ $url }}"
       target="_blank"
       rel="noopener"
       style="
     display: inline-block;
     padding: 12px 24px;
     background-color: {{ $background }};
     color: #ffffff;
     text-decoration: none;
     border-radius: 6px;
     font-weight: 600;
   ">
        {!! $slot !!}
    </a>
</td>
</tr>
</table>
</td>
</tr>
</table>
</td>
</tr>
</table>
