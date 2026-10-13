<form wire:submit="save" class="space-y-4">
    <x-api-error :message="$errors->any() ? null : $errorMessage" :offline="$offline" />

    <x-card class="space-y-4">
        <x-field :label="__('users.fields.name')" for="name" error="name">
            <x-input id="name" wire:model="name" />
        </x-field>
        <x-field :label="__('users.fields.email')" for="email" error="email">
            <x-input id="email" type="email" wire:model="email" inputmode="email" autocomplete="off" ltr />
        </x-field>
        <x-field :label="__('users.fields.password')" for="password" error="password" :hint="$userId ? __('users.password_hint') : null">
            <x-input id="password" type="password" wire:model="password" autocomplete="new-password" ltr />
        </x-field>
        <x-field :label="__('users.fields.role')" for="role" error="role">
            <x-select id="role" wire:model.live="role" :options="$roles" />
        </x-field>
        @if ($role !== 'super_admin')
            <x-field :label="__('users.fields.institute')" for="institute_id" error="institute_id">
                <x-select id="institute_id" wire:model="institute_id" :options="$institutes" :placeholder="__('students.choose')" />
            </x-field>
        @endif
        @unless ($isMe)
            <label class="flex items-center justify-between">
                <span>{{ __('users.fields.is_active') }}</span>
                <input type="checkbox" wire:model="is_active" class="h-6 w-6 rounded text-primary-700">
            </label>
        @endunless
    </x-card>

    <x-button type="submit" class="w-full" wire:loading.attr="disabled" wire:target="save">{{ __('app.save') }}</x-button>

    @if ($userId && ! $isMe)
        <x-confirm-button action="delete" :label="__('users.delete')" :confirm="__('users.confirm_delete')" />
    @endif
</form>
