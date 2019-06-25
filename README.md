# Usage
Use for Laravel website to manage page permission <br />
this package require virtualorz/sitemp and virtualorz/actionLog two packages

# Install
    composer require virtualorz/permission
    
# Config
edit config/app.php
    
    'providers' => [
        ...
        Virtualorz\Permission\PermissionServiceProvider::class
    ]
    
    'aliases' => [
        ...
        'Permission' => Virtualorz\Permission\Facades\Permission::class,
    ]
    
# Publish data
    php artisan vendor:publish --provider="Virtualorz\Permission\PermissionServiceProvider"

# Edit Config
edit config/permission_identity , <br />
for three level edit the name you want

# Edit .env
edit laravel .env file on project root <br />
add LOGINSESSION for the name store in session use for admin login on backand <br />
add LOGINSESSION_CUSTOMER for the name store in session use for customer login on frontend<br />
add LOGINPAGE for route name to login page <br />
add LOGINPAGE_CUSTOMER for route name to customer login page <br />
 
# Usage
create middleware , in middleware call 'checkLogin' , 'checkLoginCustomer', 'checkPermission' method to check login and permission <br />

# Meiddleware Example for checkLogin
    $result = Permission::checkLogin($request);
    
            if($result !== true){
                return $result;
            }
            else{
                return $next($request);
            }
            
# Meiddleware Example for checkLoginCustomer
    $result = Permission::checkLoginCustomer($request,['hash'=>'0098765']);
    
            if($result !== true){
                return $result;
            }
            else{
                return $next($request);
            }

# Middleware Example for checkPermission
    $result = Permission::checkPermission();
    
            if($result){
                return $next($request);
            }

# Method

###### checkLogin($request)
`check admin is login or not, if login return true, if not redirect to login page set in env('LOGINPAGE')`

###### checkLoginCustomer($request,$parameter)
`check customer is login or not, if login return true, if not redirect to login page set in env('LOGINPAGE')`

###### checkPermission
`check admin has permission to use this page or not, if yes returen true, if not return abort 403 page`

###### groupList($keyword = null,$page = 15)
`return the permission group data, @$keyword for search group name, @$page for paginate per page`

###### groupAdd($column)
`return the result message for add permission group data to database, @$column['name'] for group name, @column['identity'] for identity id set in config, @$column['permission'] for permission item as array`

###### getGroupItem($id)
`retmurn the group item @$id for group item id`

###### groupEdit($column)
`return the result message for edit permission group data to database, @$column['name'] for group name, @column['identity'] for identity id set in config, @$column['permission'] for permission item as array, @$cloumn['id'] for permission edit primary id`

###### groupDelete($column)
`return the result message for delete permission group data from database, @$cloumn['id'] for permission delete primary id`

###### permissionList($keyword = null,$page = 15)
`return the permission data, @$keyword for search member name, @$page for paginate per page`

###### getPermissionItem($id)
`retmurn the member setted permission and all grops data as Array`

###### permissionEdit($column)
`return the result message for edit permission data to database, @$column['select'] for group selected as array, @$cloumn['id'] for member id`

###### permissionDelete($column)
`return the result message for delete permission data from database, @$cloumn['id'] for member id`
