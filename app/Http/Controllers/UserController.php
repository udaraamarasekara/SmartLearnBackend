<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\CommonResource;
use App\Jobs\sendNotifications;
use App\Models\Paper;
use Illuminate\Http\Request;
use App\Models\User;

use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
            'role' =>'Student',
            'is_approved'=>false
        ]);

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
        else if($user->is_approved==0)
        {
            return response()->json(['error' => 'User not approved!'], 403);
        }


            $user->fcm()->create(['fcm'=>$request->fcm]);
       
        // Generate a token for the authenticated user
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'name' => $user->name,
            'token' => $token,
            'role' =>$user->role
        ]);
    }

     
    public function approveMember(int $id)
    {

        try{
         
            User::where('id',$id)->update(['is_approved'=>true]);
            return true; 
          }
          catch (Exception $e){
              return false;
          }
    }

    public function getStudents()
    {
        return  CommonResource::collection(User::select('id','name','email','is_approved')->where('role','Student')->simplePaginate(10)->withPath(''));
    }

    public function getTutors()
    {
        return  CommonResource::collection(User::select('id','name','email','is_approved' )->where('role','Lecturer')->simplePaginate(10)->withPath(''));
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
           User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' =>'Lecturer',
                'is_approved'=>true
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
            'file' => 'required|mimes:pdf',
        ]);

      
        if ($validator->fails()) {
            return false;
        }
        $path = '';
        if ($request->file('file')) {
            $file = $request->file('file');
            $path = $file->store('uploads', 'public');
            Paper::create(['title'=>$request->name,'file'=>$path]);   
            $students = User::where('role','Student')->get();
            sendNotifications::dispatch($students,$request->name);
        }
        
        
        return true;
    }

     public function getPapers(){
        return  CommonResource::collection(Paper::select('id','title')->latest()->simplePaginate(10)->withPath(''));
     }

     public function downloadPaper(int $id)
     {    
       return response()->download(Paper::find($id)->file);
     }


     public function updateProfile(Request $request)
     {
         try{ 
             // Validate the request
             $validator = Validator::make($request->all(), [
                 'name' => 'required|string|max:255',
                 'email' => 'required|string|email|max:255',
                 'password' => 'required|string|min:8|confirmed',
             ]);
     
             if ($validator->fails()) {
                 return response()->json(['errors' => $validator->errors()], 422);
             }
     
             // Create a new user
            User::where('id',auth()->user()->id)->update([
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

     public function logout(Request $request)
{
    // Revoke the token of the current user
    $request->user()->currentAccessToken()->delete();

    return true;
}
}
