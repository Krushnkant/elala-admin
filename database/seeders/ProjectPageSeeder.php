<?php

namespace Database\Seeders;

use App\Models\ProjectPage;
use App\Models\User;
use App\Models\UserPermission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProjectPageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('project_pages')->truncate();

        ProjectPage::create([
            'id' => 1,
            'parent_menu' => 0,
            'label' => 'Users',
            'route_url' => null,
            'icon_class' => 'fa fa-users',
            'is_display_in_menu' => 1,
            'sr_no' => 1
        ]);

        ProjectPage::create([
            'id' => 2,
            'parent_menu' => 1,
            'label' => 'User List',
            'route_url' => 'admin.users.list',
            'is_display_in_menu' => 1,
            'inner_routes' => 'admin.users.list,admin.users.addorupdate,admin.alluserslist,admin.users.changeuserstatus,admin.users.edit,admin.users.delete,admin.users.permission,admin.users.savepermission'
        ]);

        ProjectPage::create([
            'id' => 3,
            'parent_menu' => 1,
            'label' => 'Customer List',
            'route_url' => 'admin.end_users.list',
            'is_display_in_menu' => 1,
            'inner_routes' => 'admin.end_users.list,admin.end_users.addorupdate,admin.allEnduserlist,admin.end_users.changeEnduserstatus,admin.end_users.edit,admin.end_users.delete'
        ]);

        ProjectPage::create([
            'id' => 4,
            'parent_menu' => 1,
            'label' => 'Designation List',
            'route_url' => 'admin.designation.list',
            'is_display_in_menu' => 1,
            'inner_routes' => 'admin.designation.list,admin.designation.addorupdate,admin.allDesignationlist,admin.designation.changeDesignationstatus,admin.designation.edit,admin.designation.delete'
        ]);

        ProjectPage::create([
            'id' => 5,
            'parent_menu' => 0,
            'label' => 'Category',
            'icon_class' => 'fa fa-list-alt',
            'route_url' => 'admin.categories.list',
            'is_display_in_menu' => 0,
            'inner_routes' => 'admin.categories.list,admin.categories.add,admin.categories.save,admin.allcategorylist,admin.categories.changecategorystatus,admin.categories.delete,admin.categories.edit,admin.categories.uploadfile,admin.categories.removefile,admin.categories.checkparentcat',
            'sr_no' => 2
        ]);


        $users = User::where('role',"!=",1)->get();
        $project_page_ids1 = ProjectPage::where('parent_menu',0)->where('is_display_in_menu',0)->pluck('id')->toArray();
        $project_page_ids2 = ProjectPage::where('parent_menu',"!=",0)->where('is_display_in_menu',1)->pluck('id')->toArray();
        $project_page_ids = array_merge($project_page_ids1,$project_page_ids2);
        foreach ($users as $user){
            foreach ($project_page_ids as $pid){
                $user_permission = UserPermission::where('user_id',$user->id)->where('project_page_id',$pid)->first();
                if (!$user_permission){
                    $userpermission = new UserPermission();
                    $userpermission->user_id = $user->id;
                    $userpermission->project_page_id = $pid;
                    $userpermission->save();
                }
            }
        }

    }
}
