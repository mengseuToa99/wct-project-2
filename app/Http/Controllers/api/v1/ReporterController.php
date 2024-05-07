<?php

namespace App\Http\Controllers\api\v1;


use App\Models\Reporter;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorereporterRequest;
use App\Http\Requests\StorereportRequest;
use App\Service\ReportQuery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\ResetPassword;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\UpdateReportRequest;
use App\Http\Resources\v1\ReporterCollection;
use App\Http\Resources\v1\ReporterResource;
use App\Http\Resources\v1\ReportResource;
use App\Models\Report;
use Illuminate\Auth\Access\Gate;
use Illuminate\Support\Facades\Log;
use App\Imports\ReportersImport;
use App\Models\Category;
use Exception;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Facades\Excel;



class ReporterController extends Controller
{

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
                // Handle the case when the column doesn't exist
            }
        }

        // Include reports if needed
        if ($includeReport) {
            $reportersQuery->with(['reports' => function ($query) {
                // You can further customize the report query if needed
                $query->orderBy('created_at', 'desc'); // For example, ordering reports by created_at
            }]);
        }

        // Paginate the results and append the query parameters
        $perPage = $request->query('per_page', 10); // Default to 10 items per page
        $reporters = $reportersQuery->paginate($perPage)->appends($request->query());

        return new ReporterCollection($reporters); // Return a collection of reporters
    }

    public function show($id)
    {
        // Find the reporter by ID
        $reporter = Reporter::findOrFail($id);

        // Return the reporter as a JSON response
        return new ReporterResource($reporter);
    }


    public function allReportersWithStats()
    {
        // if (Gate::denies('admin-ability')) {
        //     throw new AuthorizationException('You are not authorized to perform this action.');
        // }

        $reporters = Reporter::withCount([
            'reports as total_reports',
            'reports as denied_reports' => function ($query) {
                $query->where('status', 'deny');
            },
            'reports as accepted_reports' => function ($query) {
                $query->where('status', 'complete');
            }
        ])->get();

        return response()->json($reporters);
    }
    /**
     * Store a newly created resource in storage.
     */

    public function store(StorereporterRequest $request)
    {
        $validatedData = $request->validated();

        $password = "password";

        $emailParts = explode('@', $validatedData['email']);
        $username = $emailParts[0];

        // Create a new reporter with auto-generated name and default role
        $reporter = Reporter::create([
            'email' => $validatedData['email'],
            'username' => $username, // Set the auto-generated name
            'password' => Hash::make($password),
            'role' => 'user'// Set the default role
        ]);

        // Send email with the generated password
        Mail::to($reporter->email)->send(new ResetPassword($reporter, $password));

        return response()->json([
            'message' => 'User created successfully',
            'reporter' => $reporter // Include the reporter object in the response
        ], 201);
    }

    public function storeMulti(Request $request)
    {
        try {
            $file = $request->file('reporters');
            $import = new ReportersImport;
            Excel::import($import, $file);

            return response()->json(['message' => 'Reporters imported successfully', 'data' => $import->getRows()], 201);
        } catch (Exception $e) {
            return response()->json(['message' => 'Import failed', 'error' => $e->getMessage()], 400);
        }
    }


    public function reportStats()
    {
        // if (Gate::denies('admin-ability')) {
        //     throw new AuthorizationException('You are not authorized to perform this action.');
        // }

        // Count total reporters
        $totalUsers = Reporter::count();

        // Count total reports created by all reporters
        $totalReports = Report::count();

        // Count reports that are denied
        $deniedReports = Report::where('status', 'deny')->count();

        $completeReports = Report::where('status', 'complete')->count();
        $acceptedReports = Report::where('status', 'pending')->count();

        $completedReports = Report::where('status', 'complete')->count();

        $categoryCounts = DB::table('reports')
        ->join('categories', 'reports.category_id', '=', 'categories.id')
        ->select('categories.name as category', DB::raw('COUNT(reports.id) as count'))
        ->groupBy('categories.name')
        ->pluck('count', 'category')
        ->toArray();

        $categoryTypesCounts = DB::table('reports')
        ->join('categories', 'reports.category_id', '=', 'categories.id')
        ->select('categories.type as category', DB::raw('COUNT(reports.id) as count'))
        ->groupBy('categories.type')
        ->pluck('count', 'category')
        ->toArray();

        return response()->json([
            'toatal_complete' => $completeReports,
            'total_users' => $totalUsers,
            'total_reports' => $totalReports,
            'denied_reports' => $deniedReports,
            'accepted_reports' => $acceptedReports,
            'completed_reports' => $completedReports,
            'total category' => $categoryCounts,
            'total category type' => $categoryTypesCounts,
        ]);
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
                'user_id' => $reporter->id,
                'name' => $reporter->name,
                'profile_pic' => $reporter->profile_pic,

                // Include the user ID in the response
            ], 200);
            Log::info('Profile picture', ['url' => $reporter->profile_pic]);
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
            $validateData = Validator::make($request->all(), [
                'email' => 'required|email',
                'current_password' => 'required',
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

            // Compare the provided current password with the hashed password stored in the database
            if (!Hash::check($request->current_password, $reporter->password)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Current password is incorrect'
                ], 400);
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
