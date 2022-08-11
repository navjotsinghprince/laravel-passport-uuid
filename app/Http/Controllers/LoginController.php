<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

//Feel Free To Visit https://navjotsinghprince.com
class LoginController extends Controller
{
    public function login(Request $request)
    {
        if (Auth::attempt(['email' =>  $request->email, 'password' =>  $request->password])) {
            $user = Auth::user();
            $success['access_token'] =  $user->createToken('PrinceFerozepuria')->accessToken;
            return response()->json(['success' => $success], 200);
        } else {
            return response()->json(['error' => 'Unauthorised'], 401);
        }
    }

    public function getUser(Request $request)
    {
        $user = Auth::user();
        $response = [
            "user" =>  $user,
            "message" => "success"
        ];
        return response()->json($response, 200);
    }
}
