<?php

namespace Virtualorz\Permission;

use ActionLog;

use DB;
use Virtualorz\Permission\system_permission_group;
use Virtualorz\Permission\system_permission;
use Session;
use Route;
use Validator;

class Permission
{

    protected static $message= [
        'status' => 0,
        'status_string' => '',
        'message' => '',
        'data' => []
    ];

    public function checkLogin($request){

        if(session(env('LOGINSESSION','virtualorz_default')) == null)
        {
            session(['return_url'=> $request->fullUrl()]);
            return redirect(env('LOGINPAGE','login'));
        }

        return true;

    }

    public function checkLoginCustomer($request,$parameter){

        if(session(env('LOGINSESSION_CUSTOMER','virtualorz_customer_default')) == null && session(env('LOGINSESSION','virtualorz_default')) == null)
        {
            session(['return_url'=> $request->fullUrl()]);
            return redirect(env('LOGINPAGE_CUSTOMER','login'),$parameter);
        }

        return true;

    }

    public function checkPermission(){

        $checkPermission = Route::currentRouteName();
        if(!isset(Route::current()->action['name'])){
            $checkPermission = Route::current()->action['parent'];
        }
        if(!in_array($checkPermission,session(env('LOGINSESSION','virtualorz_default').'.permission'))){
            abort(403, '沒有使用權限');
        }

        return true;

    }

    public function groupList($keyword = null,$page = 15){

        $group = system_permission_group::with('permission_use')
            ->whereNotNull('id');

        if(!is_null($keyword)){
            $group->where(function($query)use($keyword){
                $query->orWhere('name','LIKE','%'.$keyword.'%');
            });
        }

        $group = $group->orderBy('created_at','DESC')->paginate($page);

        return $group;
    }

    public function groupAdd($column){

        $validator = Validator::make($column,[
            'name' =>'required|max:24',
            'identity' => 'required|Integer'
        ]);

        $message= [
            'status' => 0,
            'status_string' => '',
            'message' => ''
        ];

        if($validator->fails()){
            $error = $validator->errors()->toArray();
            $error = reset($error);

            $message['status_string'] = '驗證錯誤';
            $message['message'] = $error[0];
        }

        DB::beginTransaction();
        try {

            $permission = [];
            $permissionArray = json_decode($column['permission'], true);
            foreach ($permissionArray as $k => $v) {
                array_push($permission, $v['id']);
            }


            $system_permission_group = system_permission_group::create([
                'name' => $column['name'],
                'permission' => json_encode($permission),
                'identity' => $column['identity'],
                'enable' => $column['enable'],
                'create_member_id' => session(env('LOGINSESSION','virtualorz_default'))['login_user']['id'],
                'update_member_id' => session(env('LOGINSESSION','virtualorz_default'))['login_user']['id'],
            ]);

            ActionLog::save(Route::getCurrentRoute()->action['parent'],1,'新增權限群組',$system_permission_group);

            DB::commit();

            self::$message['status'] = 1;
            self::$message['status_string'] = '新增成功';
            self::$message['data']['redirectURL'] = Route(Route::getCurrentRoute()->action['parent']);
        }catch (\Exception $ex){
            DB::rollBack();

            self::$message['status_string'] = '錯誤';
            self::$message['message'] = "資料庫錯誤 : ".$ex->getMessage();
        }

        return self::$message;

    }

    public function groupEdit($column){

        $validator = Validator::make($column,[
            'name' =>'required|max:24',
            'identity' => 'required|Integer'
        ]);

        $message= [
            'status' => 0,
            'status_string' => '',
            'message' => ''
        ];

        if($validator->fails()){
            $error = $validator->errors()->toArray();
            $error = reset($error);

            $message['status_string'] = '驗證錯誤';
            $message['message'] = $error[0];
        }

        DB::beginTransaction();
        try {
            $permission = [];
            $permissionArray = json_decode($column['permission'], true);
            foreach ($permissionArray as $k => $v) {
                array_push($permission, $v['id']);
            }
            $data = system_permission_group::findOrFail($column['id']);

            $data->name = $column['name'];
            $data->permission = json_encode($permission);
            $data->identity = $column['identity'];
            $data->enable = $column['enable'];
            $data->update_member_id = session(env('LOGINSESSION','virtualorz_default'))['login_user']['id'];

            ActionLog::save(Route::getCurrentRoute()->action['parent'],2,'編輯權限群組',$data);

            $data->save();

            DB::commit();

            self::$message['status'] = 1;
            self::$message['status_string'] = '編輯成功';
            self::$message['data']['redirectURL'] = Route(Route::getCurrentRoute()->action['parent']);

        }catch (\Exception $ex){
            DB::rollBack();

            self::$message['status_string'] = '錯誤';
            self::$message['message'] = "資料庫錯誤 : ".$ex->getMessage();
        }

        return self::$message;

    }

