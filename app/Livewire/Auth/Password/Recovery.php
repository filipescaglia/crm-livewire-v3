<?php

namespace App\Livewire\Auth\Password;

use App\Models\User;
use App\Notifications\PasswordRecoveryNotification;
use Livewire\Attributes\Rule;
use Livewire\Component;

class Recovery extends Component
{
    #[Rule(['required', 'email'])]
    public string $email;

    public string $message;

    public function render()
    {
        return view('livewire.auth.password.recovery');
    }

    public function startPasswordRecovery()
    {
        $this->validate();

        $user = User::whereEmail($this->email)->first();

        $user?->notify(new PasswordRecoveryNotification());

        $this->message = 'You will receive an email with the password recovery link.';
    }
}
