<?php

use App\Livewire\Auth\Password\{Recovery, Reset};
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\{Hash, Notification};
use Livewire\Livewire;

use function Pest\Laravel\get;
use function PHPUnit\Framework\assertTrue;

test('need to receive a valid token with a combination with the email', function () {
    Notification::fake();
    $user = User::factory()->create();

    Livewire::test(Recovery::class)
        ->set('email', $user->email)
        ->call('startPasswordRecovery');

    Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) {
        get(route('password.reset', $notification->token))
            ->assertOk();

        return true;
    });

    get(route('password.reset', ['token' => 'xpto']))
        ->assertRedirect(route('login'));
});

test('test if is possible to reset the password with the given token', function () {
    Notification::fake();
    $user = User::factory()->create();

    Livewire::test(Recovery::class)
        ->set('email', $user->email)
        ->call('startPasswordRecovery');

    Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use ($user) {
        Livewire::test(Reset::class, [
            'token' => $notification->token,
            'email' => $user->email,
        ])
            ->set('email_confirmation', $user->email)
            ->set('password', 'new-password')
            ->set('password_confirmation', 'new-password')
            ->call('updatePassword')
            ->assertHasNoErrors()
            ->assertRedirect(route('login'));

        $user->refresh();

        assertTrue(Hash::check('new-password', $user->password));

        return true;
    });
});

test('checking form rules', function ($field, $value, $rule) {
    Notification::fake();
    $user = User::factory()->create();

    Livewire::test(Recovery::class)
        ->set('email', $user->email)
        ->call('startPasswordRecovery');

    Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use ($user, $field, $value, $rule) {
        Livewire::test(Reset::class, [
            'token' => $notification->token,
            'email' => $user->email,
        ])
            ->set($field, $value)
            ->call('updatePassword')
            ->assertHasErrors([$field => $rule]);

        return true;
    });
})->with([
    'email:required'     => ['field' => 'email', 'value' => '', 'rule' => 'required'],
    'email:confirmed'    => ['field' => 'email', 'value' => 'email@email.com', 'rule' => 'confirmed'],
    'email:email'        => ['field' => 'email', 'value' => 'not-an-email', 'rule' => 'email'],
    'password:required'  => ['field' => 'password', 'value' => '', 'rule' => 'required'],
    'password:confirmed' => ['field' => 'password', 'value' => 'any-password', 'rule' => 'confirmed'],
]);

test('needs to show an obfuscated email to the user', function () {
    $email           = 'fulano@example.com';
    $obfuscatedEmail = obfuscate_email($email);

    expect($obfuscatedEmail)
        ->toBe('fu****@********com');

    Notification::fake();
    $user = User::factory()->create();

    Livewire::test(Recovery::class)
        ->set('email', $user->email)
        ->call('startPasswordRecovery');

    Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use ($user) {
        Livewire::test(Reset::class, [
            'token' => $notification->token,
            'email' => $user->email,
        ])
            ->assertSet('obfuscatedEmail', obfuscate_email($user->email));

        return true;
    });
});
