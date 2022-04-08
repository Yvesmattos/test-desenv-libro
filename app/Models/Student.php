<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'email', 'birth_date', 'gender'];

    public function courses(){
        return $this->belongsToMany(Course::class);
    }
}
