<?php

declare(strict_types=1);

namespace App\Livewire\Profile;

use App\Models\User;
use App\Services\Accounts;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\Livewire;

final class SwitchAccount extends Component
{
    public function switch(Request $request, string $username): void
    {
        Accounts::switch($username);

        $this->redirect(Livewire::originalUrl());
    }

    public function render(): View
    {
        $accounts = array_keys(Accounts::all());

        return view('livewire.profile.switch-account', [
            'accounts' => User::query()
                ->select('id', 'username')
                ->whereIn('username', $accounts)
                ->get(),
        ]);
    }
}
