<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Models\Photo;

class Tag extends Model
{
    use HasUuids;

    protected $table = 'tags';
    protected $fillable = ['id', 'name'];
    public $incrementing = false;
    protected $keyType = 'string';

    public function photos()
    {
        return $this->belongsToMany(Photo::class, 'photo_tag');
    }
}
