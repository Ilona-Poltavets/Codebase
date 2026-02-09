<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'project_id',
        'status_id',
        'priority_id',
        'category_id',
        'type_id',
        'assignee_id',
        'created_by',
        'title',
        'description',
    ];

    public function project()
    {
        return $this->belongsTo(Projects::class, 'project_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function status()
    {
        return $this->belongsTo(TicketStatus::class);
    }

    public function priority()
    {
        return $this->belongsTo(TicketPriority::class);
    }

    public function category()
    {
        return $this->belongsTo(TicketCategory::class);
    }

    public function type()
    {
        return $this->belongsTo(TicketType::class);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function comments()
    {
        return $this->hasMany(TicketComment::class);
    }

    public function activities()
    {
        return $this->hasMany(TicketActivity::class);
    }
}
