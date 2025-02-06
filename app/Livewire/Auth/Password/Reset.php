<?php

namespace App\Livewire\Auth\Password;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\{DB, Hash, Password};
use Illuminate\Support\Str;
use Livewire\Attributes\{Computed, Layout, Validate};
use Livewire\Component;

class Reset extends Component
{
    public ?string $token = null;

    #[Validate(['required', 'email', 'confirmed'])]
    public ?string $email = null;

    public ?string $email_confirmation = null;

    #[Validate(['required', 'confirmed'])]
    public ?string $password = null;

    public ?string $password_confirmation = null;

    public function mount(?string $token = null, ?string $email = null)
    {
        $this->token = $token;
        $this->email = request()->get('email', $email);

        if ($this->isTokenInvalid()) {
            session()->flash('status', 'Token Invalid');
            $this->redirectRoute('login');
        }
    }

    #[Layout('components.layouts.guest')]
    public function render()
    {
        return view('livewire.auth.password.reset');
    }

    private function isTokenInvalid(): bool
    {
        $dbTokens = DB::table('password_reset_tokens')->get(['token']);

        foreach ($dbTokens as $dbToken) {
            if (Hash::check($this->token, $dbToken->token)) {
                return false;
            }
        }

        return true;
    }

    #[Computed]
    public function obfuscatedEmail(): string
    {
        return obfuscate_email($this->email);
    }

    public function updatePassword()
    {
        $this->validate();

        $status = Password::reset(
            $this->only(
                'email',
                'password',
                'password_confirmation',
                'token'
            ),
            function (User $user, string $password) {
                $user->password       = $password;
                $user->remember_token = Str::random(60);
                $user->save();

                event(new PasswordReset($user));
            }
        );

        session()->flash('status', trans($status));

        if ($status !== Password::PASSWORD_RESET) {
            return;
        }

        $this->redirect(route('login'));
    }
}
