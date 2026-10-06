<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiQuestionEvent extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['criteria'=>'array', 'match_count'=>'integer'];
}
