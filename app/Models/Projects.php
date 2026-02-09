<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Projects extends Model
{
    /** @use HasFactory<\Database\Factories\ProjectsFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'company_id',
    ];

    public function users(){
        return $this->belongsToMany(User::class, 'project_user', 'project_id', 'user_id');
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'project_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
