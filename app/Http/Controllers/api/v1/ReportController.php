<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Requests\StorereportRequest;
use App\Models\Report;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateReportRequest;
use App\Http\Resources\v1\ReportCollection;
use App\Http\Resources\v1\ReportResource;
use App\Models\Category;
use App\Models\Location;
use App\Models\ReportDetail;
use App\Models\Reporter;
use App\Service\ReportQuery;
use Illuminate\Http\Request;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filter = new ReportQuery();
        $queryItems = $filter->transform($request);

        $status = $request->input('status');
        $user_id = $request->input('reporter_id');

        $query = Report::query();

        if ($status === 'pending' || $status === 'nostatus' || $status === 'complete' || $status === 'deny') {
            $query->where('status', $status);
        }

        if ($user_id) {
            $query->where('reporter_id', $user_id);
        }
        $reports = $query->paginate(10);

        return new ReportCollection($reports);
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
        $uploadedFileUrl = null;

        // Create a new category
        $category = Category::create(['name' => $validatedData['category']]);

        // Create a new location
        $location = Location::create([
            'building' => $validatedData['building'],
            'floor' => $validatedData['floor'],
            'room' => $validatedData['room']
        ]);
        
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $uploadedFileUrl = Cloudinary::upload($file->getRealPath())->getSecurePath();
        }

        // Create a new report detail
        $reportDetail = ReportDetail::create([
            'title' => $validatedData['title'],
            'description' => $validatedData['description'],
            'anonymous' => $validatedData['anonymous'],
            'image' => $uploadedFileUrl ?? null
        ]);

        

        $reporter_id = $validatedData['reporter_id'];

        // Check if the reporter exists
        $reporterExists = Reporter::where('id', $reporter_id)->exists();

        if (!$reporterExists) {
            // The reporter doesn't exist. You can return an error message here.
            return response()->json(['error' => 'Reporter not found'], 404);
        }

        // Create a new report
        $report = Report::create(array_merge([
            'reporter_id' => $reporter_id,
            'status' => $validatedData['status']
        ], [
            'location_id' => $location->id,
            'report_detail_id' => $reportDetail->id,
            'category_id' => $category->id
        ]));


        return new ReportResource($report);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateReportRequest $request, Report $report)
    {
        $report->update($request->all());

        return response()->json(['message' => 'Report updated successfully'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(report $report)
    {
        $report->delete();

        return response()->json(null, 204);
    }
}
