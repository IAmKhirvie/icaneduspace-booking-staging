<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-end gap-4 flex-wrap">
            <div>
                <p class="eyebrow mb-1">{{ $classroom->exists ? 'Edit room' : 'New room' }}</p>
                <h1 class="font-serif text-4xl text-brand-navy">{{ $classroom->exists ? $classroom->name : 'New classroom' }}</h1>
                <p class="text-brand-navy/60 mt-1">{{ __("Update the room details, media, and customer-facing information.") }}</p>
            </div>
            @if($classroom->exists)
                <a href="{{ route('rooms.show', $classroom) }}" class="btn-ghost">{{ __("View room") }}</a>
            @endif
        </div>
    </x-slot>

    @php
        $heroImage = old('image_url', $classroom->image_url);
        $galleryText = old('gallery', is_array($classroom->gallery) ? implode("\n", $classroom->gallery) : '');
        $galleryPreview = collect(preg_split('/\n/', (string) $galleryText))
            ->map(fn ($item) => trim($item))
            ->filter()
            ->values();
    @endphp

    <div class="max-w-6xl mx-auto px-6 py-10 space-y-6">

        @if ($errors->any())
            <div class="border border-red-300 bg-red-50 px-5 py-3 text-sm text-red-700">
                <ul class="space-y-1">@foreach($errors->all() as $e)<li>· {{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <div class="grid lg:grid-cols-[0.9fr_1.1fr] gap-6 items-start">
            <aside class="card overflow-hidden sticky top-6">
                <div class="relative h-80 bg-brand-navy/10">
                    <img id="hero-preview" src="{{ $heroImage }}" class="{{ $heroImage ? '' : 'hidden' }} w-full h-full object-cover" alt="">
                    <div id="hero-empty" class="{{ $heroImage ? 'hidden' : '' }} h-full flex items-center justify-center text-brand-navy/45 text-sm uppercase tracking-[0.18em]">
                        {{ __("Add a hero image") }}
                    </div>
                    <div class="absolute inset-x-0 bottom-0 p-5 bg-gradient-to-t from-brand-navy/90 to-transparent">
                        <p class="text-brand-gold text-xs uppercase tracking-[0.22em] mb-1">{{ __("Room preview") }}</p>
                        <h2 class="font-serif text-3xl text-white">{{ old('name', $classroom->name ?: 'New classroom') }}</h2>
                    </div>
                </div>

                <div class="p-5 space-y-4">
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div class="border border-brand-navy/10 p-3">
                            <p class="text-xs uppercase tracking-[0.18em] text-brand-navy/45 mb-1">{{ __("Capacity") }}</p>
                            <p class="font-serif text-2xl text-brand-navy">{{ old('capacity', $classroom->capacity ?? 12) }}</p>
                        </div>
                        <div class="border border-brand-navy/10 p-3">
                            <p class="text-xs uppercase tracking-[0.18em] text-brand-navy/45 mb-1">{{ __("Rate") }}</p>
                            <p class="font-serif text-2xl text-brand-navy">₱{{ old('hourly_rate', $classroom->hourly_rate ?? 0) }}</p>
                        </div>
                    </div>

                    <div>
                        <p class="text-xs uppercase tracking-[0.18em] text-brand-navy/45 mb-2">{{ __("Gallery preview") }}</p>
                        <div id="gallery-preview" class="grid grid-cols-3 gap-2">
                            @forelse($galleryPreview as $image)
                                <img src="{{ $image }}" class="h-20 w-full object-cover border border-brand-navy/10" alt="">
                            @empty
                                <p class="col-span-3 text-sm text-brand-navy/50">{{ __("Add one image path per line to build the room slider.") }}</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </aside>

            <form method="POST"
                  enctype="multipart/form-data"
                  action="{{ $classroom->exists ? route('manage.classrooms.update', $classroom) : route('manage.classrooms.store') }}"
                  class="card p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
                @csrf

                <div class="md:col-span-2">
                    <p class="eyebrow mb-3">{{ __("Room details") }}</p>
                    <label class="text-xs uppercase tracking-[0.18em] text-brand-navy/55 mb-1 block">{{ __("Name") }}</label>
                    <input name="name" required value="{{ old('name', $classroom->name) }}">
                </div>
                <div>
                    <label class="text-xs uppercase tracking-[0.18em] text-brand-navy/55 mb-1 block">{{ __("Slug") }}</label>
                    <input name="slug" required value="{{ old('slug', $classroom->slug) }}">
                </div>
                <div>
                    <label class="text-xs uppercase tracking-[0.18em] text-brand-navy/55 mb-1 block">{{ __("Location") }}</label>
                    <input name="location" value="{{ old('location', $classroom->location) }}">
                </div>
                <div>
                    <label class="text-xs uppercase tracking-[0.18em] text-brand-navy/55 mb-1 block">{{ __("Room number") }}</label>
                    <input name="room_number" value="{{ old('room_number', $classroom->room_number) }}" placeholder="{{ __('Example: 1103') }}">
                </div>
                <div>
                    <label class="text-xs uppercase tracking-[0.18em] text-brand-navy/55 mb-1 block">{{ __("Floor") }}</label>
                    <input name="floor" value="{{ old('floor', $classroom->floor) }}" placeholder="{{ __('Example: 11F') }}">
                </div>
                <div class="md:col-span-2">
                    <label class="text-xs uppercase tracking-[0.18em] text-brand-navy/55 mb-1 block">{{ __("Address") }}</label>
                    <input name="address" value="{{ old('address', $classroom->address) }}">
                </div>
                <div class="md:col-span-2">
                    <label class="text-xs uppercase tracking-[0.18em] text-brand-navy/55 mb-1 block">{{ __("Arrival instructions") }}</label>
                    <textarea name="arrival_instructions" rows="3" placeholder="{{ __('Example: Take the elevator to 11F, turn right, and check in at reception.') }}">{{ old('arrival_instructions', $classroom->arrival_instructions) }}</textarea>
                </div>
                <div>
                    <label class="text-xs uppercase tracking-[0.18em] text-brand-navy/55 mb-1 block">{{ __("Capacity") }}</label>
                    <input name="capacity" type="number" min="1" required value="{{ old('capacity', $classroom->capacity ?? 12) }}">
                </div>
                <div>
                    <label class="text-xs uppercase tracking-[0.18em] text-brand-navy/55 mb-1 block">{{ __("Hourly rate (PHP)") }}</label>
                    <input name="hourly_rate" type="number" min="0" required value="{{ old('hourly_rate', $classroom->hourly_rate ?? 0) }}">
                </div>
                <div class="md:col-span-2">
                    <label class="text-xs uppercase tracking-[0.18em] text-brand-navy/55 mb-1 block">{{ __("Description") }}</label>
                    <textarea name="description" rows="3">{{ old('description', $classroom->description) }}</textarea>
                </div>

                <div class="md:col-span-2 border-t border-brand-navy/10 pt-5">
                    <p class="eyebrow mb-3">{{ __("Media") }}</p>
                    <label class="text-xs uppercase tracking-[0.18em] text-brand-navy/55 mb-1 block">{{ __("Hero image") }}</label>
                    <div class="flex flex-col sm:flex-row gap-3">
                        <input id="image_url" name="image_url" type="text" value="{{ $heroImage }}" placeholder="/media/AICognitionRoom.jpeg">
                        <label class="btn-ghost shrink-0 text-center cursor-pointer">
                            {{ __("Open file") }}
                            <input id="hero_image_upload" name="hero_image_upload" type="file" accept="image/*" class="sr-only">
                        </label>
                    </div>
                    <p id="hero-file-name" class="mt-2 text-xs text-brand-navy/45">{{ __("Choose a file, use /media/file-name.jpeg, or paste a full https:// URL.") }}</p>
                </div>
                <div>
                    <label class="text-xs uppercase tracking-[0.18em] text-brand-navy/55 mb-1 block">{{ __("Amenities") }}</label>
                    <textarea name="amenities" rows="6" placeholder="{{ __('One per line, or comma separated') }}">{{ old('amenities', is_array($classroom->amenities) ? implode("\n", $classroom->amenities) : '') }}</textarea>
                </div>
                <div>
                    <div class="flex items-center justify-between gap-3 mb-1">
                        <label class="text-xs uppercase tracking-[0.18em] text-brand-navy/55 block">{{ __("Gallery images") }}</label>
                        <label class="text-xs uppercase tracking-[0.16em] text-brand-navy hover:text-brand-gold cursor-pointer">
                            {{ __("Open files") }}
                            <input id="gallery_uploads" name="gallery_uploads[]" type="file" accept="image/*" multiple class="sr-only">
                        </label>
                    </div>
                    <textarea id="gallery" name="gallery" rows="6" placeholder="/media/AICognitionRoom.jpeg&#10;/media/AICognitionDoor.jpeg">{{ $galleryText }}</textarea>
                    <p id="gallery-file-name" class="mt-2 text-xs text-brand-navy/45">{{ __("One image per line, or select files to add them when you save.") }}</p>
                </div>
                <label class="md:col-span-2 flex items-center justify-between gap-4 border border-brand-navy/10 bg-brand-navy/[0.02] px-4 py-3 text-sm normal-case tracking-normal">
                    <span>
                        <span class="block font-medium text-brand-navy">{{ __("Visible to customers") }}</span>
                        <span class="block text-brand-navy/50">{{ __("Turn this off to hide the room from public booking screens.") }}</span>
                    </span>
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $classroom->is_active)) class="!w-5 !h-5 accent-brand-gold">
                </label>

                <div class="md:col-span-2 flex flex-col sm:flex-row gap-3 justify-between pt-3">
                    <a href="{{ route('manage.classrooms.index') }}" class="btn-ghost text-center">← {{ __("Cancel") }}</a>
                    <button class="btn-gold">{{ $classroom->exists ? 'Save changes' : 'Create classroom' }}</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        (() => {
            const heroInput = document.getElementById('image_url');
            const heroUpload = document.getElementById('hero_image_upload');
            const galleryInput = document.getElementById('gallery');
            const galleryUploads = document.getElementById('gallery_uploads');
            const heroPreview = document.getElementById('hero-preview');
            const heroEmpty = document.getElementById('hero-empty');
            const galleryPreview = document.getElementById('gallery-preview');
            const heroFileName = document.getElementById('hero-file-name');
            const galleryFileName = document.getElementById('gallery-file-name');
            let selectedGalleryFiles = [];

            heroInput?.addEventListener('input', () => {
                if (!heroPreview) return;

                const path = heroInput.value.trim();
                if (!path) {
                    heroPreview.classList.add('hidden');
                    heroEmpty?.classList.remove('hidden');
                    return;
                }

                heroPreview.src = path;
                heroPreview.classList.remove('hidden');
                heroEmpty?.classList.add('hidden');
            });

            heroUpload?.addEventListener('change', () => {
                const file = heroUpload.files?.[0];
                if (!file || !heroPreview) return;

                heroPreview.src = URL.createObjectURL(file);
                heroPreview.classList.remove('hidden');
                heroEmpty?.classList.add('hidden');
                if (heroFileName) heroFileName.textContent = file.name;
            });

            const renderGalleryPreview = () => {
                if (!galleryPreview) return;
                const typedImages = galleryInput.value.split('\n').map((item) => item.trim()).filter(Boolean);
                const uploadedImages = selectedGalleryFiles.map((file) => URL.createObjectURL(file));
                const images = typedImages.concat(uploadedImages).slice(0, 6);

                galleryPreview.innerHTML = images.length
                    ? images.map((image) => `<img src="${image}" class="h-20 w-full object-cover border border-brand-navy/10" alt="">`).join('')
                    : `<p class="col-span-3 text-sm text-brand-navy/50">{{ __("Add one image path per line to build the room slider.") }}</p>`;
            };

            galleryInput?.addEventListener('input', renderGalleryPreview);
            galleryUploads?.addEventListener('change', () => {
                selectedGalleryFiles = Array.from(galleryUploads.files || []);
                if (galleryFileName && selectedGalleryFiles.length) {
                    galleryFileName.textContent = selectedGalleryFiles.length === 1
                        ? selectedGalleryFiles[0].name
                        : `${selectedGalleryFiles.length} files selected`;
                }
                renderGalleryPreview();
            });
        })();
    </script>
</x-app-layout>
