<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorereporterRequest;
use App\Http\Requests\UpdatereporterRequest;
use App\Models\reporter;
use GuzzleHttp\Psr7\Request;
use App\Service\ReportQuery;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\ResetPassword;
use App\Models\Report;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;


class ReporterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorereporterRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(reporter $reporter)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function login(Request $request)
    {
        try {
            // Validate the request
            $validateUser = validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required'
            ]);
    
            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }
    
            // Attempt to authenticate the reporter
            if (!Auth::guard('reporter')->attempt($request->only('email', 'password'))) {
                return response()->json([
                    'status' => false,
                    'message' => 'Email & Password do not match with our records.',
                ], 401);
            }
    
            // Get the authenticated reporter
            $reporter = Reporter::where('email', $request->email)->first();
    
            if ($request->email === 'admin@example.com' && $request->password === 'pass1234') {
                $token = $reporter->createToken('admin-token', ['admin-ability'])->plainTextToken;
            } else {
                $token = $reporter->createToken('user-token')->plainTextToken;
            }
    
            return response()->json([
                'status' => true,
                'message' => 'User Logged In Successfully',
                'token' => $token,
                'user_id' => $reporter->id ,
                'name' => $reporter ->name
                 // Include the user ID in the response
            ], 200);
    
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatereporterRequest $request, reporter $reporter)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(reporter $reporter)
    {
        //
    }
}
