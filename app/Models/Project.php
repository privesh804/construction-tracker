<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'name',
        'address',
        'nature_of_project',
        'client_name',
        'contractor_name',
        'total_amount'
    ];

    public function sections()
    {
        return $this->hasMany(Section::class);
    }
}
