<?php

namespace LaraMod\Admin\Blog\Models;

use LaraMod\Admin\Core\Scopes\AdminCoreOrderByCreatedAtScope;
use Illuminate\Database\Eloquent\Model;
use LaraMod\Admin\Core\Traits\HelpersTrait;

class Tags extends Model
{
    protected $table = 'blog_tags';
    public $timestamps = true;

    use HelpersTrait;

    protected $guarded = ['id'];
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'name'
    ];

    public function posts(){
        return $this->belongsToMany(Posts::class,'blog_post_tag','tag_id', 'post_id');
    }

    protected function bootIfNotBooted()
    {
        parent::boot();
        static::addGlobalScope(new AdminCoreOrderByCreatedAtScope());
    }
}
