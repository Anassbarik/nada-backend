@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-4xl font-bold">{{ __('Hotel Images') }}: {{ $hotel->name }}</h1>
    </div>

    <div class="mb-4">
        <a href="{{ route('admin.events.hotels.index', $hotel->event) }}" class="text-logo-link hover:underline inline-flex items-center">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
            Back to Hotels
        </a>
    </div>

    <x-shadcn.card class="shadow-lg">
        <x-shadcn.card-content class="p-6">
            <div class="mb-4">
                <h3 class="text-lg font-medium text-gray-900 mb-2">
                    Hotel Images ({{ $hotel->images->count() }}/10)
                </h3>
                
                @if($hotel->images->count() < 10)
                    <form method="POST" action="{{ route('admin.hotels.images.store', $hotel) }}" enctype="multipart/form-data" class="mb-4">
                        @csrf
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Upload Images (Max 10 total, 5MB each)
                        </label>
                        <input type="file" 
                               name="images[]" 
                               multiple 
                               accept="image/*" 
                               class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                               required>
                        @error('images') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        @error('images.*') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        
                        <button type="submit" class="btn-logo-primary mt-2 text-white font-bold py-2 px-4 rounded">
                            Upload Images
                        </button>
                    </form>
                @else
                    <p class="text-sm text-yellow-600 mb-4">Maximum 10 images reached. Delete some images to upload more.</p>
                @endif
            </div>

            @if($hotel->images->count() > 0)
                <div class="mb-2 text-sm text-gray-600">
                    <p>💡 Drag and drop images to reorder them</p>
                </div>
                <div class="grid grid-cols-3 gap-4" 
                     x-data="{
                         draggedId: null,
                         draggedOverIndex: null,
                         
                        handleDragStart(event, imageId) {
                            if (event.target.closest('.delete-button-container')) {
                                event.preventDefault();
                                return false;
                            }
                            this.draggedId = imageId;
                            event.dataTransfer.effectAllowed = 'move';
                            event.dataTransfer.setData('text/plain', imageId.toString());
                            event.currentTarget.style.opacity = '0.5';
                        },
                         
                         handleDragOver(event, index) {
                             event.preventDefault();
                             event.dataTransfer.dropEffect = 'move';
                             this.draggedOverIndex = index;
                         },
                         
                         handleDragLeave(event) {
                             this.draggedOverIndex = null;
                         },
                         
                         handleDrop(event, dropIndex) {
                             event.preventDefault();
                             event.stopPropagation();
                             
                             if (!this.draggedId) {
                                 this.resetDragState();
                                 return;
                             }
                             
                             const container = event.currentTarget.closest('.grid');
                             const imageElements = Array.from(container.querySelectorAll('[data-image-id]'));
                             const currentOrder = imageElements.map(el => parseInt(el.getAttribute('data-image-id')));
                             
                             const draggedIndex = currentOrder.indexOf(this.draggedId);
                             
                             if (draggedIndex === -1 || draggedIndex === dropIndex) {
                                 this.resetDragState();
                                 return;
                             }
                             
                             const newOrder = [...currentOrder];
                             newOrder.splice(draggedIndex, 1);
                             newOrder.splice(dropIndex, 0, this.draggedId);
                             
                             // Call reorder endpoint
                             fetch('{{ route('admin.hotels.images.reorder', $hotel) }}', {
                                 method: 'PATCH',
                                 headers: {
                                     'Content-Type': 'application/json',
                                     'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                 },
                                 body: JSON.stringify({ order: newOrder })
                             })
                             .then(response => response.json())
                             .then(data => {
                                 if (data.success) {
                                     window.location.reload();
                                 }
                             })
                             .catch(error => {
                                 console.error('Error:', error);
                                 alert('Failed to reorder images. Please try again.');
                             });
                             
                             this.resetDragState();
                         },
                         
                         handleDragEnd(event) {
                             this.resetDragState();
                         },
                         
                         resetDragState() {
                             document.querySelectorAll('[data-image-id]').forEach(el => {
                                 el.style.opacity = '1';
                             });
                             this.draggedId = null;
                             this.draggedOverIndex = null;
                         }
                     }">
                    @foreach($hotel->images->sortBy('sort_order') as $index => $image)
                        <div class="relative group border rounded-lg overflow-hidden bg-gray-100 cursor-move transition-all duration-200"
                             :class="{ 'ring-2 ring-blue-500 scale-105': draggedOverIndex === {{ $index }} }"
                             draggable="true"
                             data-image-id="{{ $image->id }}"
                             @dragstart="handleDragStart($event, {{ $image->id }})"
                             @dragover.prevent="handleDragOver($event, {{ $index }})"
                             @dragleave="handleDragLeave($event)"
                             @drop.prevent="handleDrop($event, {{ $index }})"
                             @dragend="handleDragEnd($event)">
                            <div class="absolute top-2 left-2 z-10 bg-blue-500 text-white text-xs px-2 py-1 rounded">
                                #{{ $image->sort_order + 1 }}
                            </div>
                            <img src="{{ $image->url }}" 
                                 alt="{{ $image->alt_text ?? 'Hotel image' }}" 
                                 class="w-full h-48 object-cover"
                                 draggable="false">
                            
                            <div class="delete-button-container absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity z-20"
                                 @dragstart.stop.prevent
                                 @dragover.stop
                                 @dragend.stop>
                                <form method="POST" action="{{ route('admin.hotels.images.destroy', [$hotel, $image]) }}" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            onclick="event.stopPropagation(); return confirm('Are you sure you want to delete this image?');"
                                            draggable="false"
                                            style="pointer-events: auto; z-index: 30; position: relative;"
                                            class="bg-red-500 hover:bg-red-700 text-white p-2 rounded-full text-xs cursor-pointer">
                                        🗑️
                                    </button>
                                </form>
                            </div>
                            
                            <div class="p-2 bg-white"
                                 @dragstart.stop
                                 @click.stop>
                                <form method="POST" action="{{ route('admin.hotels.images.update', [$hotel, $image]) }}" class="space-y-2">
                                    @csrf
                                    @method('PATCH')
                                    <input type="text" 
                                           name="alt_text" 
                                           placeholder="Alt text (optional)" 
                                           value="{{ $image->alt_text }}" 
                                           class="w-full text-sm p-2 border border-gray-300 rounded"
                                           onblur="this.form.submit()">
                                    <div class="mt-1 flex items-center justify-between text-xs text-gray-500">
                                        <span>Order: {{ $image->sort_order + 1 }}</span>
                                        <select name="status" onchange="this.form.submit()" class="text-xs px-2 py-1 rounded {{ $image->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                            <option value="active" {{ $image->status === 'active' ? 'selected' : '' }}>Active</option>
                                            <option value="inactive" {{ $image->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 border-2 border-dashed border-gray-300 rounded-lg">
                    <p class="text-gray-500">No images uploaded yet.</p>
                </div>
            @endif
        </x-shadcn.card-content>
    </x-shadcn.card>
</div>
@endsection
