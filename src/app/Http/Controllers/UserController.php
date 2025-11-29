<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function profile()
    {
        $user = User::find(auth()->user()->id);
        if ($user) {
            return ResponseHelper::jsonResponse(true, '0000', 'Success', $user, 200);
        }

        return ResponseHelper::jsonResponse(false, '0001', 'User not found', [], 404);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'  => 'required'
        ]);

        //if validation fails
        if ($validator->fails()) {
            return ResponseHelper::jsonResponse(false, '0001', 'validation error', $validator->errors(), 422);
        }

        $user = User::find(auth()->user()->id);
        if ($user) {
            $user->name = $request->name;
            $user->save();
            return ResponseHelper::jsonResponse(true, '0000', 'Success', $user, 200);
        }
    }
}
