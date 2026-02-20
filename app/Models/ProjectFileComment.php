<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectFileComment extends Model
{
    protected $fillable = [
        'project_file_id',
        'user_id',
        'body',
    ];

    public function file()
    {
        return $this->belongsTo(ProjectFile::class, 'project_file_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
