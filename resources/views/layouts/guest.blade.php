<x-layouts::base :title="$title ?? null">
    <main class="flex min-h-screen flex-col justify-center px-6 py-10 safe-top">
        {{ $slot }}
    </main>
</x-layouts::base>
