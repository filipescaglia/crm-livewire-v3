<?php

use App\Livewire\Auth\Login;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(Login::class)
        ->assertOk();
});

it('should be able to login', function () {
    $user = User::factory()->create([
        'email'    => 'john@doe.com',
        'password' => 'password',
    ]);

    Livewire::test(Login::class)
        ->set('email', 'john@doe.com')
        ->set('password', 'password')
        ->call('tryToLogin')
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard'));

    expect(Auth::check())->toBeTrue()
        ->and(Auth::user())->id->toBe($user->id);
});

it('should make sure to inform the user an error when email or password doesnt work', function () {
    Livewire::test(Login::class)
        ->set('email', 'john@doe.com')
        ->set('password', 'password')
        ->call('tryToLogin')
        ->assertHasErrors(['invalidCredentials'])
        ->assertSee(trans('auth.failed'));
});

it('should make sure that the rate limiting is blocking after 5 attempts', function () {
    $user = User::factory()->create();

    for ($i = 0; $i < 5; $i++) {
        Livewire::test(Login::class)
            ->set('email', $user->email)
            ->set('password', 'xpto')
            ->call('tryToLogin')
            ->assertHasErrors(['invalidCredentials']);
    }
    Livewire::test(Login::class)
        ->set('email', $user->email)
        ->set('password', 'xpto')
        ->call('tryToLogin')
        ->assertHasErrors(['rateLimiter']);
});
