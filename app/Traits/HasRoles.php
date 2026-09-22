<?php

namespace App\Traits;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Provides role and permission checking capabilities to the User model.
 *
 * Roles are loaded eagerly with their permissions to avoid N+1 queries.
 * Permission checks traverse: User → Roles → Permissions.
 */
trait HasRoles
{
    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_role');
    }

    // ──────────────────────────────────────────────
    // Role Checks
    // ──────────────────────────────────────────────

    public function hasRole(string $roleName): bool
    {
        return $this->roles->contains('name', $roleName);
    }

    public function hasAnyRole(array $roleNames): bool
    {
        return $this->roles->whereIn('name', $roleNames)->isNotEmpty();
    }

    // ──────────────────────────────────────────────
    // Permission Checks
    // ──────────────────────────────────────────────

    /**
     * Check if user has a specific permission through any of their roles.
     */
    public function hasPermission(string $permissionName): bool
    {
        if ($this->hasRole('admin')) {
            return true;
        }

        return $this->roles
            ->flatMap(fn (Role $role) => $role->permissions)
            ->contains('name', $permissionName);
    }

    public function hasAnyPermission(array $permissionNames): bool
    {
        if ($this->hasRole('admin')) {
            return true;
        }

        $userPermissions = $this->roles
            ->flatMap(fn (Role $role) => $role->permissions)
            ->pluck('name');

        return $userPermissions->intersect($permissionNames)->isNotEmpty();
    }

    // ──────────────────────────────────────────────
    // Role Assignment
    // ──────────────────────────────────────────────

    public function assignRole(string $roleName): void
    {
        $role = Role::where('name', $roleName)->firstOrFail();
        $this->roles()->syncWithoutDetaching([$role->id]);
    }

    public function removeRole(string $roleName): void
    {
        $role = Role::where('name', $roleName)->first();

        if ($role) {
            $this->roles()->detach($role->id);
        }
    }

    public function syncRoles(array $roleIds): void
    {
        $this->roles()->sync($roleIds);
    }

    // ──────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────

    /**
     * Get all permission names for this user (across all roles).
     */
    public function getAllPermissionNames(): array
    {
        if ($this->hasRole('admin')) {
            return Permission::pluck('name')->all();
        }

        return $this->roles
            ->flatMap(fn (Role $role) => $role->permissions)
            ->pluck('name')
            ->unique()
            ->values()
            ->all();
    }
}
