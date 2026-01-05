import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

// Ensure navigation links with data-livewire-ignore work properly
// This prevents Livewire from intercepting standard navigation links
document.addEventListener('click', function(e) {
    const link = e.target.closest('a[data-livewire-ignore="true"]');
    if (link && link.href && !link.hasAttribute('wire:click')) {
        // Force normal browser navigation for ignored links
        e.stopPropagation();
        window.location.href = link.href;
        return false;
    }
}, true);
