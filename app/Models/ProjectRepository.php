<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectRepository extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'created_by',
        'name',
        'slug',
        'path',
        'default_branch',
    ];

    public function project()
    {
        return $this->belongsTo(Projects::class, 'project_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
