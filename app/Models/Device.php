<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Device extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'name',
        'api_key_id',
        'key_secret',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function rawAttendanceEvents()
    {
        return $this->hasMany(RawAttendanceEvent::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function setKeySecretAttribute($value)
    {
        $this->attributes['key_secret'] = Crypt::encryptString($value);
    }

    public function getKeySecretAttribute($value)
    {
        return Crypt::decryptString($value);
    }
}
