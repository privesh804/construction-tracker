<?php

namespace Modules\Tenant\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Tenant\Database\factories\ProjectManagerFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;


class ProjectManager extends Model
{
    use HasFactory, HasUuids;

    protected $table = "project_managers";

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        "user_id",
        "project_id",
        "created_by"
    ];
    
    protected static function newFactory(): ProjectManagerFactory
    {
        //return ProjectManagerFactory::new();
    }
}
