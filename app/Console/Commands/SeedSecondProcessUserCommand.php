<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class SeedSecondProcessUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:second-process-user 
                            {--username=srie : Username user (default: srie)} 
                            {--name=Srie : Nama user} 
                            {--email=srie@daijo.co.id : Email user} 
                            {--password=Srie1234 : Password default user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Membuat atau meng-update 1 user dengan role SECONDPROCESS';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $role = Role::firstOrCreate(['name' => 'SECONDPROCESS']);

        $username = trim($this->option('username'));
        $name     = trim($this->option('name'));
        $email    = trim($this->option('email'));
        $password = $this->option('password');

        $user = User::withTrashed()
            ->where('email', $email)
            ->orWhere('username', $username)
            ->first();

        if ($user) {
            $user->update([
                'username'   => $username,
                'name'       => $name,
                'email'      => $email,
                'password'   => Hash::make($password),
                'role_id'    => $role->id,
                'deleted_at' => null,
            ]);
        } else {
            User::create([
                'username' => $username,
                'name'     => $name,
                'email'    => $email,
                'password' => Hash::make($password),
                'role_id'  => $role->id,
            ]);
        }

        $this->info("User Second Process ({$username} / {$email}) dengan role SECONDPROCESS berhasil dibuat/di-update!");
        return Command::SUCCESS;
    }
}
