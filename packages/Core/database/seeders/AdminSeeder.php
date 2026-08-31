<?php

namespace Packages\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class AdminSeeder extends Seeder
{
    /**
     * Default Super Admin credentials (full access).
     */
    protected string $email = 'admin@nguyenkhoi.dev';

    protected string $password = '123456789';

    protected string $name = 'Admin';

    protected string $roleName = 'Super Admin';

    protected string $roleSlug = 'super-admin';

    /**
     * Default Coordinator credentials (VMTA Phase 1).
     * Limited scope: content/catalog/inquiry/media. No users/roles/settings.
     */
    protected string $coordinatorEmail = 'coordinator@nguyenkhoi.dev';

    protected string $coordinatorPassword = '123456789';

    protected string $coordinatorName = 'Coordinator';

    protected string $coordinatorRoleName = 'Coordinator';

    protected string $coordinatorRoleSlug = 'coordinator';

    protected array $coordinatorPermissions = [
        'content.index', 'content.create', 'content.edit', 'content.delete', 'content.publish',
        'catalog.index', 'catalog.create', 'catalog.edit', 'catalog.delete',
        'inquiry.index', 'inquiry.view', 'inquiry.update', 'inquiry.reply', 'inquiry.export',
        'media.index', 'media.create', 'media.edit', 'media.delete',
        'newsletter.index', 'newsletter.export', 'newsletter.delete',
    ];

    public function run(): void
    {
        $hasSlug = Schema::hasColumn('roles', 'slug');
        $hasGuardName = Schema::hasColumn('roles', 'guard_name');
        $hasRoleId = Schema::hasColumn('users', 'role_id');
        $hasSuperUser = Schema::hasColumn('users', 'is_super_user');
        $hasPivot = Schema::hasTable('model_has_roles');

        $superRoleId = $this->findOrCreateRole(
            $hasSlug,
            $hasGuardName,
            $this->roleName,
            $this->roleSlug,
            ['*'],
        );
        $superUserId = $this->findOrCreateUser(
            $this->email,
            $this->name,
            $this->password,
            $hasRoleId,
            $hasSuperUser,
            $superRoleId,
            isSuper: true,
        );
        $this->assignRolePivot($hasPivot, $superRoleId, $superUserId);

        $coordRoleId = $this->findOrCreateRole(
            $hasSlug,
            $hasGuardName,
            $this->coordinatorRoleName,
            $this->coordinatorRoleSlug,
            $this->coordinatorPermissions,
        );
        $coordUserId = $this->findOrCreateUser(
            $this->coordinatorEmail,
            $this->coordinatorName,
            $this->coordinatorPassword,
            $hasRoleId,
            $hasSuperUser,
            $coordRoleId,
            isSuper: false,
        );
        $this->assignRolePivot($hasPivot, $coordRoleId, $coordUserId);

        $this->command->info('Super Admin:  '.$this->email.' / '.$this->password);
        $this->command->info('Coordinator:  '.$this->coordinatorEmail.' / '.$this->coordinatorPassword);
    }

    protected function findOrCreateRole(
        bool $hasSlug,
        bool $hasGuardName,
        string $name,
        string $slug,
        array $permissions,
    ): ?int {
        if ($hasSlug) {
            $role = DB::table('roles')->where('slug', $slug)->first();
            if (! $role) {
                return DB::table('roles')->insertGetId([
                    'name' => $name,
                    'slug' => $slug,
                    'permissions' => json_encode($permissions),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            // Refresh permissions in case the catalog grew between seed runs.
            DB::table('roles')->where('id', $role->id)->update([
                'permissions' => json_encode($permissions),
                'updated_at' => now(),
            ]);

            return $role->id;
        }

        if ($hasGuardName) {
            $role = DB::table('roles')->where('name', $name)->first();
            if (! $role) {
                return DB::table('roles')->insertGetId([
                    'name' => $name,
                    'guard_name' => 'web',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return $role->id;
        }

        return DB::table('roles')->first()?->id;
    }

    protected function findOrCreateUser(
        string $email,
        string $name,
        string $password,
        bool $hasRoleId,
        bool $hasSuperUser,
        ?int $roleId,
        bool $isSuper,
    ): int {
        $user = DB::table('users')->where('email', $email)->first();

        if ($user) {
            $update = [
                'password' => Hash::make($password),
                'updated_at' => now(),
            ];
            if ($hasRoleId && $roleId) {
                $update['role_id'] = $roleId;
            }
            if ($hasSuperUser) {
                $update['is_super_user'] = $isSuper;
            }
            DB::table('users')->where('id', $user->id)->update($update);

            return $user->id;
        }

        $insert = [
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
        if ($hasRoleId && $roleId) {
            $insert['role_id'] = $roleId;
        }
        if ($hasSuperUser) {
            $insert['is_super_user'] = $isSuper;
        }

        return DB::table('users')->insertGetId($insert);
    }

    protected function assignRolePivot(bool $hasPivot, ?int $roleId, int $userId): void
    {
        if (! $hasPivot || ! $roleId) {
            return;
        }

        DB::table('model_has_roles')->updateOrInsert(
            [
                'model_type' => 'Packages\\Core\\Src\\Models\\User',
                'model_id' => $userId,
            ],
            ['role_id' => $roleId],
        );
    }
}
