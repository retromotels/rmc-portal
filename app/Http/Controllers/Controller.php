<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Collection;

abstract class Controller
{
    /** The property (user row) the portal is currently acting on. */
    protected function currentProperty(): User
    {
        return request()->attributes->get('currentProperty') ?? auth()->user();
    }

    /** All property ids under the current account (for ownership checks). */
    protected function accountPropertyIds(): Collection
    {
        $props = request()->attributes->get('accountProperties');
        return $props ? $props->pluck('id') : collect([auth()->id()]);
    }
}
