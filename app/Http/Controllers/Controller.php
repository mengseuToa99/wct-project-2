<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
/**
 * @OA\Info(
 *    title="School Management System API Documentation",
 *    version="1.0.0",
 * )
 * 
 * @OA\SecurityScheme(
 *     type="http",
 *     securityScheme="bearerAuth",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 * 
 * @OA\Schema(
 *     schema="Report",
 *     type="object",
 *     @OA\Property(
 *         property="id",
 *         type="integer"
 *     ),
 *     @OA\Property(
 *         property="reporter_id",
 *         type="integer"
 *     ),
 *     @OA\Property(
 *         property="status",
 *         type="string"
 *     ),
 *     @OA\Property(
 *         property="typeOfCategory_id",
 *         type="integer"
 *     ),
 *     @OA\Property(
 *         property="location_id",
 *         type="integer"
 *     ),
 *     @OA\Property(
 *         property="report_detail_id",
 *         type="integer"
 *     ),
 *     
 * )
 * 
 * @OA\Schema(
 *     schema="ReportCollection",
 *     type="object",
 *     @OA\Property(
 *         property="data",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/Report")
 *     ),
 *     @OA\Property(
 *         property="links",
 *         type="object",
 *         @OA\Property(
 *             property="first",
 *             type="string"
 *         ),
 *         @OA\Property(
 *             property="last",
 *             type="string"
 *         ),
 *         @OA\Property(
 *             property="prev",
 *             type="string"
 *         ),
 *         @OA\Property(
 *             property="next",
 *             type="string"
 *         )
 *     ),
 *     @OA\Property(
 *         property="meta",
 *         type="object",
 *         @OA\Property(
 *             property="current_page",
 *             type="integer"
 *         ),
 *         @OA\Property(
 *             property="from",
 *             type="integer"
 *         ),
 *         @OA\Property(
 *             property="last_page",
 *             type="integer"
 *         ),
 *         @OA\Property(
 *             property="path",
 *             type="string"
 *         ),
 *         @OA\Property(
 *             property="per_page",
 *             type="integer"
 *         ),
 *         @OA\Property(
 *             property="to",
 *             type="integer"
 *         ),
 *         @OA\Property(
 *             property="total",
 *             type="integer"
 *         )
 *     )
 * )
 */

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
