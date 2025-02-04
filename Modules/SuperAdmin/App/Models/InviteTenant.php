<?php

namespace Modules\SuperAdmin\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InviteTenant extends Model
{
    use HasFactory, HasUuids;

    protected $table = "invite_tenants";

    protected $fillable = ['email', 'code', 'valid_upto', 'status'];
    
    // protected static function newFactory()
    // {
        
    // }
}
