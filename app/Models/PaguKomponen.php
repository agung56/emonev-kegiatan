<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaguKomponen extends Model
{
    protected $fillable = ['pagu_id', 'nama_komponen'];

    public function pagu()
    {
        return $this->belongsTo(Pagu::class);
    }

    public function details()
    {
        return $this->hasMany(PaguDetail::class)->orderBy('id');
    }
}
