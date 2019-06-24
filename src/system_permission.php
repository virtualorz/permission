<?php

namespace Virtualorz\Permission;

use Illuminate\Database\Eloquent\Model;

class system_permission extends Model
{
    //
    public $timestamps = false;

    protected $table = 'system_permission';

    protected $fillable = [
        'permission_group_id',
        'member_id',
        'created_at',
        'create_member_id'
    ];

    /*
     * 設定群組
     */
    public function group(){
        return $this->hasOne('\Virtualorz\Permission\system_permission_group','id','permission_group_id');
    }
}
