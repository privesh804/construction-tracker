<?php

namespace Modules\Tenant\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Tenant\Database\factories\ProjectAssigneeFactory;

class ProjectAssignee extends Model
{
    use HasFactory;

    protected $table = "project_assignee";

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        "user_id",
        "project_id",
        "created_by"
    ];
    
    protected static function newFactory(): ProjectAssigneeFactory
    {
        //return ProjectAssigneeFactory::new();
    }
}
