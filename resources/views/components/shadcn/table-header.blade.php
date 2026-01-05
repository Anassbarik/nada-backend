@props(['class' => ''])

<thead {{ $attributes->merge(['class' => '[&_tr]:border-b ' . $class]) }}>
    {{ $slot }}
</thead>

