<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ArticleController extends Controller
{

    public function index()
    {
        $articles = Article::when(request('query'), function ($q) {
            return $q->where('title', 'like', '%' . request('query') . '%');
        })
            ->paginate(5);
        return view('articles.index', compact('articles'));
    }

    public function create()
    {
        return view('articles.create');
    }


    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required | unique:articles',
            'content' => 'nullable | min:10',
            'cover_image' => 'required | image | mimes:png,webp,jpg,jpeg | max:1024' // 1MB
        ]);


        if ($request->cover_image) {
            $imgName = time() . '.' . $request->cover_image->extension(); // .png, .jpg
            $request->cover_image->storeAs('media/articles/', $imgName, 'public');
        }

        Article::create([
            'title' => $request->title,
            'content' => $request->content,
            'cover_image' => $imgName,
        ]);

        return redirect()->route('articles.index')->with('success', 'Article created successfully');
    }


    public function show(Article $article)
    {
        //
    }


    public function edit(Article $article)
    {
        return view('articles.edit', compact('article'));
    }


    public function update(Request $request, Article $article)
    {
        $request->validate([
            'title' => 'required | unique:articles,title,' . $article->id,
            'content' => 'nullable | min:10',
            'cover_image' => 'nullable | image | mimes:png,webp,jpg,jpeg | max:1024' // 1MB
        ]);


        if ($request->cover_image) {
            // Delete old image
            Storage::disk('public')->delete('media/articles/' . $article->cover_image);

            $imgName = time() . '.' . $request->cover_image->extension(); // .png, .jpg
            $request->cover_image->storeAs('media/articles/', $imgName, 'public');
        }

        $article->update([
            'title' => $request->title,
            'content' => $request->content,
            'cover_image' => $request->cover_image ? $imgName : $article->cover_image,
        ]);

        return redirect()->route('articles.index')->with('success', 'Article updated successfully');
    }


    public function destroy(Article $article)
    {
        $article->delete();
        Storage::disk('public')->delete('media/articles/' . $article->cover_image);

        return redirect()->route('articles.index')->with('success', 'Article deleted successfully');
    }
}
