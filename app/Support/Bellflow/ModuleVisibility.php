<?php

namespace App\Support\Bellflow;

use App\Models\ClientContact;
use App\Models\User;

class ModuleVisibility
{
    public static function isModuleVisible(string $alias): bool
    {
        $alias = strtolower(trim($alias));

        if ($alias === '') {
            return false;
        }

        $mode = config('bellflow.ui.module_visibility_mode', 'allow_all');
        $allowed = self::normalize(config('bellflow.ui.allowed_modules', []));
        $hidden = self::normalize(config('bellflow.ui.hidden_modules', []));

        if (in_array($alias, $hidden, true)) {
            return false;
        }

        if ($mode === 'allow_list') {
            return in_array($alias, $allowed, true);
        }

        return true;
    }

    public static function filterPortalItems(array $items): array
    {
        $hidden = self::normalize(config('bellflow.ui.hidden_portal_items', []));

        if (empty($hidden)) {
            return $items;
        }

        return array_values(array_filter($items, function (array $item) use ($hidden) {
            $id = strtolower((string) ($item['id'] ?? ''));

            return $id === '' || ! in_array($id, $hidden, true);
        }));
    }

    public static function canShowContractsAdmin(?User $user): bool
    {
        if (! $user || ! config('contracts.ui.enabled', true) || ! config('contracts.ui.admin_enabled', true) || ! self::isModuleVisible('contracts')) {
            return false;
        }

        return $user->isAdmin()
            || $user->hasPermission('view_contract')
            || $user->hasPermission('create_contract')
            || $user->hasPermission('edit_contract');
    }

    public static function canShowContractsPortal(?ClientContact $contact): bool
    {
        return (bool) $contact
            && config('contracts.ui.enabled', true)
            && config('contracts.ui.portal_enabled', true)
            && self::isModuleVisible('contracts');
    }

    public static function canShowContractsPortalMenu(?ClientContact $contact): bool
    {
        return self::canShowContractsPortal($contact) && config('contracts.ui.portal_menu_enabled', true);
    }

    public static function canShowContractsPublicSign(): bool
    {
        return config('contracts.ui.enabled', true)
            && config('contracts.ui.public_sign_enabled', true)
            && self::isModuleVisible('contracts');
    }

    /**
     * @param  array<int, string>|mixed  $items
     * @return array<int, string>
     */
    private static function normalize(mixed $items): array
    {
        return array_values(array_filter(array_map(
            static fn ($item) => strtolower(trim((string) $item)),
            is_array($items) ? $items : []
        )));
    }
}
