<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class AiProductReport extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['evidence'=>'array','result'=>'array','period_start'=>'datetime','period_end'=>'datetime'];
}
