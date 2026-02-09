<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectFolder extends Model
{
    protected $fillable = [
        'project_id',
        'parent_id',
        'name',
        'path',
    ];

    public function project()
    {
        return $this->belongsTo(Projects::class, 'project_id');
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function files()
    {
        return $this->hasMany(ProjectFile::class, 'folder_id');
    }
}
