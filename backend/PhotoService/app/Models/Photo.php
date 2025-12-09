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
        'user_id',
        'url',
        'compressed_url',
        'status'
    ];
}