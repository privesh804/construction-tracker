<?php

namespace Modules\Tenant\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Tenant\Database\factories\ProjectFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Modules\Tenant\App\Models\ProjectAssignee;

class Project extends Model
{
    use HasFactory, HasUuids;

    protected $table = "projects";

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        "title",
        "description",
        "address",
        "nature",
        "start",
        "end",
        "budget",
        "status",
        "created_by"
    ];
    
    function assignee(){
        $this->hasMany(ProjectAssignee::class, 'project_id');
    }
}
