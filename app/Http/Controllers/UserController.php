<?php namespace App\Http\Controllers;

use App\Classes\Countries;
use App\Http\Requests;
use App\Http\Requests\UserRequest;
use App\Http\Controllers\Controller;

use App\User;
use App\Organization;
use App\Role;
use Illuminate\Http\Request;
use App\Classes\Usability;
use Validator;
use Hash;
use Redirect;

class UserController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */

    public function __construct()
    {
        $this->middleware('user.editable',['only' => ['edit','store']]);
    }

	public function index($organization_id=0)
	{
        //dd($organization_id);

        $organization = Organization::where('id', '=', $organization_id)->first();

        $items_per_page = Usability::$item_per_page;
        if($organization_id>0)
        {
            $users = User::where('organization_id','=',$organization_id)->paginate($items_per_page);
        }
        else 
        {
            $users = User::paginate($items_per_page);
        }
        return view('users.index', compact('users', 'organization_id', 'organization'));
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
        $organizations = Organization::where('id','>',1)->get();
        $roles = Role::all();
        return view('users.create', compact(array('organizations','roles')));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
    public function store(UserRequest $request)
    {
        
        
        $user = new User();
    
        $user->name = $request->input("name");
        $user->email = $request->input("email");
        $user->password = bcrypt($request->input("password"));
        $user->organization_id = $request->input("organization_id");
        
        /* Ripon add this validation */ /* Mahadi added name and organization fields */
        $this->validate($request, [
        'name' => 'required|regex:/^[A-z ]+$/',
        'email' => 'required|email|unique:users|max:255',
        'password' => 'required|min:6|max:50|confirmed',
        'organization_id' => 'required',
         ]);
        /* Ripon add this validation End */
        
        $user->save();
        $user->attachRole($request->input("role"));


        //dd($request->input("organization"));
        //dd($user->id);
        //dd($request->input('organization'));
       // Organization::attachToOrganization($request->input("organization"),$user->id);

        return redirect()->route('users.index')->with('message', 'User created successfully.');


    }

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
        $user = User::findOrFail($id);
        return view('users.show', compact('user'));
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
        $user = User::findOrFail($id);


        $organizations = Organization::all();
        $roles = Role::all();
        $country = Countries::Country($user->organization->country);
        return view('users.edit', compact(array('user','organizations','country')));
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update(Request $request, $id)
	{
        $user = User::findOrFail($id);
        
        $this->validate($request, [
            'name' => 'required|regex:/^[A-z ]+$/',
        	'old_password' 	=> 'required',
        	'password' 	=> 'min:6|confirmed',
        	'email' 	=> 'required|email|max:255',
        ]);

        if (!Hash::check($request->input("old_password"), $user->password))
        {
            return Redirect::back()->withInput()->with('error', 'current password is not correct!');
        }

        $user->name = $request->input("name") ? $request->input("name") : $user->name;
        $user->email = $request->input("email") ? $request->input("email") : $user->email;
        $user->status = $request->input("status") ? $request->input("status") : $user->status;
        //dd($user->status);
        $user->password = bcrypt($request->input("password"));
        $user->save();
       // dd($user);
        return redirect()->route('users.index')->with('message', 'User updated successfully.');
	}

    /**
    * Activate or Deactivate users
    * Created by Mahadi
    */
    public function ActivateOrDeactivate(Request $request, $id)
    {
        //return "Deactivate";

        $user = User::findOrFail($id);

        if($user->status == 1)
            $user->status = 0;
        else
            $user->status = 1;


        //dd($user->status);

        $user->save();

        return redirect()->route('users.index')->with('message', 'User updated successfully.');
    }

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('users.index')->with('message', 'Item deleted successfully.');
	}
        
        public function validator(array $data)
        {
            return Validator::make($data, [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|confirmed|min:6',
            ]);
        }
       
}
