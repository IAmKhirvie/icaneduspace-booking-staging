@props([
    'room',
    'dark' => false,
    'height' => 'h-80 md:h-[460px]',
    'frame' => true,
])

@php
    $images = collect($room?->gallery_images ?? [])
        ->prepend($room?->hero_image)
        ->filter()
        ->unique()
        ->values();
@endphp

@if($room && $images->isNotEmpty())
    <div
        class="{{ $frame ? ($dark ? 'glass-panel' : 'card') : '' }} overflow-hidden"
        data-room-gallery
        data-gallery-images='@json($images)'
    >
        <div class="relative {{ $height }} bg-brand-navy/10 overflow-hidden">
            <img
                src="{{ $images->first() }}"
                alt="{{ $room->name }}"
                class="w-full h-full object-cover"
                data-gallery-image
            >
            <div class="absolute inset-x-0 bottom-0 p-4 flex items-center justify-between bg-gradient-to-t from-black/70 to-transparent">
                <div>
                    <p class="text-xs uppercase tracking-[0.22em] {{ $dark ? 'text-white/60' : 'text-white/70' }}">{{ __("Room gallery") }}</p>
                    <p class="font-serif text-2xl text-white">{{ $room->name }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" class="gallery-control" data-gallery-prev aria-label="{{ __('Previous photo') }}">‹</button>
                    <span class="text-white/75 text-xs uppercase tracking-[0.18em]" data-gallery-count>1 / {{ $images->count() }}</span>
                    <button type="button" class="gallery-control" data-gallery-next aria-label="{{ __('Next photo') }}">›</button>
                </div>
            </div>
        </div>

        @if($images->count() > 1)
            <div class="p-4 grid grid-cols-4 md:grid-cols-6 gap-3 {{ $dark ? 'bg-brand-dark/45' : 'bg-white' }}">
                @foreach($images as $index => $image)
                    <button
                        type="button"
                        class="gallery-thumb {{ $index === 0 ? 'active' : '' }}"
                        data-gallery-thumb
                        data-gallery-index="{{ $index }}"
                        aria-label="{{ __('View photo') }} {{ $index + 1 }}"
                    >
                        <img src="{{ $image }}" alt="" class="w-full h-20 object-cover">
                    </button>
                @endforeach
            </div>
        @endif
    </div>

    @once
        <style>
            .gallery-control {
                width: 2.25rem;
                height: 2.25rem;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border: 1px solid rgba(255,255,255,0.55);
                color: #fff;
                background: rgba(7,17,47,0.45);
                font-size: 1.5rem;
                line-height: 1;
                transition: all 180ms ease;
            }
            .gallery-control:hover {
                background: #D9A72F;
                color: #07112F;
                border-color: #D9A72F;
            }
            .gallery-thumb {
                border: 2px solid transparent;
                opacity: 0.72;
                transition: border-color 180ms ease, opacity 180ms ease, transform 180ms ease;
            }
            .gallery-thumb:hover,
            .gallery-thumb.active {
                border-color: #D9A72F;
                opacity: 1;
            }
            .gallery-thumb:hover {
                transform: translateY(-1px);
            }
        </style>
        <script>
            (() => {
                const setup = (gallery) => {
                    if (gallery.dataset.galleryReady === '1') return;
                    gallery.dataset.galleryReady = '1';

                    const images = JSON.parse(gallery.dataset.galleryImages || '[]');
                    const image = gallery.querySelector('[data-gallery-image]');
                    const count = gallery.querySelector('[data-gallery-count]');
                    const thumbs = Array.from(gallery.querySelectorAll('[data-gallery-thumb]'));
                    let index = 0;

                    const show = (nextIndex) => {
                        if (!images.length || !image) return;
                        index = (nextIndex + images.length) % images.length;
                        image.src = images[index];
                        if (count) count.textContent = `${index + 1} / ${images.length}`;
                        thumbs.forEach((thumb) => thumb.classList.toggle('active', Number(thumb.dataset.galleryIndex) === index));
                    };

                    gallery.querySelector('[data-gallery-prev]')?.addEventListener('click', () => show(index - 1));
                    gallery.querySelector('[data-gallery-next]')?.addEventListener('click', () => show(index + 1));
                    thumbs.forEach((thumb) => thumb.addEventListener('click', () => show(Number(thumb.dataset.galleryIndex || 0))));
                };

                const init = () => document.querySelectorAll('[data-room-gallery]').forEach(setup);

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', init);
                } else {
                    init();
                }
            })();
        </script>
    @endonce
@endif
