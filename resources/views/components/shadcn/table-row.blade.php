@props(['class' => '', 'hover' => false])

<tr {{ $attributes->merge(['class' => 'border-b transition-colors ' . ($hover ? 'hover:bg-muted/50' : '') . ' ' . $class]) }}>
    {{ $slot }}
</tr>

