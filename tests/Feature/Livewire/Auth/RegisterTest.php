<?php

use App\Livewire\Auth\Register;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\{assertDatabaseCount, assertDatabaseHas};

it('should render the component', function () {
    Livewire::test(Register::class)
        ->assertOk();
});

it('should be able to register a new user in the system', function () {
    Livewire::test(Register::class)
        ->set('name', 'John Doe')
        ->set('email', 'john@doe.com')
        ->set('email_confirmation', 'john@doe.com')
        ->set('password', 'password')
        ->call('submit')
        ->assertHasNoErrors();

    assertDatabaseHas('users', [
        'name'  => 'John Doe',
        'email' => 'john@doe.com',
    ]);

    assertDatabaseCount('users', 1);

    expect(auth()->check())
        ->and(auth()->user())
        ->id->toBe(User::first()->id);
});

test('validation rules', function ($f) {
    Livewire::test(Register::class)
        ->set($f->field, $f->value)
        ->call('submit')
        ->assertHasErrors([$f->field => $f->rule]);
})->with([
    'name::required'     => (object) ['field' => 'name', 'value' => '', 'rule' => 'required'],
    'name::max:255'      => (object) ['field' => 'name', 'value' => str_repeat('*', 256), 'rule' => 'max'],
    'email::required'    => (object) ['field' => 'email', 'value' => '', 'rule' => 'required'],
    'email::email'       => (object) ['field' => 'email', 'value' => 'not-an-email', 'rule' => 'email'],
    'email::max:255'     => (object) ['field' => 'email', 'value' => str_repeat('*' . '@doe.com', 256), 'rule' => 'max'],
    'email::confirmed'   => (object) ['field' => 'email', 'value' => 'john@doe.com', 'rule' => 'confirmed'],
    'password::required' => (object) ['field' => 'password', 'value' => '', 'rule' => 'required'],
]);

test('email should be unique', function () {
    User::factory()->create(['email' => 'john@doe.com']);

    Livewire::test(Register::class)
        ->set('email', 'john@doe.com')
        ->set('email_confirmation', 'john@doe.com')
        ->call('submit')
        ->assertHasErrors(['email' => 'unique']);
});
