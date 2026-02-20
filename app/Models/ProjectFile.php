<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectFile extends Model
{
    protected $fillable = [
        'project_id',
        'folder_id',
        'uploaded_by',
        'name',
        'version',
        'is_current',
        'disk',
        'path',
        'size',
        'mime_type',
    ];

    public function project()
    {
        return $this->belongsTo(Projects::class, 'project_id');
    }

    public function folder()
    {
        return $this->belongsTo(ProjectFolder::class, 'folder_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function comments()
    {
        return $this->hasMany(ProjectFileComment::class, 'project_file_id')->latest();
    }
}
