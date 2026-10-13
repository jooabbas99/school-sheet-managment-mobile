<div class="space-y-4">
    <section class="rounded-xl bg-white p-4 shadow-sm">
        <p class="text-lg font-bold">{{ __('app.hello', ['name' => $user['name'] ?? '']) }}</p>
        <p class="mt-1 text-sm text-slate-500">{{ __('app.role', ['role' => $user['role']['label'] ?? '']) }}</p>
        <p class="text-sm text-slate-500">
            {{ isset($user['institute']['name']) ? __('app.your_institute', ['name' => $user['institute']['name']]) : __('app.all_institutes') }}
        </p>
    </section>
</div>
