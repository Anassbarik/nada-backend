<div class="flex items-center space-x-2">
    @php
        $currentQuery = request()->query();
        // Remove existing lang parameter if present
        unset($currentQuery['lang']);
        $enQuery = array_merge($currentQuery, ['lang' => 'en']);
        $frQuery = array_merge($currentQuery, ['lang' => 'fr']);
        $baseUrl = request()->url();
        $enUrl = $baseUrl . '?' . http_build_query($enQuery);
        $frUrl = $baseUrl . '?' . http_build_query($frQuery);
    @endphp
    <a href="{{ $enUrl }}" 
       class="px-3 py-1 text-sm rounded {{ app()->getLocale() == 'en' ? 'bg-blue-500 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }} rounded hover:bg-blue-400 transition-colors">
        🇺🇸 EN
    </a>
    <a href="{{ $frUrl }}" 
       class="px-3 py-1 text-sm rounded {{ app()->getLocale() == 'fr' ? 'bg-blue-500 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }} rounded hover:bg-blue-400 transition-colors">
        🇫🇷 FR
    </a>
</div>
