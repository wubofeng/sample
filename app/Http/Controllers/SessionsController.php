<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Auth;

class SessionsController extends Controller
{
	public function __construct()
	{
		$this->middleware('guest', [
			'only' => ['create']
		]);
	}
    //登录页
    public function create()
    {
    	return view('sessions.create');
    }

    //验证登录
    public function store(Request $request)
    {
    	$credentials = $this->validate($request, [
    		'email' => 'required|email|max:255',
    		'password' => 'required'
    	]);

    	if (Auth::attempt($credentials, $request->has('remember'))){
            if(Auth::user()->activated){
                //登录成功
                session()->flash('success', '欢迎回来');
                return redirect()->intended(route('users.show', [Auth::user()]));
            } else {
                Auth::logout();
                session()->flash('warning', '您的账号未激活，请检查邮箱中的激活邮件');
                return redirect('/');
            }
    		
    	} else {
    		//登录失败
    		session()->flash('danger', '很抱歉，您的邮箱和密码不匹配');
    		return redirect()->back();
    	}
    	
    }

    //退出登录
    public function destroy()
    {
    	Auth::logout();
    	session()->flash('success', '您已成功退出！');
    	return redirect('login');
    }


}
