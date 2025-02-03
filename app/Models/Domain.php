<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Stancl\Tenancy\Database\Models\Domain as MDomain;
use Illuminate\Database\Eloquent\Model;


class Domain extends MDomain
{
    use HasFactory, HasUuids;
}