<?php

namespace App\Livewire\Auth\Password;

use Illuminate\Support\Facades\{DB, Hash};
use Livewire\Attributes\Layout;
use Livewire\Component;

class Reset extends Component
{
    public ?string $token = null;

    public function mount(string $token)
    {
        $this->token = $token;

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
}
