<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $connection = 'mysql';

    protected $fillable = [
        'name',
        'slug',
        'monthly_price',
        'termly_price',
        'yearly_price',
        'trial_days',
        'max_students',
        'max_staff',
        'max_branches',
        'active',
        'features',
        'sort_order',
    ];

    protected $casts = [
        'features' => 'array',
        'active' => 'boolean',
        'monthly_price' => 'float',
        'termly_price' => 'float',
        'yearly_price' => 'float',
        'trial_days' => 'integer',
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class);
    }
}
