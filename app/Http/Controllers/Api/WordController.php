<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Controllers\Api\Concerns\ValidatesCaptcha;
use App\Http\Controllers\Controller;
use App\Models\Word;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class WordController extends Controller
{
    use ApiResponse;
    use ValidatesCaptcha;

    /**
     * Display a listing of the authenticated user's words.
     */
    public function index(Request $request)
    {
        $words = $request->user()->words()->latest()->paginate(10);

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
            'captcha_token' => 'required|string',
            'captcha_answer' => 'required|string',
        ]);

        if (!$this->validateCaptcha($request)) {
            return $this->sendError('Invalid captcha.', [], 422);
        }

        unset($validated['captcha_token'], $validated['captcha_answer']);

        $validated['image'] = $this->handleImageUpload($request);

        $word = $request->user()->words()->create($validated);

        return response()->json([
            'success' => true,
            'data' => $word,
            'message' => 'Word created successfully.',
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $word = $request->user()->words()->find($id);

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
        $word = $request->user()->words()->findOrFail($id);

        $validated = $request->validate([
            'word' => 'required|string|max:255',
            'pronunciation' => 'required|string|max:255',
            'part_of_speech' => 'required|string|max:255',
            'meaning' => 'required|string',
            'example_sentence' => 'required|string',
            'difficulty' => 'required|in:easy,medium,hard',
            'image' => 'nullable|image',
            'remove_image' => 'nullable|boolean',
        ]);

        $validated['image'] = $this->handleImageUpload($request, $word, $request->boolean('remove_image'));

        $word->update($validated);

        return $this->sendResponse($word->fresh(), 'Word updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $word = $request->user()->words()->findOrFail($id);

        if ($word->image) {
            $this->deleteImage($word->image);
        }

        $word->delete();

        return response()->json([
            'success' => true,
            'message' => 'Word deleted successfully.',
        ], 200);
    }

    private function handleImageUpload(Request $request, ?Word $word = null, bool $removeExisting = false): ?string
    {
        if (!$request->hasFile('image')) {
            if ($removeExisting && $word?->image) {
                $this->deleteImage($word->image);
                return null;
            }

            return $word?->image;
        }

        $file = $request->file('image');
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $destination = public_path('img');

        if (!File::exists($destination)) {
            File::makeDirectory($destination, 0755, true);
        }

        if ($word?->image) {
            $this->deleteImage($word->image);
        }

        $file->move($destination, $filename);

        return 'img/' . $filename;
    }

    private function deleteImage(?string $path): void
    {
        if (!$path) {
            return;
        }

        $fullPath = public_path($path);
        if (File::exists($fullPath)) {
            File::delete($fullPath);
        }
    }
}
