<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CompanyPolicy
{
    use HandlesAuthorization;

    public function manage(User $user): mixed
    {
        // Apenas superusuários podem gerenciar empresas
        return $user->is_superuser;
    }
}
