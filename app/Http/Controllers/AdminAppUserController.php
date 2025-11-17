<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\DataTables\AppUsersDataTable;
use App\Models\AppUser;
use App\Helpers\AuthHelper;

class AdminAppUserController extends Controller
{

    public function index(AppUsersDataTable $dataTable)
    {
        $pageTitle = trans('global-message.list_form_title',['form' => trans('users.title')] );
        $auth_user = AuthHelper::authSession();
        $assets = ['data-table'];
        $headerAction = '<a href="'.route('users.create').'" class="btn btn-sm btn-primary" role="button">Add New User </a>';
        return $dataTable->render('global.datatable', compact('pageTitle','auth_user','assets', 'headerAction'));
    }

    public function show($id)
    {
        $data = AppUser::findOrFail($id);

        $profileImage = getSingleMedia($data, 'profile_image');

        return view('users.profile', compact('data', 'profileImage'));
    }

}
