<?php

namespace App\Policies;

use App\Models\{BankAccount, User};
use Illuminate\Auth\Access\{HandlesAuthorization};

class BankAccountPolicy
{
    use HandlesAuthorization;
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user, BankAccount $bankAccount): bool
    {
        return false; // para não dar erro de método>
    }

    public function index(User $user, BankAccount $bankAccount): bool
    {
        // O usuário pode visualizar apenas as contas da sua empresa
        return $user->company_id === $bankAccount->company_id;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, BankAccount $bankAccount): bool
    {
        // O usuário pode visualizar apenas as contas da sua empresa
        return $user->company_id === $bankAccount->company_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false; // para não dar erro de método>
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, BankAccount $bankAccount): bool
    {
        // O usuário pode editar apenas as contas da sua empresa
        //return $this->view($user, $bankAccount);

        // O usuário pode editar a conta bancária se ela pertencer à sua empresa
        return $user->company_id === $bankAccount->company_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, BankAccount $bankAccount): bool
    {
        // O usuário pode deletar apenas as contas da sua empresa
        //return $this->view($user, $bankAccount);

        // O usuário pode deletar a conta bancária se ela pertencer à sua empresa
        return $user->company_id === $bankAccount->company_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, BankAccount $bankAccount): bool
    {
        return false; // para não dar erro de método>
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, BankAccount $bankAccount): bool
    {
        return false; // para não dar erro de método>
    }
}
