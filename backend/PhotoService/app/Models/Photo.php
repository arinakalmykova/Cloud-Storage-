<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Tag;
use App\Models\Folder as FolderModel;

class Photo extends Model 
{
    protected $table = 'photo';
    protected $primaryKey = 'id';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'file_name',
        'description',
        'format',
        'size',
        'url',
        'user_id',
        'status',
        'dominant_color',
        'folder_id'
    ];

   public function tags(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'photo_tag', 'photo_id', 'tag_id');
    }

    public function folder(): \Illuminate\Database\Eloquent\Relations\BelongsTo
{
    return $this->belongsTo(FolderModel::class);
}

}