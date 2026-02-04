<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Company extends Model
{
    /** @use HasFactory<\Database\Factories\CompanyFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'domain',
        'owner_id',
        'plan',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function projects()
    {
        return $this->hasMany(Projects::class);
    }

    public static function normalizeDomain(?string $domain, string $companyName): string
    {
        $baseDomain = parse_url(config('app.url'), PHP_URL_HOST);
        $value = trim((string) $domain);
        if ($value === '') {
            $value = Str::slug($companyName);
        }
        if ($baseDomain && ! str_contains($value, '.')) {
            return $value . '.' . $baseDomain;
        }

        return $value;
    }
}
