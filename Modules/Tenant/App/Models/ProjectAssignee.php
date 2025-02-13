<?php

namespace Modules\Tenant\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Tenant\Database\factories\ProjectAssigneeFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;


class ProjectAssignee extends Model
{
    use HasFactory, HasUuids;

    protected $table = "project_assignees";

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
