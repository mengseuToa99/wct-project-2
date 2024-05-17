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
use Illuminate\Support\Facades\DB;
use App\Traits\HttpRespones;

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
        $reports = $query->paginate(5);

        return new ReportCollection($reports);
    }

    public function show($id)
    {
        $report = Report::find($id);

        if (!$report) {
            return response()->json(['message' => 'Report not found'], 404);
        }

        return response()->json($report, 200);
    }

    public function countCategories()
{
    $categoryCounts = DB::table('reports')
        ->join('type_of_categories', 'reports.typeOfCategory_id', '=', 'type_of_categories.id')
        ->join('categories', 'type_of_categories.category_id', '=', 'categories.id')
        ->select('categories.name as category', DB::raw('COUNT(reports.id) as count'))
        ->groupBy('categories.name')
        ->pluck('count', 'category')
        ->toArray();

    return response()->json($categoryCounts);
}



    public function store(StorereportRequest $request)
    {
        $validatedData = $request->validated();
    $uploadedFileUrl = null;

    // Check if the report already exists
    $existingReport = Report::where('reporter_id', $validatedData['reporter_id'])
        ->where('status', $validatedData['status'])
        ->whereHas('reportDetail', function($query) use ($validatedData) {
            $query->where('title', $validatedData['title'])
                ->where('description', $validatedData['description']);
        })
        ->whereHas('location', function($query) use ($validatedData) {
            $query->where('building', $validatedData['building'])
                ->where('floor', $validatedData['floor'])
                ->where('room', $validatedData['room']);
        })
        ->whereHas('category', function($query) use ($validatedData) {
            $query->where('name', $validatedData['category'])
                ->where('type', $validatedData['type']);
        })
        ->exists();

    if ($existingReport) {
        // Report with the same details already exists. You can return an error message here.
        return response()->json(['error' => 'Duplicate report found'], 422);
    }
        // Create a new category
        $category = Category::create([
            'name' => $validatedData['category'],
            'type' => $validatedData['type']
        ]);

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

        $report->ReportDetail->update(['feedback' => $request->feedback]);

        return (new ReportResource($report))
            ->response()
            ->setStatusCode(200)
            ->header('Content-Type', 'application/json');
    }

}
