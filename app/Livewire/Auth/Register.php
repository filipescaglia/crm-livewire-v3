<?php

namespace App\Livewire\Auth;

use App\Models\User;
use App\Notifications\WelcomeNotification;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Rule;
use Livewire\Component;

class Register extends Component
{
    #[Rule(['required', 'max:255'])]
    public ?string $name = null;

    #[Rule(['required', 'email', 'max:255', 'confirmed', 'unique:users,email'])]
    public ?string $email = null;

    public ?string $email_confirmation = null;

    #[Rule(['required'])]
    public ?string $password = null;

    public function render()
    {
        return view('livewire.auth.register')
            ->layout('components.layouts.guest');
    }

    public function submit(): void
    {
        $this->validate();

        $user = User::query()->create([
            'name'     => $this->name,
            'email'    => $this->email,
            'password' => Hash::make($this->password),
        ]);

        auth()->login($user);

        $user->notify(new WelcomeNotification());

        $this->redirect('/home');
    }
}
