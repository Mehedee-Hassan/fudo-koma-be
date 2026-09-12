<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

class CreateAdmin extends Command
{
    protected $signature = 'app:create-admin {email} {--name=Administrator}';

    protected $description = 'Create an administrator with an interactively entered password';

    public function handle(): int
    {
        $data = ['email' => $this->argument('email'), 'name' => $this->option('name'), 'password' => $this->secret('Password (at least 12 characters)')];
        $validator = Validator::make($data, ['email' => 'required|email|unique:users', 'name' => 'required|string|max:100', 'password' => 'required|string|min:12']);
        if ($validator->fails()) {
            $this->error($validator->errors()->first());

            return self::FAILURE;
        }
        $user = new User($data);
        $user->role = 'admin';
        $user->save();
        $this->info('Administrator created.');

        return self::SUCCESS;
    }
}
