<?php

namespace Virtualorz\Permission;

use Illuminate\Database\Eloquent\Model;

class system_permission_group extends Model
{
    //
    protected $table = 'system_permission_group';

    protected $fillable = [
        'name',
        'identity',
        'permission',
        'enable',
        'create_member_id',
        'update_member_id'
    ];

    protected $guarded = [
        'id'
    ];

    /*
     * 已套用人員
     */
    public function permission_use(){
        return $this->hasMany('\Virtualorz\Permission\system_permission','permission_group_id','id');
    }
}
