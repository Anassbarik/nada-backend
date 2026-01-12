@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold break-words">{{ __('Send Newsletter') }}</h1>
    </div>

    <div class="mb-4">
        <a href="{{ route('admin.newsletter.index') }}" 
           class="text-logo-link hover:underline inline-flex items-center"
           data-livewire-ignore="true">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
            {{ __('Back to Subscribers') }}
        </a>
    </div>

    <x-shadcn.card class="shadow-lg">
        <x-shadcn.card-content class="p-6">
            <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <p class="text-sm text-blue-800">
                    <strong>{{ __('Active Subscribers') }}:</strong> {{ number_format($subscriberCount) }}
                </p>
                <p class="text-xs text-blue-600 mt-1">{{ __('This newsletter will be sent to all active subscribers.') }}</p>
            </div>

            <form method="POST" action="{{ route('admin.newsletter.send') }}">
                @csrf

                <div class="mb-4">
                    <x-input-label for="subject" :value="__('Subject')" />
                    <x-text-input id="subject" class="block mt-1 w-full" type="text" name="subject" :value="old('subject')" required autofocus placeholder="Newsletter Subject" />
                    <x-input-error :messages="$errors->get('subject')" class="mt-2" />
                </div>

                <div class="mb-4">
                    <x-input-label for="content" :value="__('Content')" />
                    <textarea id="content" name="content" rows="12" class="block mt-1 w-full bg-white text-gray-900 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required placeholder="Enter your newsletter content here...">{{ old('content') }}</textarea>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('You can use plain text. Line breaks will be preserved.') }}</p>
                    <x-input-error :messages="$errors->get('content')" class="mt-2" />
                </div>

                <div class="mb-6 p-4 bg-gray-50 border border-gray-200 rounded-lg">
                    <div class="flex items-start">
                        <input type="checkbox" id="send_test" name="send_test" value="1" class="mt-1 mr-2" {{ old('send_test') ? 'checked' : '' }}>
                        <div class="flex-1">
                            <label for="send_test" class="font-medium text-gray-900">{{ __('Send Test Email First') }}</label>
                            <p class="text-sm text-gray-600 mt-1">{{ __('Check this to send a test email before sending to all subscribers.') }}</p>
                            <div id="test_email_container" class="mt-3" style="display: none;">
                                <x-input-label for="test_email" :value="__('Test Email Address')" />
                                <x-text-input id="test_email" class="block mt-1 w-full" type="email" name="test_email" :value="old('test_email', auth()->user()->email)" placeholder="test@example.com" />
                                <x-input-error :messages="$errors->get('test_email')" class="mt-2" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end mt-4 gap-3">
                    <a href="{{ route('admin.newsletter.index') }}" 
                       class="text-gray-600 hover:text-gray-900"
                       data-livewire-ignore="true">{{ __('cancel') }}</a>
                    <x-primary-button type="submit" class="btn-logo-primary">
                        {{ __('Send Newsletter') }}
                    </x-primary-button>
                </div>
            </form>
        </x-shadcn.card-content>
    </x-shadcn.card>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const checkbox = document.getElementById('send_test');
        const container = document.getElementById('test_email_container');
        
        checkbox.addEventListener('change', function() {
            container.style.display = this.checked ? 'block' : 'none';
            if (this.checked) {
                document.getElementById('test_email').required = true;
            } else {
                document.getElementById('test_email').required = false;
            }
        });
        
        // Show container if checkbox was checked on page load
        if (checkbox.checked) {
            container.style.display = 'block';
        }
    });
</script>
@endsection

