<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\User;
use Auth;
use Mail;

class UsersController extends Controller
{

    //验证登录
    public function __construct()
    {
        $this->middleware('auth', [
            'except' => ['show', 'create', 'store', 'index', 'confirmEmail']
        ]);

        $this->middleware('guest', [
            'only' => ['create']
        ]);
    }
    //用户注册页面
    public function create()
    {
    	return view('users.create');
    }

    //显示用户信息
    public function show(User $user)
    {
        $statuses = $user->statuses()
                           ->orderBy('created_at', 'desc')
                           ->paginate(30);
        return view('users.show', compact('user', 'statuses'));
    }

    //创建用户
    public function store(Request $request)
    {
    	$this->validate($request, [
    		'name' => 'required|max:50',
    		'email' => 'required|email|unique:users|max:255',
    		'password' => 'required|confirmed|min:6'
    	]);

    	$user = User::create([
    		'name' => $request->name,
    		'email' => $request->email,
    		'password' => bcrypt($request->password),
    	]);

        $this->sendEmailConfirmationTo($user);
        session()->flash('success', '验证邮箱已发送到你的邮箱，请登录邮件激活账号');
        return redirect('/');

    }

    //编辑用户资料
    public function edit(User $user)
    {
        //验证用户授权策略
        $this->authorize('update', $user);

        return view('users.edit', compact('user'));
    }

    //更新用户资料
    public function update(User $user, Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:50',
            'password' => 'nullable|confirmed|min:6',
        ]);
        //验证用户授权策略
        $this->authorize('update', $user);

        $data = [];
        $data['name'] = $request->name;
        if( $request->password ){
            $data['password'] = $request->password;
        }
        session()->flash('success', '个人资料更新成功');

        return redirect()->route('users.show', $user->id);
    }

    //用户列表
    public function index()
    {
        $users = User::paginate(10);
        return view('users.index', compact('users'));
    }

    //删除用户
    public function destroy(User $user)
    {
        $this->authorize('destroy', $user);
        $user->delete();
        session()->flash('success', '成功删除用户');
        return back();
    }

    //发送验证邮件
    protected function sendEmailConfirmationTo($user)
    {
        $view = 'emails.confirm';
        $data = compact('user');
        $to = $user->email;
        $subject = "感谢注册 Sample 应用！请确认你的邮箱。";

        Mail::send($view, $data, function ($message) use ($to, $subject) {
            $message->to($to)->subject($subject);
        });
    }

    //邮箱激活
    public function confirmEmail($token)
    {
        $user = User::where('activation_token', $token)->firstOrFail();

        $user->activated = true;
        $user->activation_token = null;
        $user->save();

        Auth::login($user);
        session()->flash('success', '恭喜你，激活成功');
        return redirect()->route('users.show', [$user]);
    }



}
