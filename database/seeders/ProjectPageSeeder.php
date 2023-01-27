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

        ProjectPage::create([
            'id' => 6,
            'parent_menu' => 0,
            'label' => 'Experience',
            'icon_class' => 'fa fa-level-up',
            'route_url' => 'admin.experience.list',
            'is_display_in_menu' => 0,
            'inner_routes' => 'admin.experience.list,admin.allcategorylist,admin.experience.save,admin.experience.edit,admin.experience.changeexperiencestatus,admin.experience.delete,admin.experience.removefile,admin.experience.change_experience_status,admin.experience.uploadfile',
            'sr_no' => 3
        ]);

        ProjectPage::create([
            'id' => 7,
            'parent_menu' => 0,
            'label' => 'Languages',
            'icon_class' => 'fa fa-language',
            'route_url' => 'admin.languages.list',
            'is_display_in_menu' => 0,
            'inner_routes' => 'admin.languages.list,admin.languages.addorupdate,admin.alllanguageslist,admin.languages.edit,admin.languages.delete,admin.attributes.chageattributestatus',
            'sr_no' => 4
        ]);

        ProjectPage::create([
            'id' => 8,
            'parent_menu' => 0,
            'label' => 'Age Group',
            'route_url' => 'admin.agegroups.list',
            'is_display_in_menu' => 0,
            'inner_routes' => 'admin.agegroups.list,admin.agegroup.addorupdate,admin.allagegroupslist,admin.agegroup.changeagegroupstatus,admin.agegroup.edit,admin.agegroup.delete',
            'icon_class' => 'fa fa-child',
            'sr_no' => 5
        ]);

        ProjectPage::create([
            'id' => 9,
            'parent_menu' => 0,
            'label' => 'Cancellation Policy',
            'route_url' => 'admin.policy.list',
            'is_display_in_menu' => 0,
            'inner_routes' => 'admin.policy.list,admin.policy.addorupdate,admin.allpolicylist,admin.policy.chagepolicystatus,admin.policy.edit,admin.policy.delete',
            'icon_class' => 'fa fa-shield',
            'sr_no' => 6
        ]);

        ProjectPage::create([
            'id' => 10,
            'parent_menu' => 0,
            'label' => 'Settings',
            'icon_class' => 'fa fa-cog',
            'route_url' => 'admin.settings.list',
            'is_display_in_menu' => 0,
            'inner_routes' => 'admin.settings.list,admin.settings.edit',
            'sr_no' => 100
        ]);

        ProjectPage::create([
            'id' => 11,
            'parent_menu' => 0,
            'label' => 'Bookings',
            'route_url' => null,
            'icon_class' => 'icon-basket',
            'is_display_in_menu' => 1,
            'sr_no' => 1
        ]);

        ProjectPage::create([
            'id' => 12,
            'parent_menu' => 11,
            'label' => 'Booking',
            'route_url' => 'admin.orders.list',
            'is_display_in_menu' => 1,
            'inner_routes' => 'admin.orders.list,admin.allOrderlist,admin.updateOrdernote,admin.orders.view,admin.orders.save,admin.change_order_status,admin.change_order_item_status,admin.orders.pdf,admin.orders.play_video'
        ]);

        ProjectPage::create([
            'id' => 13,
            'parent_menu' => 16,
            'label' => 'Team Members',
            'icon_class' => 'fa fa-users',
            'route_url' => 'admin.teammembers.list',
            'is_display_in_menu' => 1,
            'inner_routes' => 'admin.teammembers.list,admin.teammembers.addorupdate,admin.allteamslist,admin.teammembers.changeteamstatus,admin.teammembers.edit,admin.teammembers.delete',
            'sr_no' => 8
        ]);

        ProjectPage::create([
            'id' => 14,
            'parent_menu' => 16,
            'label' => 'Testimonials',
            'icon_class' => 'fa fa-quote-left',
            'route_url' => 'admin.testimonials.list',
            'is_display_in_menu' => 1,
            'inner_routes' => 'admin.testimonials.list,admin.testimonials.addorupdate,admin.alltestimonialslist,admin.testimonials.changetestimonialstatus,admin.testimonials.edit,admin.testimonials.delete',
            'sr_no' => 9
        ]);

        ProjectPage::create([
            'id' => 15,
            'parent_menu' => 0,
            'label' => 'Review',
            'route_url' => 'admin.review.list',
            'icon_class' => 'fa fa-comment',
            'is_display_in_menu' => 0,
            'inner_routes' => 'admin.review.list,admin.allReviewlist,admin.review.rejectstatus,admin.review.acceptstatus',
            'sr_no' => 10
        ]);

        ProjectPage::create([
            'id' => 16,
            'parent_menu' => 0,
            'label' => 'Page',
            'route_url' => null,
            'icon_class' => 'fa fa-file',
            'is_display_in_menu' => 1,
            'sr_no' => 11
        ]);

        ProjectPage::create([
            'id' => 17,
            'parent_menu' => 16,
            'label' => 'About Us',
            'route_url' => 'admin.infopage.about',
            'is_display_in_menu' => 1,
            'inner_routes' => 'admin.infopage.about,admin.infopage.edit,admin.infopage.update'
        ]);

        ProjectPage::create([
            'id' => 18,
            'parent_menu' => 16,
            'label' => 'Contact Us',
            'route_url' => 'admin.infopage.contact',
            'is_display_in_menu' => 1,
            'inner_routes' => 'admin.infopage.contact,admin.infopage.edit,admin.infopage.update'
        ]);

        ProjectPage::create([
            'id' => 19,
            'parent_menu' => 16,
            'label' => 'Privacy Policy',
            'route_url' => 'admin.infopage.privacy_policy',
            'is_display_in_menu' => 1,
            'inner_routes' => 'admin.infopage.privacy_policy,admin.infopage.edit,admin.infopage.update'
        ]);

        ProjectPage::create([
            'id' => 20,
            'parent_menu' => 16,
            'label' => 'Terms Condition',
            'route_url' => 'admin.infopage.terms_condition',
            'is_display_in_menu' => 1,
            'inner_routes' => 'admin.infopage.terms_condition,admin.infopage.edit,admin.infopage.update'
        ]);

        ProjectPage::create([
            'id' => 21,
            'parent_menu' => 16,
            'label' => 'FAQ',
            'route_url' => 'admin.faqs.list',
            'icon_class' => 'icon-question',
            'is_display_in_menu' => 1,
            'inner_routes' => 'admin.faqs.list,admin.faq.add,admin.faq.save,admin.allFaqlist,admin.faq.edit,admin.faq.delete',
            'sr_no' => 9
        ]);

        ProjectPage::create([
            'id' => 22,
            'parent_menu' => 16,
            'label' => 'Post',
            'icon_class' => 'fa fa-list-alt',
            'route_url' => 'admin.posts.list',
            'is_display_in_menu' => 1,
            'inner_routes' => 'admin.posts.list,admin.posts.add,admin.posts.save,admin.allbloglist,admin.posts.changepoststatus,admin.posts.delete,admin.posts.edit,admin.posts.uploadfile,admin.posts.removefile',
        ]);

        ProjectPage::create([
            'id' => 23,
            'parent_menu' => 0,
            'label' => 'Login Log',
            'icon_class' => 'fa fa-list-alt',
            'route_url' => 'admin.loginlog.list',
            'is_display_in_menu' => 0,
            'inner_routes' => 'admin.loginlog.list,admin.allloginloglist',
            'sr_no' => 101
        ]);

        ProjectPage::create([
            'id' => 24,
            'parent_menu' => 0,
            'label' => 'Payments',
            'icon_class' => 'fa fa-list-alt',
            'route_url' => 'admin.payments.list',
            'is_display_in_menu' => 0,
            'inner_routes' => 'admin.payments.list,admin.allpaymentslist',
            'sr_no' => 101
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
