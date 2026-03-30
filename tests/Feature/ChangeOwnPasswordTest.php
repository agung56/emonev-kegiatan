<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ChangeOwnPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_open_change_password_page(): void
    {
        $user = User::factory()->create([
            'password' => 'password-lama',
        ]);

        $response = $this->actingAs($user)->get(route('password.edit'));

        $response->assertOk();
        $response->assertSee('Ganti Password');
    }

    public function test_authenticated_user_can_change_password_with_valid_current_password(): void
    {
        $user = User::factory()->create([
            'password' => 'password-lama',
        ]);

        $response = $this->actingAs($user)->from(route('password.edit'))->put(route('password.update'), [
            'current_password' => 'password-lama',
            'password' => 'PasswordBaru123',
            'password_confirmation' => 'PasswordBaru123',
        ]);

        $response->assertRedirect(route('password.edit'));
        $response->assertSessionHas('success');

        $this->assertTrue(Hash::check('PasswordBaru123', $user->fresh()->password));
    }

    public function test_authenticated_user_cannot_change_password_with_wrong_current_password(): void
    {
        $user = User::factory()->create([
            'password' => 'password-lama',
        ]);

        $response = $this->actingAs($user)->from(route('password.edit'))->put(route('password.update'), [
            'current_password' => 'salah-total',
            'password' => 'PasswordBaru123',
            'password_confirmation' => 'PasswordBaru123',
        ]);

        $response->assertRedirect(route('password.edit'));
        $response->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('password-lama', $user->fresh()->password));
    }
}
