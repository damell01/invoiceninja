<?php

namespace App\Policies;

use App\Models\Contract;
use App\Models\User;

class ContractPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view_contract') || $user->isAdmin();
    }

    public function view(User $user, Contract $contract): bool
    {
        return $user->company()->id === $contract->company_id
            && ($user->hasPermissionTo('view_contract') || $user->isAdmin());
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create_contract') || $user->isAdmin();
    }

    public function update(User $user, Contract $contract): bool
    {
        return $user->company()->id === $contract->company_id
            && ($user->hasPermissionTo('edit_contract') || $user->isAdmin())
            && $contract->isEditable();
    }

    public function delete(User $user, Contract $contract): bool
    {
        return $user->company()->id === $contract->company_id
            && ($user->hasPermissionTo('delete_contract') || $user->isAdmin())
            && !$contract->is_locked;
    }

    public function send(User $user, Contract $contract): bool
    {
        return $user->company()->id === $contract->company_id
            && ($user->hasPermissionTo('send_contract') || $user->isAdmin());
    }
}
