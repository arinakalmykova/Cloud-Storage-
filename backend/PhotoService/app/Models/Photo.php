<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Photo extends Model 
{
    protected $table = 'photo';
    protected $primaryKey = 'id';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'filename',
        'description',
        'format',
        'size',
        'url',
        'user_id',
        'status'
    ];
}