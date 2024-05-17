<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTypeOfCategoryRequest;
use App\Http\Requests\UpdateTypeOfCategoryRequest;
use App\Models\Category;
use App\Models\TypeOfCategory;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class TypeOfCategoryController extends Controller
{
    public function showTypesWithCategories()
    {
        // Fetch all types with their associated categories
        $types = TypeOfCategory::with('category')->get();

        // Initialize an array to hold the result
        $result = [];

        // Iterate over the types and manually construct the response structure
        foreach ($types as $type) {
            $typeName = $type->type;
            $categoryName = $type->category->name;

            // Check if the type is already in the result array
            if (!isset($result[$typeName])) {
                $result[$typeName] = [
                    'type' => $typeName,
                    'categories' => []
                ];
            }

            // Add the category to the type's categories array
            $result[$typeName]['categories'][] = $categoryName;
        }

        // Re-index the result array to remove the type names as keys
        $result = array_values($result);

        // Return the data as a JSON response
        return response()->json($result);
    }


    public function addType(Request $request)
    {
        // Validate the incoming request data
        $validator = Validator::make($request->all(), [
            'type' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id', // Assuming you're providing category_id
        ]);
    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
    
        // Retrieve the validated input data
        $type = $request->input('type');
        $categoryId = $request->input('category_id');
    
        // Create the new type
        $typeOfCategory = TypeOfCategory::create([
            'type' => $type,
            'category_id' => $categoryId, // Assign the provided category_id
        ]);
    
        return response()->json([
            'message' => 'Type and categories added successfully',
            'type' => $typeOfCategory,
        ], 201);
    }


    public function addCategoryToType(Request $request, $typeId)
    {
        // Validate the incoming request data
        $validator = Validator::make($request->all(), [
            'category_name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Find the type by ID
        $type = TypeOfCategory::find($typeId);

        if (!$type) {
            return response()->json(['error' => 'Type not found'], 404);
        }

        // Retrieve the validated input data
        $categoryName = $request->input('category_name');

        // Create or find the category
        $category = Category::firstOrCreate(['name' => $categoryName]);

        // Associate the category with the type
        $type->category()->associate($category);
        $type->save();

        return response()->json([
            'message' => 'Category added to type successfully',
            'type' => $type,
            'category' => $category,
        ], 201);
    }


    public function deleteTypeOfCategory($typeId)
    {
        // Find the type by ID
        $type = TypeOfCategory::find($typeId);
    
        if (!$type) {
            return response()->json(['error' => 'Type not found'], 404);
        }
    
        // Find all categories associated with the type
        $categories = $type->category()->get();
    
        // Loop through each category and delete associated reports
        foreach ($categories as $category) {
            // Get the reports associated with the category's type
            $reports = $type->reports()->where('category_id', $category->id)->get();
    
            // Delete associated reports
            foreach ($reports as $report) {
                $report->delete();
            }
        }
    
        // Delete the categories associated with the type
        $type->category()->delete();
    
        // Delete the type
        $type->delete();
    
        return response()->json([
            'message' => 'Type of category and associated categories deleted successfully',
        ]);
    }
    
    public function deleteCategory($categoryId)
    {
        // Find the category by ID
        $category = Category::find($categoryId);

        if (!$category) {
            return response()->json(['error' => 'Category not found'], 404);
        }

        // Delete the category
        $category->delete();

        return response()->json([
            'message' => 'Category deleted successfully',
        ]);
    }
    
}    
