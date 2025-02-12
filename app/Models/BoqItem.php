<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BoqItem extends Model
{
    protected $fillable = [
        'section_id',
        'item_no',
        'description',
        'quantity',
        'unit',
        'rate',
        'amount',
        'time_related_charges',
        'fixed_charges'
    ];

    public function section()
    {
        return $this->belongsTo(Section::class);
    }
}
