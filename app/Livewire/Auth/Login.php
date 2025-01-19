<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Login extends Component
{
    public ?string $email;

    public ?string $password;

    public function render()
    {
        return view('livewire.auth.login');
    }

    public function tryToLogin(): void
    {
        Auth::attempt(['email' => $this->email, 'password' => $this->password]);

        $this->redirect(route('dashboard'));
    }
}
