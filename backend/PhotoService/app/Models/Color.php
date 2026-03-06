<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Photo;

class Color extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'colors';
    public $timestamps = false;
    protected $fillable = [
        'id',
        'color'
    ];

   public function photos()
    {
        return $this->belongsToMany(Photo::class, 'photo_color', 'color_id', 'photo_id');
    }
   
}
