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

    public function folders()
    {
        return $this->hasMany(ProjectFolder::class, 'project_id');
    }

    public function files()
    {
        return $this->hasMany(ProjectFile::class, 'project_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function repositories()
    {
        return $this->hasMany(ProjectRepository::class, 'project_id');
    }

    public function wikiPages()
    {
        return $this->hasMany(WikiPage::class, 'project_id');
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class, 'project_id');
    }
}
