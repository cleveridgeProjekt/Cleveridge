<?php

namespace App\Policies;

use App\Models\Fridge;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FridgePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Fridge $fridge): bool
    {
        return $fridge->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Fridge $fridge): bool
    {
        return $fridge->user_id === $user->id;
    }

    public function delete(User $user, Fridge $fridge): bool
    {
        return $fridge->user_id === $user->id;
    }
}
