<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Word;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WordController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $words = Word::latest()->paginate(10);

        return $this->sendResponse($words, 'Words retrieved successfully.');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'word' => 'required|string|max:255',
            'pronunciation' => 'required|string|max:255',
            'part_of_speech' => 'required|string|max:255',
            'meaning' => 'required|string',
            'example_sentence' => 'required|string',
            'difficulty' => 'required|in:easy,medium,hard',
            'image' => 'nullable|image',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('images/upload', 'public');
        }

        $word = Word::create($validated);

        return response()->json([
            'success' => true,
            'data' => $word,
            'message' => 'Word created successfully.',
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $word = Word::find($id);

        if (!$word) {
            return $this->sendError('Word not found.');
        }

        return $this->sendResponse($word, 'Word retrieved successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $word = Word::findOrFail($id);

        $validated = $request->validate([
            'word' => 'required|string|max:255',
            'pronunciation' => 'required|string|max:255',
            'part_of_speech' => 'required|string|max:255',
            'meaning' => 'required|string',
            'example_sentence' => 'required|string',
            'difficulty' => 'required|in:easy,medium,hard',
            'image' => 'nullable|image',
        ]);

        if ($request->hasFile('image')) {
            if ($word->image) {
                Storage::disk('public')->delete($word->image);
            }

            $validated['image'] = $request->file('image')->store('images/upload', 'public');
        }

        $word->update($validated);

        return $this->sendResponse($word->fresh(), 'Word updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $word = Word::findOrFail($id);

        if ($word->image) {
            Storage::disk('public')->delete($word->image);
        }

        $word->delete();

        return response()->json([
            'success' => true,
            'message' => 'Word deleted successfully.',
        ], 200);
    }
}