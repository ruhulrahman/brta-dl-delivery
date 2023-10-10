<?php

namespace App\Http\Controllers\API\V1\Admin;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
// use Carbon\Carbon;

class AjaxController extends Controller
{
    public function get(Request $req, $name)
    {
        $user = Auth::user();
        $data['user'] = $user;
        $default_per_page = 50;
        $request = $req;
        $carbon = new Carbon();

        if ($name == 'get_auth_user') {

            $auth_user = model('User')::with('role')->find($user->id);

            $permissionIds = model('PermissionRole')::where('role_id', $user->role_id)->pluck('permission_id');
            $permission_codes = model('Permission')::whereIn('id', $permissionIds)->pluck('code');
            $auth_user->permission_codes = model('Permission')::whereIn('id', $permissionIds)->pluck('code');

            // if ($auth_user->media_id) {
            //     $media = model('Media')::find($auth_user->media_id);

            //     // $url = Storage::url('file.jpg');
            //     $auth_user->url = \Storage::url('app/public/'.$media->file);
            // }

            return response()->json([
                'success' => true,
                'message' => 'Data fetch Sucessfully!',
                'auth_user' => $auth_user,
                'user_permissions' => $permission_codes,
            ], 200);
        } else if ($name == 'get_dashboard_data') {

            $dashboardData = new \StdClass;

            $dlStockQuery = model('DlStock')::query();

            $dashboardData->received_today = (clone $dlStockQuery)->whereDate('receive_date', Carbon::now())->count();
            $dashboardData->entry_today = (clone $dlStockQuery)->whereDate('created_at', Carbon::now())->count();
            $dashboardData->delivered_today = (clone $dlStockQuery)->whereDate('delivery_date', Carbon::now())->count();
            $dashboardData->delivered_this_month = (clone $dlStockQuery)->whereMonth('delivery_date', Carbon::now())->count();
            $dashboardData->delivered_last_6th_month = (clone $dlStockQuery)->whereDate('delivery_date', '<=', Carbon::now())
            ->whereDate('delivery_date', '>=', Carbon::now()->subMonth(6))
            ->count();
            $dashboardData->delivered_this_year = (clone $dlStockQuery)->whereYear('delivery_date', Carbon::now())->count();
            $dashboardData->totol_delivered = (clone $dlStockQuery)->whereNotNull('delivery_date')->count();
            $dashboardData->totol_un_delivered = (clone $dlStockQuery)->whereNull('delivery_date')->count();
            $dashboardData->totol_dl = (clone $dlStockQuery)->count();

            $dashboardData->currentMonth = (int) date('m');

            $monthList=[];

            $count = 0;

            for($i=$dashboardData->currentMonth; $i>=1; $i--){
                // if ($count == 6) {
                //     break;
                // }

                $month = [];
                $month['month_number'] = $i;
                $month['month_name'] = date('M', mktime(0, 0, 0, $i, 1));

                array_push($monthList, $month);

                $count++;
            }

            $array_temp_id = array_column($monthList,'month_number'); // which column needed to be sorted
            array_multisort($array_temp_id, SORT_ASC, $monthList);

            $dashboardData->monthList = [];

            foreach($monthList as $item) {
                $data = [];
                $data['month_number'] = $item['month_number'];
                $data['month_name'] = $item['month_name'];
                $data['earning'] = (clone $dlStockQuery)->whereMonth('delivery_date', $item['month_number'])->count();
                array_push($dashboardData->monthList, $data);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data fetch Sucessfully!',
                'data' => $dashboardData
            ], 200);
        } else if ($name == "get_statuses_with_groups") {

            return response()->json([
                'success' => true,
                'message' => 'Data fetch Sucessfully!',
                'status_groups' => model('StatusGroup')::with('statuses')->get(),
                'statuses' => model('Status')::with('group')->orderBy('serial', 'asc')->get()
            ], 200);
        } else if ($name == "get_permission_list") {

            $query = model('Permission')::with('parent')->latest();

            if ($request->name) {
                $query = $query->where('name', 'like', "%{$request->name}%");
            }

            if ($request->type) {
                $query = $query->where('type', $request->type);
            }

            if ($request->code) {
                $query = $query->where('code', $request->code);
            }

            $list = $query->paginate($default_per_page);

            foreach ($list as $item) {
                $item->active = $item->active == 1 ? TRUE : FALSE;
            }

            if (!$list) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data Not Found!'
                ], 402);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data fetch Sucessfully!',
                'data' => $list
            ], 200);
        } else if ($name == "get_permission_parent_list") {

            $list = model('Permission')::whereNull('parent_id')->select('id as value', 'name as text', 'type')->get();
            // $list = model('Permission')::where('type', 'Page')->select('id as value', 'name as text', 'type')->get();

            if (!$list) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data Not Found!'
                ], 402);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data fetch Sucessfully!',
                'data' => $list
            ], 200);
        } else if ($name == "get_permission_parent_and_child_list") {

            $rolePermissionIds = model('PermissionRole')::where('role_id', $req->role_id)->pluck('permission_id')->toArray();

            $query = model('Permission')::query();

            $permissionParentList = (clone $query)->whereNull('parent_id')->orderBy('name', 'asc')->get();

            foreach($permissionParentList as $item) {
                $item->children_pages = (clone $query)->where([
                    'parent_id' => $item->id,
                    'type' => 'Page',
                ])->orderBy('name', 'asc')->get();

                $item->children_operations = (clone $query)->where([
                    'parent_id' => $item->id,
                    'type' => 'Feature',
                ])->orderBy('name', 'asc')->get();

                $permission_ids = (clone $query)->where([
                    'parent_id' => $item->id,
                ])->pluck('id')->toArray();

                $parent_permission_ids = [$item->id];
                $item->children_permission_ids = array_merge($permission_ids, $parent_permission_ids);

                $permissionCheck = false;

                foreach($item->children_permission_ids as $permissionId) {

                    $permissionCheck = in_array($permissionId, $rolePermissionIds);

                }

                $item->checkAll = $permissionCheck;
            }

            return response()->json([
                'success' => true,
                'message' => 'Data fetch Sucessfully!',
                'data' => $permissionParentList,
                // 'userPermissionIds' => $userPermissionIds,
            ], 200);

        } else if ($name == "get_permissions_by_role_id") {

            $validator = Validator::make($req->all(), [
                'role_id' => 'required',
            ]);

            if ($validator->fails()) {

                $errors = $validator->errors()->all();
                return response(['msg' => $errors[0]], 422);
            }


            $role = model('Role')::find($req->role_id);


            $role->role_permission_ids = model('PermissionRole')::where('role_id', $req->role_id)->pluck('permission_id');

            return response()->json([
                'success' => true,
                'message' => 'Data fetch Sucessfully!',
                'data' => $role,
            ], 200);

        } else if ($name == "get_role_list") {

            $query = DB::table('roles')->latest();

            if ($request->name) {
                $query = $query->where('name', 'like', "%{$request->name}%");
            }

            if ($request->type) {
                $query = $query->where('type', $request->type);
            }

            if ($request->code) {
                $query = $query->where('code', $request->code);
            }

            $list = $query->paginate($default_per_page);

            foreach ($list as $item) {
                $item->active = $item->active == 1 ? TRUE : FALSE;
            }

            if (!$list) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data Not Found!'
                ], 402);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data fetch Sucessfully!',
                'data' => $list
            ], 200);
        } else if ($name == 'get_user_all_list') {

            $query = DB::table('users')
                ->select('id as value', 'name as text', 'phone')
                ->orderBy('name', 'asc')
                ->get();

            if (!$query) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data Not Found!'
                ], 402);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data fetch Sucessfully!',
                'data' => $query
            ], 201);
        } else if ($name == 'get_all_role_list') {

            $query = DB::table('roles')
                ->select('id as value', 'name as text', 'active', 'code')
                ->orderBy('name', 'desc')
                ->get();

            if (!$query) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data Not Found!'
                ], 402);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data fetch Sucessfully!',
                'data' => $query
            ], 201);
        } else if ($name == 'get_user_list') {

            $query = model('User')::with('role')
                ->where('user_type', 'admin')
                ->latest();

            if ($request->name) {
                $query->where('name', 'Like', "%$request->name%");
            }

            if ($request->email) {
                $query->where('email', $request->email);
            }

            if ($request->phone) {
                $query->where('phone', $request->phone);
            }

            if ($request->role_id) {
                $query->where('phone', $request->role_id);
            }

            $list = $query->paginate($default_per_page);

            foreach ($list as $item) {
                $item->active = $item->active == 1 ? TRUE : FALSE;
            }

            if (!$list) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data Not Found!'
                ], 402);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data fetch Sucessfully!',
                'data' => $list
            ], 200);
        } else if ($name == 'get_total_active_user_list') {

            $list = model('User')::with('role')
                ->where([
                    'user_type' => 'admin',
                    'active' => 1,
                ])->orderBy('name', 'asc')
                ->get();

            foreach ($list as $item) {
                $item->value = $item->id;
                $item->label = $item->name;
                $item->active = $item->active == 1 ? TRUE : FALSE;
            }

            if (!$list) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data Not Found!'
                ], 402);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data fetch Sucessfully!',
                'data' => $list
            ], 200);
        } else if ($name == 'get_current_profile_list') {

            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data Not Found!'
                ], 402);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data fetch Sucessfully!',
                'data' => $user
            ], 200);
        } else if ($name == 'get_parent_name_wise_permission') {

            $list = model('Permission')::select('parent_id', 'type', 'name', 'code')
                ->orderBy('parent_id')
                ->orderBy('type')
                ->whereNotNull('parent_id')
                ->get();

            $arr = [];

            foreach ($list as $listItem) {
                $arr[$listItem->parent_id][$listItem->type][] = [
                    'name' => $listItem->name,
                    'code' => $listItem->code,
                ];
            }

            $collectionArr = [];

            foreach ($arr as $parentId => $typeArr) {
                $collectionArr[] = [
                    'parent_id' => $parentId,
                    'type' => key($typeArr), // Add 'type' here
                    'permission_info' => reset($typeArr), // Add 'permission_info' here
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'Data fetched Successfully!',
                'data' => $collectionArr
            ], 200);

        } else if ($name == 'get_dl_stock_data_by_search') {

            $query = model('DlStock')::with('creator', 'editor', 'delivered');

            if($req->reference_number) {
                $query->where('reference_number', $req->reference_number);
            }

            if($req->dl_number) {
                $query->where('dl_number', $req->dl_number);
            }

            if($req->entry_box_number) {
                $query->where('entry_box_number', $req->entry_box_number);
            }

            if($req->name) {
                $query->whereDate('name', 'LIKE', "%$req->name%");
            }

            if($req->father_name) {
                $query->whereDate('father_name', 'LIKE', "%$req->father_name%");
            }

            if($req->dob) {
                $query->whereDate('dob', new Carbon($req->dob));
            }

            if($req->receive_date) {
                $query->whereDate('receive_date', new Carbon($req->receive_date));
            }

            if($req->delivery_date) {
                $query->whereDate('delivery_date', new Carbon($req->delivery_date));
            }

            if($req->delivered_id) {
                $query->where('delivered_id', $req->delivered_id);
            }

            $list = $query->paginate($default_per_page);

            return response()->json([
                'success' => true,
                'message' => 'DL data fetched Successfully!',
                'data' => $list
            ], 200);
        } else if ($name == 'get_last_dl_stock_data') {

            $dlStock = model('DlStock')::latest()->first();

            return response()->json([
                'success' => true,
                'message' => 'DL data fetched Successfully!',
                'data' => $dlStock
            ], 200);
        }

        return response(['msg' => 'Sorry!, found no named argument.'], 403);
    }



    // Post functions are here .............
    public function post(Request $req, $name)
    {
        $user = Auth::user();
        $data['user'] = $user;
        $request = $req;
        $carbon = new Carbon();

        if ($name == 'store_permission_data') {

            $validate = Validator::make($request->all(), [
                'name' => 'required',
                'type' => 'required',
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validate->errors()
                ], 422);
            }

            try {

                $input = $request->all();
                $model = model('Permission')::create($input);

                return response()->json([
                    'success' => true,
                    'message' => 'Data Created Successfully',
                    'data'  => $model
                ], 201);
            } catch (\Throwable $th) {
                return response()->json([
                    'success' => false,
                    'message' => $th->getMessage()
                ], 500);
            }
        } else if ($name == 'update_permission_data') {

            $validate = Validator::make($request->all(), [
                'name' => 'required',
                'type' => 'required'
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validate->errors()
                ], 422);
            }

            $permission = model('Permission')::find($req->id);

            if (!$permission) {
                return response([
                    'success' => false,
                    'message' => 'Data not found.'
                ]);
            }

            try {

                $permission->type  = $request->type;
                $permission->name  = $request->name;
                // $permission->code  = $request->code;
                $permission->parent_id = $request->parent_id;
                $permission->save();
            } catch (\Throwable $th) {
                return response()->json([
                    'success' => false,
                    'message' => $th->getMessage()
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data updated Successfully',
                'data'    => $permission
            ], 200);
        } else if ($name == 'toggle_permission_active_status') {

            $Permission = model('Permission')::find($req->id);
            $Permission->active = $req->active == 'true' ? 1 : 0;
            $Permission->save();

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully!',
                'data'    => $Permission
            ], 200);

            // return res_msg('Permission active status updated successfully!', 200);
        } else  if ($name == 'store_role_data') {
            $validate = Validator::make($request->all(), [
                'name' => 'required',
                'type' => 'required',
                'code' => 'required'
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validate->errors()
                ], 422);
            }

            try {

                $input = $request->all();
                $model = model('Role')::create($input);

                return response()->json([
                    'success' => true,
                    'message' => 'Data Created Successfully',
                    'data'  => $model
                ], 201);
            } catch (\Throwable $th) {
                return response()->json([
                    'success' => false,
                    'message' => $th->getMessage()
                ], 500);
            }
        } else if ($name == 'update_role_data') {

            $validate = Validator::make($request->all(), [
                'name' => 'required',
                'type' => 'required',
                'code' => 'required'
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validate->errors()
                ], 422);
            }

            $role = model('Role')::find($req->id);

            if (!$role) {
                return response([
                    'success' => false,
                    'message' => 'Data not found.'
                ]);
            }

            try {
                $role->type  = $request->type;
                $role->name  = $request->name;
                $role->code  = $request->code;
                $role->save();
            } catch (\Throwable $th) {
                return response()->json([
                    'success' => false,
                    'message' => $th->getMessage()
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data update Successfully',
                'data'    => $role
            ], 200);
        } else  if ($name == 'store_role_permission_data') {
            $validate = Validator::make($request->all(), [
                'name' => 'required',
                'type' => 'required',
                'code' => 'required'
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validate->errors()
                ], 422);
            }

            try {

                $role = model('Role')::create([
                    'type' => $req->type,
                    'name' => $req->name,
                    'code' => $req->code,
                ]);

                foreach($req->role_permission_ids as $permission_id) {
                    model('PermissionRole')::create([
                        'permission_id' => (int) $permission_id,
                        'role_id' => $role->id,
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Role permission inserted Successfully',
                    'data'  => $role
                ], 201);

            } catch (\Throwable $th) {
                return response()->json([
                    'success' => false,
                    'message' => $th->getMessage()
                ], 500);
            }

        } else  if ($name == 'update_role_permission_data') {

            $validate = Validator::make($request->all(), [
                'name' => 'required',
                'type' => 'required',
                'code' => 'required'
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validate->errors()
                ], 422);
            }

            try {

                $role = model('Role')::find($req->id);
                $role->update([
                    // 'type' => $req->type,
                    'name' => $req->name,
                    // 'code' => $req->code,
                ]);

                model('PermissionRole')::where('role_id', $role->id)->delete();

                foreach($req->role_permission_ids as $permission_id) {
                    model('PermissionRole')::create([
                        'permission_id' => (int) $permission_id,
                        'role_id' => $role->id,
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Role permission updated Successfully',
                    'data'  => $role
                ], 201);

            } catch (\Throwable $th) {
                return response()->json([
                    'success' => false,
                    'message' => $th->getMessage()
                ], 500);
            }

        } else if ($name == 'toggle_role_active_status') {

            $role = model('Role')::find($req->id);
            $role->active = $req->active == 'true' ? 1 : 0;
            $role->save();

            return response()->json([
                'success' => true,
                'message' => 'Status Changed successfully!',
                'data'    => $role
            ], 200);
        } else if ($name == 'delete_role_data') {

            $roleDelete = model('Role')::find($req->id);

            if (!$roleDelete) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data not found.'
                ]);
            }
            $roleDelete->delete();
            // $role->active = $req->active == 'true' ? 1 : 0;
            // $role->save();

            return response()->json([
                'success' => true,
                'message' => 'Delete successfully!'
            ], 200);
        } else if ($name == 'delete_role_permission_data') {

            $role = model('Role')::find($req->id);

            if (!$role) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data not found.'
                ]);
            }
            model('PermissionRole')::where('role_id', $role->id)->delete();
            $role->delete();

            return response()->json([
                'success' => true,
                'message' => 'Role permission deleted successfully!'
            ], 200);

        } else if ($name == 'store_user_data') {

            $validate = Validator::make($request->all(), [
                'name' => 'required',
                'email' => 'required|unique:users,email',
                'phone' => 'required|unique:users,phone'
                // 'role_id' => 'required'
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validate->errors()
                ], 422);
            }

            try {

                $input = $request->all();
                $input['user_type'] = 'admin';
                $model = model('User')::create($input);

                return response()->json([
                    'success' => true,
                    'message' => 'Data Created Successfully',
                    'data'  => $model
                ], 201);
            } catch (\Throwable $th) {
                return response()->json([
                    'success' => false,
                    'message' => $th->getMessage()
                ], 500);
            }
        } else if ($name == 'update_user_data') {
            info($req->all());

            $id = $req['id'];

            $validate = Validator::make($req->all(), [
                'name' => 'required',
                'email' => 'required|email|unique:users,email,'.$id,
                'phone' => 'required|unique:users,phone,'.$id
                // 'role_id' => 'required'
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validate->errors()
                ], 422);
            }

            $updateUser = model('User')::find($req->id);

            if (!$updateUser) {
                return response([
                    'success' => false,
                    'message' => 'Data not found.'
                ]);
            }

            try {

                $requestAll = $request->all();
                $requestAll['name'] = $request->name;
                $requestAll['email'] = $request->email;
                $requestAll['phone'] = $request->phone;
                $requestAll['active'] = $request->active == 'true' ? 1 : 0;
                if ($req->password) {
                    $validate = validate_ajax([
                        'password' => 'required|string|min:8',
                        'c_password' => 'required|same:password',
                    ]);

                    if ($validate->fails()) {
                        return response()->json([
                            'status' => false,
                            'message' => 'validation error',
                            'errors' => $validate->errors()
                        ], 422);
                    }

                    $requestAll['password'] = bcrypt($req->password);
                }

                $updateUser->fill($requestAll);
                $updateUser->save();

            } catch (\Throwable $th) {
                return response()->json([
                    'success' => false,
                    'message' => $th->getMessage()
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data Update successfully',
                'data'    => $updateUser
            ], 200);

        } else if ($name == 'toggle_user_status') {

            $toggleUser = model('User')::find($req->id);

            if (!$toggleUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data not found.'
                ]);
            }
            $toggleUser->active = $req->active == 'true' ? 1 : 0;
            $toggleUser->save();

            return response()->json([
                'success' => true,
                'message' => 'Status Changed successfully!',
                'data'    => $toggleUser
            ], 200);
        } else if ($name == 'delete_user_data') {

            $roleDelete = model('User')::find($req->id);

            if (!$roleDelete) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data not found.'
                ]);
            }

            $roleDelete->delete();
            return response()->json([
                'success' => true,
                'message' => 'Delete successfully!'
            ], 200);
        } else if ($name == 'delete_permission_data') {

            try {
                $permissionDelete = model('Permission')::find($req->id);

                if (!$permissionDelete) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Data not found',
                    ], 422);
                }

                $permissionDelete->delete();

                return response()->json([
                    'success' => true,
                    'message' => 'Data deleted Successfully',
                    'data'  => $permissionDelete
                ], 201);
            } catch (\Throwable $th) {
                return response()->json([
                    'success' => false,
                    'message' => $th->getMessage()
                ], 500);
            }
        } else if ($name == 'change_password_data') {

            // $authUser = model('User')::find($user->id);

            // if(strcmp($request->get('new_password'), $user->password) == 0){
            //     // Current password and new password same
            //     return response([
            //         'success' => false,
            //         'message' => 'New Password cannot be same as your current password'
            //     ]);
            // }

            $validator = Validator::make(
                $request->all(),
                [
                    'old_password' => 'required',
                    'new_password' => 'required|string|min:6|required_with:confirm_password|same:confirm_password',
                    'confirm_password' => 'required|min:6',
                ],
                [
                    'new_password.min' => 'New Password Should be Minimum of 6 Character',
                    'new_password.same' => 'Password & Repeat New Password not match',
                    'confirm_password.min' => 'Repeat New Password Should be Minimum of 6 Character',
                ]
            );

            if ($validator->fails()) {
                return [
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ];
            }

            if (!Hash::check($req->old_password, $user->password)) {

                $errors = new \stdClass;
                $errors->old_password = 'Old Password does not match';

                return response([
                    'success' => false,
                    'errors' => $errors,
                    'message' => 'Old Password does not match'
                ]);
            }


            //Change Password
            $user->password = bcrypt($request->new_password);
            $user->save();

            return [
                'success' => true,
                'message' => 'Password successfully changed!'
            ];
        } else if ($name == 'profile_update_data') {

            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'phone' => 'required',
            ]);

            if ($validator->fails()) {
                return [
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ];
            }

            try {

                //    info($request->all());
                $authUser = model('User')::find($user->id);

                if ($req->photo instanceof \Illuminate\Http\UploadedFile) {

                    if ($authUser->media_id) {
                        delete_media($authUser->media_id);
                    }
                    $media = upload_media($req->photo, [
                        'model' => get_class($authUser),
                        'model_id' => $authUser->id,
                    ]);
                }

                if (isset($media)) {
                    $authUser->media_id = $media ? $media->id : NULL;
                }

                $authUser->name = $req->name;
                $authUser->phone = $req->phone;
                $authUser->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Profile updated successfully!',
                    'authUser' => $authUser
                ], 200);
            } catch (\Throwable $th) {
                return response()->json([
                    'success' => false,
                    'message' => $th->getMessage()
                ], 500);
            }
        } else if ($name == 'upload_user_photo') {

            $validator = Validator::make($request->all(), [
                'photo' => 'required',
            ]);

            if ($validator->fails()) {
                return [
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ];
            }

            try {

                //    info($request->all());
                $authUser = model('User')::find($user->id);

                if ($req->photo instanceof \Illuminate\Http\UploadedFile) {

                    if ($authUser->media_id) {
                        delete_media($authUser->media_id);
                    }

                    $media = upload_media($req->photo, [
                        'model' => get_class($authUser),
                        'model_id' => $authUser->id,
                    ]);
                }

                if (isset($media)) {
                    $authUser->media_id = $media ? $media->id : NULL;
                }

                $authUser->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Profile updated successfully!',
                    'authUser' => $authUser
                ], 200);
            } catch (\Throwable $th) {
                return response()->json([
                    'success' => false,
                    'message' => $th->getMessage()
                ], 500);
            }
        } else if ($name == 'store_dl_stock_data') {

            $validate = Validator::make($request->all(), [
                'serial_number' => 'required',
                'receive_date' => 'required',
                'receiving_box_number' => 'required',
                'reference_number' => 'required|unique:dl_stocks,reference_number',
                'entry_box_number' => 'required',
                // 'dl_number' => 'required|unique:dl_stocks,dl_number',
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validate->errors()
                ], 422);
            }

            try {

                $model = model('DlStock')::create([
                    'reference_number' => $req->reference_number,
                    'serial_number' => $req->serial_number,
                    'dl_number' => $req->dl_number,
                    'name' => $req->name,
                    'father_name' => $req->father_name,
                    'dob' => $req->dob ? new Carbon($req->dob) : NULL,
                    'blood' => $req->blood,
                    'entry_box_number' => $req->entry_box_number,
                    'receiving_box_number' => $req->receiving_box_number,
                    'receive_date' => $req->receive_date ? new Carbon($req->receive_date) : Carbon::now(),
                    'comment' => $req->comment,
                    'creator_id' => $user->id,
                    'created_at' => Carbon::now(),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Data saved successfully',
                    'data'  => $model
                ], 201);

            } catch (\Throwable $th) {
                return response()->json([
                    'success' => false,
                    'message' => $th->getMessage()
                ], 500);
            }
        } else if ($name == 'update_dl_stock_data') {

            // $id = (int) $req['id'];

            $validate = Validator::make($req->all(), [
                'serial_number' => 'required',
                'receive_date' => 'required',
                'receiving_box_number' => 'required',
                // 'reference_number' => 'required|unique:dl_stocks,reference_number,'.$id,
                'reference_number' => 'required',
                'entry_box_number' => 'required',
                // 'dl_number' => 'required|unique:dl_stocks,dl_number,'.$req->id,
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validate->errors()
                ], 422);
            }

            $dlStockById = model("DlStock")::find($req->id);
            $dlStockByReference = model("DlStock")::where('reference_number', $req->reference_number)->first();

            if ($dlStockByReference && $dlStockByReference->id != $dlStockById->id) {
                $errors = new \stdClass;
                $errors->reference_number = ["The reference number has already been taken."];

                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $errors
                ], 422);

            }

            try {

                $dlStock = model('DlStock')::find($req->id);

                $dlStock->update([
                    'reference_number' => $req->reference_number,
                    'serial_number' => $req->serial_number,
                    'dl_number' => $req->dl_number,
                    'name' => $req->name,
                    'father_name' => $req->father_name,
                    'dob' => $req->dob ? new Carbon($req->dob) : NULL,
                    'blood' => $req->blood,
                    'entry_box_number' => $req->entry_box_number,
                    'receiving_box_number' => $req->receiving_box_number,
                    'receive_date' => new Carbon($req->receive_date),
                    'comment' => $req->comment,
                    'editor_id' => $user->id,
                    'updated_at' => Carbon::now(),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Data updated successfully',
                    'data'  => $dlStock
                ], 200);

            } catch (\Throwable $th) {
                return response()->json([
                    'success' => false,
                    'message' => $th->getMessage()
                ], 500);
            }
        } else if ($name == 'delete_dl_stock_data') {

            $validate = Validator::make($request->all(), [
                'id' => 'required',
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validate->errors()
                ], 422);
            }

            try {

                $dlStock = model('DlStock')::find($req->id);

                if (!$dlStock) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Data not found.'
                    ], 422);
                }

                $dlStock->delete();

                return response()->json([
                    'success' => true,
                    'message' => 'Data deleted successfully',
                    'data'  => $dlStock
                ], 200);

            } catch (\Throwable $th) {
                return response()->json([
                    'success' => false,
                    'message' => $th->getMessage()
                ], 500);
            }
        } else if ($name == 'deliver_dl_stock_data') {

            $validate = Validator::make($request->all(), [
                'id' => 'required',
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validate->errors()
                ], 422);
            }

            try {

                $dlStock = model('DlStock')::find($req->id);

                $dlStock->update([
                    'delivery_date' => Carbon::now(),
                    'comment' => $req->comment,
                    'delivered_id' => $user->id,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Data delivered successfully',
                    'data'  => $dlStock
                ], 200);

            } catch (\Throwable $th) {
                return response()->json([
                    'success' => false,
                    'message' => $th->getMessage()
                ], 500);
            }
        } elseif($name=="multiple_dl_info_excel_file_import"){

			$validator = Validator::make($req->all(), [
				'file' => 'required|mimes:xlx,xls,xlsx'
			]);

			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				return response(['msg' => $errors[0]], 422);
			}


			// $file = $req->file('file');
			$file = $req['file'];
            info($req->all());
            info($file->get());

			$dl_stock_import = new \App\Imports\DlStockImport();
			// $dl_stock_import->import(public_path('static/dl_info_upload_file_sample.xlsx'));
			$dl_stock_import->import($file);

			$imported_list = $dl_stock_import->getImportedRows();

			return res_msg('Dl Stock Excel file imported successfully!', 201, [
				'success' => true,
				'imported_list' => $imported_list,
			]);

		} elseif($name=="mulitiple_dl_stock_store"){

			$validator = Validator::make($req->all(), [
				'data'=> 'required|array',
				'data.*.reference_number' => 'required',
				'data.*.serial_number' => 'required',
				'data.*.entry_box_number' => 'required',
				'data.*.receiving_box_number' => 'required',
			], [
				'data.*.reference_number.required' => 'Please fill the reference_number field.',
				'data.*.serial_number.required' => 'Please fill the serial_number field.',
				'data.*.entry_box_number.required' => 'Please fill the entry_box_number field.',
				'data.*.receiving_box_number.required' => 'Please fill the receiving_box_number field.',
			]);

			if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validator->errors()
                ], 422);
            }


			foreach($req->data as $item){
                $model = model('DlStock')::create([
                    'reference_number' => $item['reference_number'],
                    'serial_number' => $item['serial_number'],
                    'entry_box_number' => $item['entry_box_number'],
                    'receiving_box_number' => $item['receiving_box_number'],
                    'receive_date' => new Carbon($item['receive_date']),
                    'delivery_date' => new Carbon($item['delivery_date']),
                    'comment' => $item['comment'],
                    'creator_id' => $user->id,
                    'created_at' => Carbon::now(),
                ]);
			}

            return response()->json([
                'success' => true,
                'message' => 'Data uploaded successfully!',
            ], 200);

		} elseif($name=="check_duplicate_reference_number"){

			$validator = Validator::make($req->all(), [
				'reference_number' => 'required|unique:dl_stocks,reference_number'
			]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validator->errors()
                ], 422);
            }


            return response()->json([
                'success' => true,
                'message' => 'No duplicate found!',
            ], 200);

		}

        return response(['msg' => 'Sorry!, found no named argument.'], 403);
    }
}
