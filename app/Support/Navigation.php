<?php

namespace App\Support;

/**
 * App sections, shown only when the user has the permission. The server still checks every request.
 */
class Navigation
{
    /**
     * route name => [label key, icon, permission or null]
     */
    private const TABS = [
        'home' => ['app.nav.home', 'home', null],
        'students.index' => ['app.nav.students', 'users', 'students.view'],
        'reports.index' => ['app.nav.reports', 'document', 'reports.view'],
        'templates.index' => ['app.nav.templates', 'table', 'templates.manage'],
    ];

    private const MORE = [
        'dashboard' => ['app.nav.dashboard', 'chart', 'students.view'],
        'users.index' => ['app.nav.users', 'user-group', 'users.manage'],
        'lookups.index' => ['app.nav.lookups', 'list', 'lookups.manage'],
        'institute.logo' => ['app.nav.logo', 'photo', 'templates.manage'],
        'activity.index' => ['app.nav.activity', 'clock', 'activity.view'],
    ];

    public function __construct(private readonly CurrentUser $user) {}

    /**
     * Bottom bar tabs (the "more" tab is added by the view).
     *
     * @return list<array{route: string, label: string, icon: string}>
     */
    public function tabs(): array
    {
        return $this->visible(self::TABS);
    }

    /**
     * Items on the "more" screen.
     *
     * @return list<array{route: string, label: string, icon: string}>
     */
    public function more(): array
    {
        return $this->visible(self::MORE);
    }

    /**
     * Permission needed for a route, if it is an app section.
     */
    public static function permissionFor(string $route): ?string
    {
        return (self::TABS + self::MORE)[$route][2] ?? null;
    }

    /**
     * @param  array<string, array{0: string, 1: string, 2: string|null}>  $items
     * @return list<array{route: string, label: string, icon: string}>
     */
    private function visible(array $items): array
    {
        $visible = [];

        foreach ($items as $route => [$label, $icon, $permission]) {
            if ($permission === null || $this->user->can($permission)) {
                $visible[] = ['route' => $route, 'label' => __($label), 'icon' => $icon];
            }
        }

        return $visible;
    }
}
