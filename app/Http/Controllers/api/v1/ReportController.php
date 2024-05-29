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
/**
 * @OA\Tag(
 *     name="Reports",
 *     description="API Endpoints for Reports"
 * )
 */
class ReportController extends Controller
{
 /**
     * @OA\Get(
     *     path="/api/v1/reports",
     *     summary="Filter reports",
     *     tags={"Reports"},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by status",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="reporter_id",
     *         in="query",
     *         description="Filter by reporter ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/ReportCollection")
     *     )
     * )
     */
    public function filterReports(Request $request)
    {
        $query = Report::query();

        $validStatuses = ['pending', 'nostatus', 'complete', 'deny'];

        // Filter by status if provided and valid
        $status = $request->input('status');
        if ($status && in_array($status, $validStatuses)) {
            $query->where('status', $status);
        }

        // Filter by reporter ID if provided
        $reporterId = $request->input('reporter_id');
        if ($reporterId) {
            $query->where('reporter_id', $reporterId);
        }

        // Paginate the filtered reports
        $reports = $query->paginate(5);

        return new ReportCollection($reports);
    }

 /**
     * @OA\Get(
     *     path="/api/v1/reports/{reporterId}",
     *     summary="Get reports by reporter ID",
     *     tags={"Reports"},
     *     @OA\Parameter(
     *         name="reporterId",
     *         in="path",
     *         description="Reporter ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Report"))
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No reports found for this reporter"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="An error occurred while retrieving the reports"
     *     )
     * )
     */
    public function getReportById($reporterId)
    {
        try {
            // Query the database to find all reports by the reporter's ID
            $reports = Report::where('reporter_id', $reporterId)->get();
    
            // Check if any reports were found
            if ($reports->isEmpty()) {
                return response()->json(['message' => 'No reports found for this reporter'], 404);
            }
    
            // Return the reports
            return response()->json($reports, 200);
        } catch (\Exception $e) {

            
            return response()->json(['message' => 'An error occurred while retrieving the reports'], 500);
        }
    }


    public function makeReport(StorereportRequest $request)
    {
        $validatedData = $request->validated();

        // Check if the type of category exists
        $typeOfCategory = TypeOfCategory::where('type', $validatedData['type'])->first();

        if (!$typeOfCategory) {
            return response()->json(['error' => 'Type does not exist'], 404);
        }

        // Check if the reporter exists
        if (!Reporter::where('id', $validatedData['reporter_id'])->exists()) {
            return response()->json(['error' => 'Reporter not found'], 404);
        }

        // Check if the report already exists
        $existingReport = Report::where('reporter_id', $validatedData['reporter_id'])
            ->where('status', $validatedData['status'])
            ->where('typeOfCategory_id', $typeOfCategory->id)
            ->whereHas('reportDetail', function ($query) use ($validatedData) {
                $query->where('title', $validatedData['title'])
                    ->where('description', $validatedData['description']);
            })
            ->whereHas('location', function ($query) use ($validatedData) {
                $query->where('building', $validatedData['building'])
                    ->where('floor', $validatedData['floor'])
                    ->where('room', $validatedData['room']);
            })
            ->exists();

        if ($existingReport) {
            return response()->json(['error' => 'Duplicate report found'], 422);
        }

        // Create the location
        $location = Location::create([
            'building' => $validatedData['building'],
            'floor' => $validatedData['floor'],
            'room' => $validatedData['room'],
        ]);

        // Handle image upload
        $uploadedFileUrl = $request->hasFile('image')
            ? Cloudinary::upload($request->file('image')->getRealPath())->getSecurePath()
            : null;

        // Create report detail
        $reportDetail = ReportDetail::create([
            'title' => $validatedData['title'],
            'description' => $validatedData['description'],
            'image' => $uploadedFileUrl,
            'anonymous' => $validatedData['anonymous'] ?? false,
        ]);

        // Create the report
        $report = Report::create([
            'reporter_id' => $validatedData['reporter_id'],
            'status' => $validatedData['status'],
            'location_id' => $location->id,
            'report_detail_id' => $reportDetail->id,
            'typeOfCategory_id' => $typeOfCategory->id
        ]);

        return new ReportResource($report);
    }

    public function updateReportDetail(UpdateReportRequest $request, Report $report)
    {
        $report->update($request->except('feedback'));
        $report->reportDetail()->update(['feedback' => $request->feedback]);

        return (new ReportResource($report))
            ->response()
            ->setStatusCode(200)
            ->header('Content-Type', 'application/json');
    }

   
    public function deleteReport(report $report)
    {
        $report->delete();

        return response()->json(null, 204);
    }
}
