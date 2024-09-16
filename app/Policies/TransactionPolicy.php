<?php

namespace App\Policies;

use App\Models\{Transaction, User};
use Illuminate\Auth\Access\HandlesAuthorization;

class TransactionPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Transaction $transaction): bool
    {
        // O usuário pode ver a transação se a conta bancária pertencer à sua empresa
        return $user->company_id === $transaction->bankAccount->company_id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Transaction $transaction): bool
    {
        // O usuário pode editar apenas as contas da sua empresa
        //return $this->view($user, $bankAccount);

        // O usuário pode editar a transação se a conta bancária pertencer à sua empresa
        return $user->company_id === $transaction->bankAccount->company_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Transaction $transaction): bool
    {
        // O usuário pode deletar apenas as contas da sua empresa
        //return $this->view($user, $bankAccount);

        // O usuário pode deletar a transação se a conta bancária pertencer à sua empresa
        return $user->company_id === $transaction->bankAccount->company_id;
    }
}
