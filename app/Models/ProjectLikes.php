<?php

namespace Helix\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectLikes extends Model
{
    use SoftDeletes;

    protected $table = 'project_likes';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'user_id',
        'project_id',
        'type'
    ];
}