    public function groupDelete($column){

        DB::beginTransaction();
        try {
            $system_permission_group = system_permission_group::find($column['id']);

            $system_permission_group->delete();

            ActionLog::save(Route::getCurrentRoute()->action['parent'],0,'刪除權限群組',$system_permission_group);

            DB::commit();

            self::$message['status'] = 1;
            self::$message['status_string'] = '刪除成功';
            self::$message['data']['redirectURL'] = Route(Route::getCurrentRoute()->action['as']);

        }catch (\Exception $ex){
            DB::rollBack();

            self::$message['status_string'] = '錯誤';
            self::$message['message'] = "資料庫錯誤 : ".$ex->getMessage();
        }

        return self::$message;

    }

    public function permissionList($keyword = null,$page = 15){

        //先取得有設定權限的人員
        $member_permissioned_array = [];
        $member_permissioned = system_permission::with('group')
            ->orderBy('created_at','DESC')
            ->get();
        foreach($member_permissioned as $k=>$v){
            if(!isset($member_permissioned_array[$v->member_id])){
                $member_permissioned_array[$v->member_id] = [];
            }
            array_push($member_permissioned_array[$v->member_id],$v);
        }

        $identity = Config('permission_identity.identity');

        //人員處理
        $data = [];
        $member_all = session(env('LOGINSESSION','virtualorz_default').'.member');
        foreach($member_all as $k=>$v){
            if($v['name'] == $v['org_name']){
                $member_all[$k]['group_name'] = '';
                $member_all[$k]['identity_name'] = '';
                $member_all[$k]['created_at'] = '';
                if(array_key_exists($v['id'],$member_permissioned_array)){
                    foreach($member_permissioned_array[$v['id']] as $k1=>$v1){
                        $member_all[$k]['group_name'] .= $v1->group['name'].' ';
                        $member_all[$k]['identity_name'] .= $identity[$v1->group['identity']].' ';
                        $member_all[$k]['created_at'] = $v1->created_at;
                    }
                }
                if(!is_null($keyword)){
                    //有傳關鍵字，則符合人名才儲存
                    if($member_all[$k]['name'] == $keyword){
                        array_push($data,$member_all[$k]);
                    }
                }
                else{
                    //沒有關鍵字，則全部儲存
                    array_push($data,$member_all[$k]);
                }

            }
        }

        return $data;

    }

    public function permissionEdit($column){

        DB::beginTransaction();
        try {

            system_permission::where('member_id', $column['id'])->delete();

            if ($column['select'] != null) {
                foreach ($column['select'] as $k => $v) {
                    $system_permission = system_permission::create([
                        'permission_group_id' => $v,
                        'member_id' => $column['id'],
                        'created_at' => date('Y-m-d H:i:s'),
                        'create_member_id' => session(env('LOGINSESSION','virtualorz_default'))['login_user']['id']
                    ]);

                    ActionLog::save(Route::getCurrentRoute()->action['parent'],2,'編輯使用者權限',$system_permission);
                }
            }

            DB::commit();

            self::$message['status'] = 1;
            self::$message['status_string'] = '編輯成功';
            self::$message['data']['redirectURL'] = Route(Route::getCurrentRoute()->action['parent']);
        }catch (\Exception $ex){
            DB::rollBack();

            self::$message['status_string'] = '錯誤';
            self::$message['message'] = "資料庫錯誤 : ".$ex->getMessage();
        }

        return self::$message;

    }

    public function permissionDelete($column){

        DB::beginTransaction();
        try {

            $system_permission = system_permission::where('member_id', $column['id']);
            $system_permission->delete();

            ActionLog::save(Route::getCurrentRoute()->action['parent'],0,'刪除使用者權限',null,$column['id']);

            DB::commit();

            self::$message['status'] = 1;
            self::$message['status_string'] = '刪除成功';
            self::$message['data']['redirectURL'] = Route(Route::getCurrentRoute()->action['as']);

        }catch (\Exception $ex){
            DB::rollBack();

            self::$message['status_string'] = '錯誤';
            self::$message['message'] = "資料庫錯誤 : ".$ex->getMessage();
        }

        return self::$message;

    }
}
