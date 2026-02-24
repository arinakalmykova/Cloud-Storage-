<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Photo;

class Folder extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'folders';
    protected $fillable = [
        'id',
        'user_id',
        'name',
        'created_at',
        'updated_at'
    ];

    public function photos(): HasMany
    {
        return $this->hasMany(Photo::class, 'folder_id', 'id');
    }
}
