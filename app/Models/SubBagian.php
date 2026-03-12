<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubBagian extends Model
{
    protected $fillable = ['nama_sub_bagian'];

    public function users()
    {
        return $this->hasMany(User::class, 'sub_bagian_id');
    }
}
