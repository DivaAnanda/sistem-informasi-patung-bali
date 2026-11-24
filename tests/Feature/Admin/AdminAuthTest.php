<?php

namespace Tests\Feature\Admin;

use Illuminate\Support\Facades\Hash;

class AdminAuthTest extends AdminTestCase
{
    public function test_guests_are_sent_to_the_login_page(): void
    {
        $this->get('/admin')->assertRedirect('/admin/masuk');
        $this->get('/admin/masuk')->assertOk()->assertSee('Masuk admin')->assertSee('noindex', false);
    }

    public function test_wrong_password_is_rejected_without_revealing_which_field_was_wrong(): void
    {
        $this->from('/admin/masuk')
            ->post('/admin/masuk', ['email' => self::EMAIL, 'password' => 'salah'])
            ->assertRedirect('/admin/masuk')
            ->assertSessionHasErrors(['email' => 'Email atau kata sandi salah.']);

        $this->get('/admin')->assertRedirect('/admin/masuk');
    }

    public function test_correct_credentials_open_the_panel_and_logout_closes_it(): void
    {
        $this->logIn();
        $this->get('/admin')->assertOk()->assertSee('Kelola patung');

        $this->post('/admin/keluar')->assertRedirect('/admin/masuk');
        $this->get('/admin')->assertRedirect('/admin/masuk');
    }

    public function test_repeated_failures_are_throttled(): void
    {
        foreach (range(1, 5) as $attempt) {
            $this->post('/admin/masuk', ['email' => self::EMAIL, 'password' => 'salah-'.$attempt]);
        }

        $this->post('/admin/masuk', ['email' => self::EMAIL, 'password' => self::PASSWORD])
            ->assertSessionHasErrors('email');
        $this->get('/admin')->assertRedirect('/admin/masuk');
    }

    public function test_changing_the_password_ends_existing_sessions(): void
    {
        $this->logIn();
        config(['patung.admin.password_hash' => Hash::make('kata-sandi-baru-yang-panjang')]);

        $this->get('/admin')->assertRedirect('/admin/masuk');
    }

    public function test_panel_is_off_when_no_account_is_configured(): void
    {
        config(['patung.admin.password_hash' => null]);

        $this->get('/admin/masuk')->assertNotFound();
        $this->post('/admin/masuk', ['email' => self::EMAIL, 'password' => self::PASSWORD])->assertNotFound();
    }

    public function test_password_command_prints_a_hash_that_logs_in(): void
    {
        $this->artisan('patung:admin-password')
            ->expectsQuestion('Kata sandi admin baru (minimal 12 karakter)', 'kata-sandi-perintah-uji')
            ->expectsQuestion('Ulangi kata sandi', 'kata-sandi-perintah-uji')
            ->expectsOutputToContain('$2y$12$')
            ->assertSuccessful();

        $this->artisan('patung:admin-password')
            ->expectsQuestion('Kata sandi admin baru (minimal 12 karakter)', 'pendek')
            ->assertFailed();
    }
}
