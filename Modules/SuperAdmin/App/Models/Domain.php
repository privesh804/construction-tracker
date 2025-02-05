<?php

namespace Modules\SuperAdmin\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\SuperAdmin\Database\factories\DomainFactory;

class Domain extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];
    
    protected static function newFactory(): DomainFactory
    {
        //return DomainFactory::new();
    }
}
