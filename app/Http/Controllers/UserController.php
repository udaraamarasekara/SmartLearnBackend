<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\CommonResource;
use App\Models\Fcm;
use Illuminate\Http\Request;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
class UserController extends Controller
{
    public function register(Request $request)
    {
       try{ 
        // Validate the request
        DB::beginTransaction();
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'fcm' =>'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Create a new user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' =>'Student'
        ]);

        if(!Fcm::where('fcm',$request->fcm)->exists())
        {
        $user->fcm()->create(['fcm'=>$request->fcm]);
        }
        // Generate a token for the user
        $token = $user->createToken('auth_token')->plainTextToken;
        DB::commit();
        return response()->json([
            'name' => $user->name,
            'token' => $token,
            'role' =>$user->role
        ]);
        }
        catch(Exception $e)
        {
           DB::rollBack(); 
           return response()->json(['errors' => $e], 500);

        }
    }
    public function login(Request $request)
    {
      // Validate the request
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string|min:8',
            'fcm' =>'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Attempt to find the user and verify the password
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }


        if (!Fcm::where('fcm', $request->fcm)->exists()) {
            $user->fcm()->create(['fcm'=>$request->fcm]);
        }
        // Generate a token for the authenticated user
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'name' => $user->name,
            'token' => $token,
            'role' =>$user->role
        ]);
    }

  

    public function getStudents()
    {
        return  CommonResource::collection(User::select('id','name','email')->where('role','Student')->simplePaginate(10)->withPath(''));
    }

    public function getTutors()
    {
        return  CommonResource::collection(User::select('id','name','email')->where('role','Lecturer')->simplePaginate(10)->withPath(''));
    }

    public function registerTutor(Request $request)
    {
        try{ 
            // Validate the request
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
            ]);
    
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
    
            // Create a new user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' =>'Lecturer'
            ]);
            return true;
        }
        catch(Exception $e)
        {
            return $e;
        }
    }

    public function deleteMember(int $id)
    {
      try{
         
          User::where('id',$id)->delete();
          return true; 
        }
        catch (Exception $e){
            return false;
        }
    } 
    
    public function newPaper(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'file' => 'required|application/pdf',
        ]);


        if ($validator->fails()) {
            return false;
        }
        $path = '';
        if ($request->file('file')) {
            $file = $request->file('file');
            $path = $file->store('uploads', 'public');

        }
        return true;
    }
}
