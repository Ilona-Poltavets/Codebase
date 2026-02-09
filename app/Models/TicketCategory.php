<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketCategory extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'slug',
        'sort',
        'is_active',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
