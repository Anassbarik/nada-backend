@props(['class' => '', 'responsive' => false])

<div @if($responsive) class="overflow-x-auto" @endif>
    <table {{ $attributes->merge(['class' => 'w-full caption-bottom text-sm ' . $class]) }}>
        {{ $slot }}
    </table>
</div>

