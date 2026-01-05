<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Contenu des Pages') }}: {{ $event->name }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class="mb-4">
                <a href="{{ route('admin.events.index') }}" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">← Retour aux Événements</a>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium mb-6">{{ $event->name }} → Contenu des Pages</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <a href="{{ route('admin.events.content.edit', [$event, 'conditions']) }}" class="block p-6 border-2 border-gray-300 dark:border-gray-600 rounded-lg hover:border-blue-500 dark:hover:border-blue-400 transition-colors">
                            <div class="text-3xl mb-2">📄</div>
                            <h4 class="text-lg font-semibold mb-2">Conditions de Réservation</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                @if(isset($contents['conditions']))
                                    {{ count($contents['conditions']->sections ?? []) }} section(s)
                                @else
                                    Non créé
                                @endif
                            </p>
                            <span class="text-blue-600 dark:text-blue-400 text-sm mt-2 inline-block">Éditer la page →</span>
                        </a>

                        <a href="{{ route('admin.events.content.edit', [$event, 'informations']) }}" class="block p-6 border-2 border-gray-300 dark:border-gray-600 rounded-lg hover:border-blue-500 dark:hover:border-blue-400 transition-colors">
                            <div class="text-3xl mb-2">📋</div>
                            <h4 class="text-lg font-semibold mb-2">Informations Générales</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                @if(isset($contents['informations']))
                                    {{ count($contents['informations']->sections ?? []) }} section(s)
                                @else
                                    Non créé
                                @endif
                            </p>
                            <span class="text-blue-600 dark:text-blue-400 text-sm mt-2 inline-block">Éditer la page →</span>
                        </a>

                        <a href="{{ route('admin.events.content.edit', [$event, 'faq']) }}" class="block p-6 border-2 border-gray-300 dark:border-gray-600 rounded-lg hover:border-blue-500 dark:hover:border-blue-400 transition-colors">
                            <div class="text-3xl mb-2">❓</div>
                            <h4 class="text-lg font-semibold mb-2">FAQ</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                @if(isset($contents['faq']))
                                    {{ count($contents['faq']->sections ?? []) }} section(s)
                                @else
                                    Non créé
                                @endif
                            </p>
                            <span class="text-blue-600 dark:text-blue-400 text-sm mt-2 inline-block">Éditer la page →</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

