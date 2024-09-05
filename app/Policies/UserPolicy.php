<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\{HandlesAuthorization};

class UserPolicy
{
    use HandlesAuthorization;

    public function manage(User $user): mixed
    {
        // Apenas superusuários podem gerenciar outros usuários
        return $user->is_superuser;
    }
}
