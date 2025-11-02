<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\Post;
use App\Http\Requests\PostRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Traits\FileUpload;
class PostController extends BaseController
{
    use FileUpload;
    public function index(Request $request)
    {
        try {
            // Get the 'perPage' parameter from the request, default to 15
            $perPage = $request->per_page ?? 15;
            
            // Initialize query
            $query = Post::query();
            
            // Apply filters
            if ($request->has('is_published') && $request->is_published) {
                $query->published();
            }
            
            if ($request->has('search') && $request->search) {
                $query->where(function($q) use ($request) {
                    $q->where('title', 'like', '%' . $request->search . '%')
                      ->orWhere('content', 'like', '%' . $request->search . '%')
                      ->orWhere('excerpt', 'like', '%' . $request->search . '%');
                });
            }
            
            // Apply sorting and pagination
            $posts = $query->orderBy('created_at', 'DESC')->paginate($perPage);
    
            // Return paginated response
            return $this->sendResponse($posts, 'Posts retrieved successfully.');
        } catch (\Exception $e) {
            return $this->sendError($e, ["Line- " . $e->getLine() . ' ' . $e->getMessage()], 500);
        }
    }

    public function show(Post $post)
    {
        try {
            // Find the post by ID

            // Check if the post exists
            if (!$post) {
                return $this->sendError('Post not found.', [], 404);
            }

            // Return the post data
            return $this->sendResponse($post, 'Post retrieved successfully.');
        } catch (\Exception $e) {
            return $this->sendError($e, ["Line- " . $e->getLine() . ' ' . $e->getMessage()], 500);
        }
    }

    public function store(PostRequest $request)
    {
        try {
            // Get validated data from the request
            $validated = $request->getValidatedData();

            // Handle featured image upload
            if ($request->hasFile('featured_image')) {
                $file = $request->file('featured_image');
                $filePath = $this->FileUpload($file, 'posts');
                $validated['featured_image'] = $filePath;
            }

            // Handle meta image upload
            if ($request->hasFile('meta_image')) {
                $filePath = $this->FileUpload($request->file('meta_image'), 'posts/meta');
                $validated['meta_image'] = $filePath;
            }

            // Create the post
            $post = Post::create($validated);

            // Load the fresh instance with any relations if needed
            $post = Post::find($post->id);

            return $this->sendResponse($post, 'Post created successfully.', 201);
        } catch (\Exception $e) {
            return $this->sendError($e, ["Line- " . $e->getLine() . ' ' . $e->getMessage()], 500);
        }
    }

    public function update(PostRequest $request, Post $post)
    {
        try {


            // Get validated data from the request
            $validated = $request->getValidatedData();

            // Handle featured image upload
            if ($request->hasFile('featured_image')) {
                // Delete old featured image
                if ($post->featured_image && Storage::disk('public')->exists($post->featured_image)) {
                    Storage::disk('public')->delete($post->featured_image);
                }
                $filePath = $this->FileUpload($request->file('featured_image'), 'posts');
                $validated['featured_image'] = $filePath;
            }

            // Handle meta image upload
            if ($request->hasFile('meta_image')) {
                // Delete old meta image
                if ($post->meta_image && Storage::disk('public')->exists($post->meta_image)) {
                    Storage::disk('public')->delete($post->meta_image);
                }
                $filePath = $this->FileUpload($request->file('meta_image'), 'posts/meta');
                $validated['meta_image'] = $filePath;
            }

            // Update the post
            $post->update($validated);

            // Return the updated post
            return $this->sendResponse($post, 'Post updated successfully.');
        } catch (\Exception $e) {
            return $this->sendError($e, ["Line- " . $e->getLine() . ' ' . $e->getMessage()], 500);
        }
    }

    public function destroy(Post $post)
    {
        try {

            // Check if the post exists
            if (!$post) {
                return $this->sendError('Post not found.', [], 404);
            }

            // Delete featured image
            if ($post->featured_image && Storage::disk('public')->exists($post->featured_image)) {
                Storage::disk('public')->delete($post->featured_image);
            }

            // Delete meta image
            if ($post->meta_image && Storage::disk('public')->exists($post->meta_image)) {
                Storage::disk('public')->delete($post->meta_image);
            }

            // Delete the post
            $post->delete();

            return $this->sendResponse([], 'Post deleted successfully.');
        } catch (\Exception $e) {
            return $this->sendError($e, ["Line- " . $e->getLine() . ' ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get published posts only
     */
    public function publishedPosts(Request $request)
    {
        try {
            $perPage = $request->per_page ?? 15;
            
            $posts = Post::published()
                        ->orderBy('published_at', 'DESC')
                        ->paginate($perPage);
    
            return $this->sendResponse($posts, 'Published posts retrieved successfully.');
        } catch (\Exception $e) {
            return $this->sendError($e, ["Line- " . $e->getLine() . ' ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get post by slug
     */
    public function showBySlug($slug)
    {
        try {
            // Find the post by slug
            $post = Post::where('slug', $slug)->first();

            // Check if the post exists
            if (!$post) {
                return $this->sendError('Post not found.', [], 404);
            }

            // Return the post data
            return $this->sendResponse($post, 'Post retrieved successfully.');
        } catch (\Exception $e) {
            return $this->sendError($e, ["Line- " . $e->getLine() . ' ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update post status only
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'is_published' => 'required|boolean',
                'published_at' => 'nullable|date|after_or_equal:now',
            ]);

            $post = Post::find($id);

            if (!$post) {
                return $this->sendError('Post not found.', [], 404);
            }

            $post->update($validated);

            return $this->sendResponse($post, 'Post status updated successfully.');
        } catch (\Exception $e) {
            return $this->sendError($e, ["Line- " . $e->getLine() . ' ' . $e->getMessage()], 500);
        }
    }
}