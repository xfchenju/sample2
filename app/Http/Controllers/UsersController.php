<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\User;
use Auth;

class UsersController extends Controller
{
    public function __construct() {
        $this->middleware('auth', [
            'except' => ['show', 'create', 'stroe','index']
        ]);
    }

    public function create() {
    	return view('users.create');
    }

    public function show(User $user) {
    	return view('users.show',compact('user'));
    }

    public function store(Request $request) {
    	$this->validate($request, [
    		'name' => 'required|max:50',
    		'email' => 'required|email|unique:users|max:255',
    		'password' => 'required',
    	]);

    	$user = User::create([
    		'name' => $request->name,
    		'email' => $request->email,
    		'password' => bcrypt($request->password),
    	]);

    	Auth::login($user);

    	session()->flash('success', '欢迎，您将在这里开启一段新的旅程~');
    	
    	return redirect()->route('users.show',[$user]);
    }

    public function edit(User $user) {
        $this->authorize('update', $user);
        return view('users.edit',compact('user'));
    }

    public function update(User $user, Request $request) {
        $this->authorize('update', $user);
        
        $this->validate($request, [
            'name' => 'required|max:50',
            'password' => 'nullable|confirmed|min:6'
        ]);

        $data = [];
        if($user->name != $request->name){
            $data['name'] = $request->name;
        }
        
        if($request->password && $user->password != bcrypt($request->password)){
            $data['password'] = bcrypt($request->password);
        }
        if(!empty($data)){
            $user->update($data);
            session()->flash('success', '个人资料更新成功！');    
        }else{
            session()->flash('success', '个人资料与原来相同！');    
        }
        
        return redirect()->route('users.show', $user->id);
    }

    public function index() {
        $users = User::paginate(10); 
        return view('users.index', compact('users'));
    }

    public function destroy(User $user) {
        
        $this->authorize('destroy', $user);
        $user->delete();
        session()->flash('success', '删除用户成功！');
        return back();
    }
}
