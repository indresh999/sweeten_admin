<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\AppUser;
use Validator;

class PostController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id'    => 'required|exists:app_users,id',
            'content'    => 'nullable|string',
            'media_urls' => 'nullable|array',
            'type'       => 'required|in:text,image,video',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $post = Post::create([
            'user_id' => $request->user_id,
            'content' => $request->content,
            'media_urls' => $request->media_urls,
            'type' => $request->type,
        ]);

        return response()->json(['status' => true, 'message' => 'Post created', 'post' => $post]);
    }

    public function index(Request $request)
    {
        $posts = Post::with('user')->latest()->paginate(10);
        return response()->json(['status' => true, 'posts' => $posts]);
    }
}
