<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('patung:admin-password')]
#[Description('Buat hash kata sandi untuk akun admin (isi ADMIN_PASSWORD_HASH di Vercel)')]
class MakeAdminPassword extends Command
{
    private const MIN_LENGTH = 12;

    public function handle(): int
    {
        $password = (string) $this->secret('Kata sandi admin baru (minimal '.self::MIN_LENGTH.' karakter)');

        if (mb_strlen($password) < self::MIN_LENGTH) {
            $this->components->error('Kata sandi terlalu pendek.');

            return self::FAILURE;
        }

        if ($password !== (string) $this->secret('Ulangi kata sandi')) {
            $this->components->error('Kedua kata sandi tidak sama.');

            return self::FAILURE;
        }

        $this->newLine();
        $this->line(password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]));
        $this->newLine();
        $this->components->info('Salin baris di atas ke variabel ADMIN_PASSWORD_HASH (Vercel, Production, Sensitive). Kata sandinya sendiri tidak disimpan di mana pun.');

        return self::SUCCESS;
    }
}
