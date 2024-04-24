<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorereportRequest;
use App\Mail\ResetPassword;
use App\Models\Reporter;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

use App\Http\Requests\UpdateReportRequest;
use App\Http\Resources\v1\ReporterCollection;
use App\Service\ReportQuery;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class ReporterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filter = new ReportQuery();
        $filterItems = $filter->transform($request);

        $includeReport = $request->query('includeReport');

        $reportersQuery = Reporter::query();

        // Apply filters
        foreach ($filterItems as $column => $value) {
            // Check if the column exists in the reporters table
            if (Schema::hasColumn('reporters', $column)) {
                $reportersQuery->where($column, $value);
            } else {
            
            }
        }

        // Include reports if needed
        if ($includeReport) {
            $reportersQuery->with('reports');
        } else {
            // Eager load reports for all reporters
            $reportersQuery->with('reports');
        }

        // Paginate the results and append the query parameters
        $perPage = $request->query('per_page', 10); // Default to 10 items per page
        $reporters = $reportersQuery->paginate($perPage)->appends($request->query());

        return new ReporterCollection($reporters);
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
    public function store(StorereportRequest $request)
    {
        
        $validatedData = $request->validated();
    
        // Generate a random password
        $password = Str::random(12); // Generates a random 12-character password
    
        // Create a new reporter
        $reporter = Reporter::create([
            'email' => $validatedData['email'],
            'password' => Hash::make($password),
        ]);
    
        // Send email with the generated password
        Mail::to($reporter->email)->send(new ResetPassword ($reporter, $password));
    
        return response()->json(['message' => 'User created successfully'], 201);
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
            $validateUser = Validator::make($request->all(), [
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
    public function resetPassword(Request $request)
    {
        try {
            // Validate request data
            $validateData = validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required',
                'password_confirmation' => 'required|same:password',
            ]);

            if ($validateData->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validateData->errors()
                ], 400);
            }

            // Find the reporter by email
            $reporter = Reporter::where('email', $request->email)->first();

            if (!$reporter) {
                return response()->json([
                    'status' => false,
                    'message' => 'Reporter not found'
                ], 404);
            }

            // Update the reporter's password
            $reporter->password = Hash::make($request->password);
            $reporter->save();

            return response()->json([
                'status' => true,
                'message' => 'Password reset successfully'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(reporter $reporter)
    {
        try {
            $reporter->delete();

            return response()->json(['message' => 'Reporter deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete reporter'], 500);
        }
    }
}
