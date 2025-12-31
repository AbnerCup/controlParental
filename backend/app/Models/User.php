<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'status'];
    protected $hidden = ['password', 'remember_token'];
    protected $casts = ['email_verified_at' => 'datetime'];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }
    public function hasRole(string $key): bool
    {
        return $this->roles()->where('key', $key)->exists();
    }
    public function hasAnyRole(array $keys): bool
    {
        return $this->roles()->whereIn('key', $keys)->exists();
    }

    public function schools()
    {
        return $this->belongsToMany(School::class, 'user_schools');
    }

    public function guardian()
    {
        return $this->hasOne(Guardian::class);
    }
    public function student()
    {
        return $this->hasOne(Student::class);
    }
    public function hasPermission($permissionKey)
    {
        return $this->roles()->whereHas('permissions', function ($query) use ($permissionKey) {
            $query->where('key', $permissionKey);
        })->exists();
    }
}
