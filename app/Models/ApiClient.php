<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiClient extends Model
{
    protected $table = 'api_clients';

    protected $fillable = [
        'school_id',
        'name',
        'type',
        'key_id',
        'key_secret_hash',
        'key_secret_enc',
        'active',
        'last_used_at',
    ];

    protected $casts = [
        'active' => 'boolean',
        'last_used_at' => 'datetime',
    ];

    public function school()
    {
        return $this->belongsTo(\App\Models\School::class);
    }
}
