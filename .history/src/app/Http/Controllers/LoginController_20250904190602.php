<?php

namespace App\Http\Controllers;



use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;


class LoginController extends Controller
{



    //ログイン画面表示(管理者)
    public function create()
    {
        return view('admin.admin_login');
    }

}
