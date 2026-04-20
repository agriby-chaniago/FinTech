<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use function Pest\Laravel\actingAs;

test('profile page is displayed', function () {
    /** @var User $user */
    $user = User::factory()->create();

    $response = actingAs($user)->get('/profile');

    $response->assertOk();
});

test('profile information can be updated', function () {
    /** @var User $user */
    $user = User::factory()->create();

    $response = actingAs($user)->patch('/profile', [
        'name' => 'Test User',
        'email' => 'test@example.com',
    ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $user->refresh();

    $this->assertSame('Test User', $user->name);
    $this->assertSame('test@example.com', $user->email);
    $this->assertNull($user->email_verified_at);
});

test('email verification status is unchanged when the email address is unchanged', function () {
    /** @var User $user */
    $user = User::factory()->create();

    $response = actingAs($user)->patch('/profile', [
        'name' => 'Test User',
        'email' => $user->email,
    ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $this->assertNotNull($user->refresh()->email_verified_at);
});

test('user can delete their account', function () {
    /** @var User $user */
    $user = User::factory()->create();

    $response = actingAs($user)->delete('/profile');

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/');

    expect(Auth::check())->toBeFalse();
    $this->assertNull($user->fresh());
});
