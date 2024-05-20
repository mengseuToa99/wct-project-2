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
use App\Models\TypeOfCategory;
use App\Service\ReportQuery;
use Illuminate\Http\Request;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $status = $request->input('status');
        $user_id = $request->input('reporter_id');

        $query = Report::query();

        $validStatuses = ['pending', 'nostatus', 'complete', 'deny'];

        if (in_array($status, $validStatuses)) {
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
            ->join('categories', 'reports.category_id', '=', 'categories.id')
            ->select('categories.name as category', DB::raw('COUNT(reports.id) as count'))
            ->groupBy('categories.name')
            ->pluck('count', 'category')
            ->toArray();

        return response()->json($categoryCounts);
    }


    public function store(StorereportRequest $request)
    {
        $validatedData = $request->validated();

        // Check if the type of category exists
        $typeOfCategory = TypeOfCategory::where('type', $validatedData['type'])->first();

        if (!$typeOfCategory) {
            // Type of category does not exist. You can return an error message here.
            return response()->json(['error' => 'Type does not exist'], 404);
        }

        // Check if the report already exists
        $existingReport = Report::where('reporter_id', $validatedData['reporter_id'])
            ->where('status', $validatedData['status'])
            ->whereHas('reportDetail', function ($query) use ($validatedData) {
                $query->where('title', $validatedData['title'])
                    ->where('description', $validatedData['description']);
            })
            ->whereHas('location', function ($query) use ($validatedData) {
                $query->where('building', $validatedData['building'])
                    ->where('floor', $validatedData['floor'])
                    ->where('room', $validatedData['room']);
            })
            ->where('typeOfCategory_id', $typeOfCategory->id)
            ->exists();

        if ($existingReport) {
            return response()->json(['error' => 'Duplicate report found'], 422);
        }

        $location = Location::create([
            'building' => $validatedData['building'],
            'floor' => $validatedData['floor'],
            'room' => $validatedData['room'],
        ]);

        $uploadedFileUrl = $request->hasFile('image')
            ? Cloudinary::upload($request->file('image')->getRealPath())->getSecurePath()
            : null;

        $reportDetail = ReportDetail::create([
            'title' => $validatedData['title'],
            'description' => $validatedData['description'],
            'image' => $uploadedFileUrl,
            'anonymous' => $validatedData['anonymous'] ?? false, // provide a default value if it's not set
        ]);

        $reporter_id = $validatedData['reporter_id'];

        if (!Reporter::where('id', $reporter_id)->exists()) {
            return response()->json(['error' => 'Reporter not found'], 404);
        }

        $report = Report::create([
            'reporter_id' => $reporter_id,
            'status' => $validatedData['status'],
            'location_id' => $location->id,
            'report_detail_id' => $reportDetail->id,
            'typeOfCategory_id' => $typeOfCategory->id
        ]);

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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(report $report)
    {
        $report->delete();

        return response()->json(null, 204);
    }
}
