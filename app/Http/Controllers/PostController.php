<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $post = Post::latest()->get();

        return response()->json([
            "success" => true,
            "message" => "Post successfully displayed",
            "data" => $post
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'title' => 'required',
            'content' => 'required',
            'category_id' => 'required',
            'image' => 'required|image|mimes:png,jpg,jpeg'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $image = $request->file('image');
        $image->storeAs('public/posts', $image->hashName());

        $post = Post::create([
            'title'     => $request->title,
            'content'   => $request->content,
            'category_id'   => $request->category_id,
            'image'     => $image->hashName()
        ]);

        return response()->json([
            "success" => true,
            "message" => "data post created successfully",
            "data" => $post
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $post = Post::find($id);

        return response()->json([
            "success" => true,
            "message" => "post successfully displayed",
            "data" => $post
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'content' => 'required',
            'category_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        //check if image is not empty
        if ($request->hasFile('image')) {
            //upload image
            $post = Post::findOrFail($id);

            $image = $request->file('image');
            $image->storeAs('public/posts', $image->hashName());


            //delete old image
            Storage::delete('public/posts/' . $post->image);

            //update post with new image
            $post->title = $request->title;
            $post->image = $image->hashName();
            $post->content = $request->content;
            $post->category_id = $request->category_id;
            $post->update();
        } else {

            //update post without image
            $post = Post::find($id);
            $post->title = $request->title;
            $post->content = $request->content;
            $post->category_id = $request->category_id;
            $post->update();
        }

        return response()->json([
            "success" => true,
            "message" => "category edited successfully",
            "data" => $post
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $post = Post::find($id);
        Storage::delete('public/posts/' . $post->image);
        $post->delete();

        return response()->json([
            "success" => true,
            "message" => "delete data successfully",
            "data" => $post
        ]);
    }
}