<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolCalendarDay extends Model
{
    protected $fillable = ['school_id', 'day', 'day_type', 'note'];
    protected $casts = ['day' => 'date'];
    public function school()
    {
        return $this->belongsTo(School::class);
    }
}
