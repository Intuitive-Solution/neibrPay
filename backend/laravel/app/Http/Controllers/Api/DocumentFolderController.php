<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HoaDocumentFolder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DocumentFolderController extends Controller
{
    /**
     * Display a listing of document folders.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = HoaDocumentFolder::forTenant($user->tenant_id)
            ->with('creator:id,name,email')
            ->withCount(['documents', 'children'])
            ->orderBy('name', 'asc');

        // If 'all' parameter is true, return all folders (for dropdowns)
        if ($request->boolean('all')) {
            // Return all folders without parent_id filtering
        } elseif ($request->has('parent_id')) {
            // Filter by parent_id to show only direct children
            // If parent_id is null, show root folders (no parent)
            // If parent_id is provided, show only folders with that parent
            if ($request->input('parent_id') === null || $request->input('parent_id') === '') {
                // Get root folders (no parent)
                $query->rootFolders();
            } else {
                // Get folders with specific parent_id
                $query->where('parent_id', $request->input('parent_id'));
            }
        } else {
            // If parent_id is not provided, default to root folders
            $query->rootFolders();
        }

        // If user is a resident, automatically filter by visible_to_residents = true
        if ($user->isResident()) {
            $query->visibleToResidents();
        } elseif ($request->has('visible_to_residents')) {
            // For admins/bookkeepers, allow filtering by visibility if requested
            $query->where('visible_to_residents', $request->boolean('visible_to_residents'));
        }

        $folders = $query->get();

        return response()->json([
            'data' => $folders,
            'meta' => [
                'total' => $folders->count(),
            ],
        ]);
    }

    /**
     * Store a newly created folder.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        // Only admins can create folders
        if (!$user->isAdmin()) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Only admins can create folders',
            ], 403);
        }

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('hoa_document_folders')
                    ->where('tenant_id', $user->tenant_id)
                    ->where('parent_id', $request->input('parent_id'))
                    ->whereNull('deleted_at'),
            ],
            'description' => 'nullable|string|max:1000',
            'parent_id' => [
                'nullable',
                'integer',
                'exists:hoa_document_folders,id',
                function ($attribute, $value, $fail) use ($user) {
                    if ($value !== null) {
                        $parent = HoaDocumentFolder::where('id', $value)
                            ->where('tenant_id', $user->tenant_id)
                            ->withoutTrashed()
                            ->first();
                        if (!$parent) {
                            $fail('The selected parent folder does not exist or does not belong to your tenant.');
                        }
                    }
                },
            ],
            'visible_to_residents' => 'nullable|boolean',
        ]);

        $folder = HoaDocumentFolder::create([
            'tenant_id' => $user->tenant_id,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'parent_id' => $validated['parent_id'] ?? null,
            'visible_to_residents' => (bool) ($validated['visible_to_residents'] ?? false),
            'created_by' => $user->id,
        ]);

        $folder->load('creator:id,name,email');
        $folder->loadCount(['documents', 'children']);

        return response()->json([
            'data' => $folder,
            'message' => 'Folder created successfully',
        ], 201);
    }

    /**
     * Display the specified folder.
     */
    public function show(Request $request, HoaDocumentFolder $folder): JsonResponse
    {
        $user = $request->user();

        // Ensure user can access this folder
        if ($folder->tenant_id !== $user->tenant_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Residents can only see folders marked as visible
        if ($user->isResident() && !$folder->visible_to_residents) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $folder->load('creator:id,name,email');
        $folder->loadCount(['documents', 'children']);

        return response()->json([
            'data' => $folder,
        ]);
    }

    /**
     * Update the specified folder.
     */
    public function update(Request $request, HoaDocumentFolder $folder): JsonResponse
    {
        $user = $request->user();

        // Only admins can update folders
        if (!$user->isAdmin()) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Only admins can update folders',
            ], 403);
        }

        // Ensure user can access this folder
        if ($folder->tenant_id !== $user->tenant_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('hoa_document_folders')
                    ->where('tenant_id', $user->tenant_id)
                    ->where('parent_id', $request->input('parent_id', $folder->parent_id))
                    ->whereNull('deleted_at')
                    ->ignore($folder->id),
            ],
            'description' => 'nullable|string|max:1000',
            'parent_id' => [
                'nullable',
                'integer',
                'exists:hoa_document_folders,id',
                function ($attribute, $value, $fail) use ($user, $folder) {
                    if ($value !== null) {
                        // Prevent moving folder into itself or its descendants
                        if ($value == $folder->id) {
                            $fail('A folder cannot be moved into itself.');
                        }
                        
                        $parent = HoaDocumentFolder::where('id', $value)
                            ->where('tenant_id', $user->tenant_id)
                            ->withoutTrashed()
                            ->first();
                        if (!$parent) {
                            $fail('The selected parent folder does not exist or does not belong to your tenant.');
                        }

                        // Check if the new parent is a descendant of this folder
                        $descendant = HoaDocumentFolder::where('id', $value)
                            ->where('tenant_id', $user->tenant_id)
                            ->withoutTrashed()
                            ->first();
                        
                        if ($descendant) {
                            $current = $descendant;
                            while ($current !== null && $current->parent_id !== null) {
                                if ($current->parent_id == $folder->id) {
                                    $fail('A folder cannot be moved into its own descendant.');
                                }
                                $current = HoaDocumentFolder::where('id', $current->parent_id)
                                    ->where('tenant_id', $user->tenant_id)
                                    ->withoutTrashed()
                                    ->first();
                                
                                // If we can't find the parent (corrupted data), break to avoid infinite loop
                                if ($current === null) {
                                    break;
                                }
                            }
                        }
                    }
                },
            ],
            'visible_to_residents' => 'nullable|boolean',
        ]);

        $folder->update($validated);
        $folder->load('creator:id,name,email');
        $folder->loadCount(['documents', 'children']);

        return response()->json([
            'data' => $folder,
            'message' => 'Folder updated successfully',
        ]);
    }

    /**
     * Remove the specified folder.
     */
    public function destroy(Request $request, HoaDocumentFolder $folder): JsonResponse
    {
        $user = $request->user();

        // Only admins can delete folders
        if (!$user->isAdmin()) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Only admins can delete folders',
            ], 403);
        }

        // Ensure user can access this folder
        if ($folder->tenant_id !== $user->tenant_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Check if folder can be deleted
        if (!$folder->canDelete()) {
            return response()->json([
                'error' => 'Cannot delete folder',
                'message' => 'Folder cannot be deleted because it contains documents or subfolders.',
            ], 422);
        }

        // Soft delete the folder
        $folder->delete();

        return response()->json([
            'message' => 'Folder deleted successfully',
        ]);
    }
}

