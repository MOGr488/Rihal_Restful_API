<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\User as UserResource;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
    
        $limit = $request->input('limit') <= 50 ? $request->input('limit') : 15;
        $user = UserResource::collection(User::paginate($limit));
        return $user->response()
                    ->setStatusCode(200, "Users Returned Successfully")
                    ->header('Additional-Header', 'True');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {   

        $user =new UserResource(User::create(
            [
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
            ],
        ));
        return $user->response()
                    ->setStatusCode(200, "User Stored Successfully");

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = new UserResource(User::findOrFail($id));
        return $user->response()
                    ->setStatusCode(200, "User Returned Successfully")
                    ->header('Additional-Header', 'True');
    
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $iduser = User::findOrFail($id);
        

        $user =new UserResource(User::findOrFail($id));
        $user->update($request->all());

        return $user->response()
                    ->setStatusCode(200, "User Updated Successfully");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
   

        User::findOrFail($id)->delete();
        return response()->noContent();
    }
}
