<?php

namespace App\Policies;

use App\Models\Fridge;
use App\Models\User;

class FridgePolicy
{
    /**
     * Anyone logged in can see their own list of fridges.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * A user can view their own fridge.
     */
    public function view(User $user, Fridge $fridge): bool
    {
        return $fridge->user_id === $user->id;
    }

    /**
     * A user can create a fridge for themselves.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * A user can update their own fridge.
     */
    public function update(User $user, Fridge $fridge): bool
    {
        return $fridge->user_id === $user->id;
    }

    /**
     * A user can delete their own fridge.
     */
    public function delete(User $user, Fridge $fridge): bool
    {
        return $fridge->user_id === $user->id;
    }
}
