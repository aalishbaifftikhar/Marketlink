<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FavoriteController extends Controller
{
    /**
     * Toggle a favorite for the signed-in user.
     * Types: farmer, product, market.
     */
    public function toggle(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', Rule::in(['farmer', 'product', 'market'])],
            'id' => ['required', 'integer'],
        ]);

        $modelClass = [
            'farmer' => \App\Models\Farmer::class,
            'product' => \App\Models\Product::class,
            'market' => \App\Models\Market::class,
        ][$validated['type']];

        $model = $modelClass::findOrFail($validated['id']);
        $favorited = $request->user()->toggleFavorite($validated['type'], $model->id);

        if ($request->expectsJson()) {
            return response()->json(['favorited' => $favorited]);
        }

        return back()->with('success', $favorited ? 'Added to favorites.' : 'Removed from favorites.');
    }
}
