@props(['variant' => 'default', 'class' => ''])

@php
$variants = [
    'default' => 'bg-primary text-primary-foreground',
    'secondary' => 'bg-secondary text-secondary-foreground',
    'destructive' => 'bg-destructive text-destructive-foreground',
    'outline' => 'border border-input bg-background',
];
$variantClass = $variants[$variant] ?? $variants['default'];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 ' . $variantClass . ' ' . $class]) }}>
    {{ $slot }}
</span>

