<?php

namespace App\Http\Controllers;

use App\Services\VisualSearchService;
use Illuminate\Http\Request;

class VisualSearchController extends Controller
{
    protected VisualSearchService $visualService;

    public function __construct(VisualSearchService $visualService)
    {
        $this->visualService = $visualService;
    }

    public function index()
    {
        return view('visual-search.index');
    }

    public function search(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $file = $request->file('image');
        $result = $this->visualService->searchByImage($file);

        // Store uploaded image temporarily in public/uploads/visual
        $imagePath = $file->store('uploads/visual', 'public');

        return view('visual-search.results', [
            'uploadedImage' => '/storage/' . $imagePath,
            'detected' => $result['detected_characteristics'],
            'products' => $result['products'],
            'confidence' => $result['match_confidence'],
        ]);
    }
}
