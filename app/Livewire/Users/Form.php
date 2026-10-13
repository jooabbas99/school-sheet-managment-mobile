<?php

namespace App\Livewire\Users;

use App\Livewire\Concerns\HandlesApiErrors;
use App\Services\Api\LookupsApi;
use App\Services\Api\UsersApi;
use App\Support\CurrentUser;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Form extends Component
{
    use HandlesApiErrors;

    public ?int $userId = null;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $role = 'staff';

    public string $institute_id = '';

    public bool $is_active = true;

    public function mount(?int $user = null): void
    {
        if ($user === null) {
            $this->institute_id = (string) (array_key_first($this->callApi(fn () => app(LookupsApi::class)->instituteOptions()) ?? []) ?? '');

            return;
        }

        $data = $this->callApi(fn () => app(UsersApi::class)->find($user));

        if ($data !== null) {
            $this->userId = $user;
            $this->name = $data['name'];
            $this->email = $data['email'];
            $this->role = $data['role']['value'] ?? 'staff';
            $this->institute_id = (string) ($data['institute']['id'] ?? '');
            $this->is_active = (bool) $data['is_active'];
        }
    }

    public function save(UsersApi $users): void
    {
        $this->resetErrorBag();

        $saved = $this->callApi(fn () => $users->save($this->userId, [
            'name' => trim($this->name),
            'email' => strtolower(trim($this->email)),
            'password' => $this->password !== '' ? $this->password : null,
            'role' => $this->role,
            'institute_id' => $this->role === 'super_admin' || $this->institute_id === '' ? null : (int) $this->institute_id,
            'is_active' => $this->is_active,
        ]));

        $this->password = '';

        if ($saved !== null) {
            session()->flash('status', __('users.saved'));
            $this->redirectRoute('users.index', navigate: true);
        }
    }

    public function delete(UsersApi $users): void
    {
        $this->callApi(function () use ($users) {
            $users->delete((int) $this->userId);
            session()->flash('status', __('users.deleted'));
            $this->redirectRoute('users.index', navigate: true);
        });
    }

    public function render(LookupsApi $lookups, CurrentUser $me): View
    {
        return view('livewire.users.form', [
            'institutes' => $this->callApi(fn () => $lookups->instituteOptions(), resetErrors: false) ?? [],
            'roles' => __('users.roles'),
            'isMe' => $this->userId !== null && $this->userId === ($me->data()['id'] ?? null),
        ])
            ->title(__($this->userId ? 'users.edit' : 'users.new'))
            ->layoutData(['back' => route('users.index')]);
    }
}
