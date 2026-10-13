<div class="mx-auto w-full max-w-sm">
    <div class="mb-8 text-center">
        <h1 class="text-2xl font-bold text-primary-700">{{ __('app.name') }}</h1>
        <p class="mt-1 text-slate-500">{{ __('auth.subtitle') }}</p>
    </div>

    @if (session('status'))
        <x-alert type="warning" class="mb-4">{{ session('status') }}</x-alert>
    @endif

    @if ($errorMessage && ! $errors->any())
        <x-alert class="mb-4">{{ $errorMessage }}</x-alert>
    @endif

    <form wire:submit="login" class="space-y-4">
        <div>
            <label for="email" class="mb-1 block text-sm font-medium">{{ __('auth.email') }}</label>
            <input id="email" type="email" wire:model="email" dir="ltr" autocomplete="username" inputmode="email"
                   class="w-full rounded-lg border border-slate-300 px-3 py-3 text-left focus:border-primary-600 focus:ring-primary-600">
            @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password" class="mb-1 block text-sm font-medium">{{ __('auth.password') }}</label>
            <input id="password" type="password" wire:model="password" dir="ltr" autocomplete="current-password"
                   class="w-full rounded-lg border border-slate-300 px-3 py-3 text-left focus:border-primary-600 focus:ring-primary-600">
            @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <button type="submit" wire:loading.attr="disabled"
                class="w-full rounded-lg bg-primary-700 py-3 font-bold text-white disabled:opacity-60">
            <span wire:loading.remove wire:target="login">{{ __('auth.submit') }}</span>
            <span wire:loading wire:target="login">{{ __('app.loading') }}</span>
        </button>
    </form>
</div>
