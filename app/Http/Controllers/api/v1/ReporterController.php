<?php

namespace App\Http\Controllers\api\v1;


use App\Models\Reporter;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorereporterRequest;
use App\Http\Requests\UpdatereporterRequest;
use App\Service\ReportQuery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Mail\ResetPassword;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\v1\ReporterCollection;
use App\Http\Resources\v1\ReporterResource;
use App\Models\Report;
use Illuminate\Support\Facades\Log;
use App\Imports\ReportersImport;
use Exception;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;



class ReporterController extends Controller
{

    public function update(UpdatereporterRequest $request, Reporter $reporter)
    {
        $data = $request->all();

        if ($request->has('profile_pic')) {
            $file = $request->file('profile_pic');
            $uploadedFileUrl = Cloudinary::upload($file->getRealPath())->getSecurePath();
            $data['profile_pic'] = $uploadedFileUrl;
        }

        $reporter->update($data);

        return (new ReporterResource($reporter))
            ->response()
            ->setStatusCode(200)
            ->header('Content-Type', 'application/json');
    }


    public function index(Request $request)
    {
        $filter = new ReportQuery();
        $filterItems = $filter->transform($request);
        $includeReport = $request->query('includeReport');
        $reportersQuery = Reporter::query();

        foreach ($filterItems as $column => $value) {

            if (Schema::hasColumn('reporters', $column)) {
                $reportersQuery->where($column, $value);
            }
        }

        if ($includeReport) {
            $reportersQuery->with(['reports' => function ($query) {
                $query->orderBy('created_at', 'desc');
            }]);
        }

        $perPage = $request->query('per_page', 10);
        $reporters = $reportersQuery->paginate($perPage)->appends($request->query());
        return new ReporterCollection($reporters);
    }

    public function show($id)
    {
        $reporter = Reporter::findOrFail($id);
        return new ReporterResource($reporter);
    }


    public function allReportersWithStats()
    {

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


    public function addOneUser(StorereporterRequest $request)
    {
        $validatedData = $request->validated();
        $password = "password";
        $emailParts = explode('@', $validatedData['email']);
        $username = $emailParts[0];

        $reporter = Reporter::create([
            'email' => $validatedData['email'],
            'username' => $username,
            'password' => Hash::make($password),
            'role' => $validatedData['role'],
        ]);

        Mail::to($reporter->email)->send(new ResetPassword($reporter, $password));

        return response()->json([
            'message' => 'User created successfully',
            'reporter' => $reporter
        ], 201);
    }


    public function addMultiUser(Request $request)
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
        $totalUsers = Reporter::count();
        $totalReports = Report::count();
        $deniedReports = Report::where('status', 'deny')->count();
        $completeReports = Report::where('status', 'complete')->count();
        $acceptedReports = Report::where('status', 'pending')->count();
        $completedReports = Report::where('status', 'complete')->count();

        $categoryCounts = DB::table('reports')
        ->join('type_of_categories', 'reports.typeOfCategory_id', '=', 'type_of_categories.id')
        ->join('categories', 'type_of_categories.category_id', '=', 'categories.id')
        ->select('categories.name as category', DB::raw('COUNT(reports.id) as count'))
        ->groupBy('categories.name')
        ->pluck('count', 'category')
        ->toArray();

        $categoryTypesCounts = DB::table('reports')
            ->join('type_of_categories', 'reports.typeOfCategory_id', '=', 'type_of_categories.id')
            ->select('type_of_categories.type as category', DB::raw('COUNT(reports.id) as count'))
            ->groupBy('type_of_categories.type')
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


    public function login(Request $request)
    {
        try {
            // Validate the request
            $validateUser = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required'
            ]);

            if ($validateUser->fails()) {
                return  $this->error('', 'Validation error', 401);
            }

            $reporter = Reporter::where('email', $request->email)->first();

            // Attempt to authenticate the reporter
            if (!$reporter || !Hash::check($request->password, $reporter->password)) {
                return $this->error('', 'Credentials do not match', 401);
            }

            return response()->json([
                'status' => true,
                'message' => 'User Logged In Successfully',
                'token' =>  $token = $reporter->createToken('Api token of ' . $reporter->name)->plainTextToken,
                'user_id' => $reporter->id,
                'role' => $reporter->role,
                'name' => $reporter->username,
                'profile_pic' => $reporter->profile_pic,
            ], 200);
            Log::info('Profile picture', ['url' => $reporter->profile_pic]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $this->getMessage()
            ], 500);
        }
    }


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


    public function deleteUser(reporter $reporter)
    {
        try {
            $reporter->delete();

            return response()->json(['message' => 'Reporter deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete reporter'], 500);
        }
    }


    public function logout(Request $request)
    {
        try {
            // Log the request headers and the token

            // Revoke all tokens for the authenticated reporter
            $request->user('reporter')->tokens()->delete();

            // Return success response
            return response()->json(['message' => 'You have logged out successfully.'], 200);
        } catch (\Exception $e) {

            // Handle any exceptions that may occur during token revocation
            return response()->json(['message' => 'Failed to log out.'], 500);
        }
    }
}
