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

            // Initialize the type in the result array if it's not already there
            if (!isset($result[$typeName])) {
                $result[$typeName] = [
                    'id' => $type->id, // Add the type ID to the response for easier identification
                    'type' => $typeName,
                    'categories' => []
                ];
            }

            // If the type has an associated category, add it to the type's categories array
            if ($type->category) {
                $categoryName = $type->category->name;
                $categoryId = $type->category->id;
                $result[$typeName]['categories'][] = [
                    'id' => $categoryId,
                    'name' => $categoryName
                ];
            }
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
            'category_name' => 'nullable|string|max:255', // Make category_name optional
        ]);
    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
    
        // Retrieve the validated input data
        $type = $request->input('type');
        $categoryName = $request->input('category_name');
    
        // Initialize category_id as null
        $categoryId = null;
    
        // If a category name is provided, find or create the category
        if ($categoryName) {
            $category = Category::firstOrCreate(['name' => $categoryName]);
            $categoryId = $category->id;
        }
    
        // Find the type if it exists
        $typeOfCategory = TypeOfCategory::where('type', $type)->first();
    
        if ($typeOfCategory && $typeOfCategory->category_id === null) {
            // If the type exists and its category_id is null, update it with the new category_id
            $typeOfCategory->category_id = $categoryId;
            $typeOfCategory->save();
        } else {
            // If the type doesn't exist or its category_id is not null, create a new type
            $typeOfCategory = TypeOfCategory::create([
                'type' => $type,
                'category_id' => $categoryId, // Use the id of the found or created category, or null if no category name was provided
            ]);
        }
    
        return response()->json([
            'message' => 'Type added successfully',
            'type' => $typeOfCategory,
        ], 201);
    }


    public function deleteTypeOfCategory($typeId)
    {
        // Find the type by ID
        $type = TypeOfCategory::find($typeId);

        if (!$type) {
            return response()->json(['error' => 'Type not found'], 404);
        }

        // Find all types with the same name
        $types = TypeOfCategory::where('type', $type->type)->get();

        foreach ($types as $type) {
            // Delete the category associated with the type
            if ($type->category) {
                $type->category->delete();
            }

            // Delete the type
            $type->delete();
        }

        return response()->json([
            'message' => 'Types of category and associated categories deleted successfully',
        ]);
    }

    public function deleteCategory($categoryId)
    {
        $category = Category::find($categoryId);

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $category->delete();

        return response()->json(['message' => 'Category deleted successfully']);
    }
}
