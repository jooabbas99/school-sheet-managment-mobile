@props(['href', 'label'])
<a href="{{ $href }}" wire:navigate aria-label="{{ $label }}"
   class="fixed bottom-20 left-4 z-20 flex items-center gap-2 rounded-full bg-primary-700 px-5 py-3 font-bold text-white shadow-lg">
    <span class="text-xl leading-none">+</span> {{ $label }}
</a>
