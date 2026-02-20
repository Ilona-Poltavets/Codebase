<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WikiPageVersion extends Model
{
    protected $fillable = [
        'wiki_page_id',
        'version',
        'title',
        'content',
        'edited_by',
    ];

    public function page()
    {
        return $this->belongsTo(WikiPage::class, 'wiki_page_id');
    }

    public function editor()
    {
        return $this->belongsTo(User::class, 'edited_by');
    }
}
