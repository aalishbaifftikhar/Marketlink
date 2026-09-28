<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class FarmerStockTemplateController extends Controller
{
    public function index(Request $request): View
    {
        $farmers = $request->user()->farmers;
        abort_unless($farmers, 404);

        $templates = $farmers->stockTemplates()->latest()->get()->each(
            fn ($template) => $template->items_count = count($template->items ?? [])
        );

        return view('farmer.templates.index', [
            'farmer' => $farmers,
            'templates' => $templates,
            'products' => $farmers->products()->orderBy('name')->get(['id', 'name', 'price', 'unit', 'category_id', 'stock_quantity']),
            'categories' => Category::active()->orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $farmers = $request->user()->farmers;
        abort_unless($farmers, 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.name' => ['required', 'string', 'max:150'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
            'items.*.unit' => ['required', 'string', 'max:20'],
            'items.*.stock_quantity' => ['required', 'integer', 'min:0'],
            'items.*.category_id' => ['nullable', 'integer', 'exists:categories,id'],
        ]);

        $farmers->stockTemplates()->create([
            'name' => $data['name'],
            'items' => $data['items'],
        ]);

        return back()->with('success', 'Stock template "' . $data['name'] . '" saved.');
    }

    public function destroy(Request $request, int $template): RedirectResponse
    {
        $farmers = $request->user()->farmers;
        abort_unless($farmers, 404);
        $farmers->stockTemplates()->where('id', $template)->delete();

        return back()->with('success', 'Template deleted.');
    }

    public function apply(Request $request, int $template): RedirectResponse
    {
        $farmers = $request->user()->farmers;
        abort_unless($farmers, 404);

        $stockTemplate = $farmers->stockTemplates()->where('id', $template)->firstOrFail();
        $applied = 0;
        $updated = 0;

        foreach ($stockTemplate->items ?? [] as $item) {
            $product = $farmers->products()->where('name', $item['name'])->first();
            if ($product) {
                $product->update([
                    'price' => $item['price'],
                    'unit' => $item['unit'],
                    'stock_quantity' => $item['stock_quantity'],
                    'category_id' => $item['category_id'] ?? $product->category_id,
                    'is_available' => true,
                    'is_active' => true,
                ]);
                $updated++;
            } else {
                $farmers->products()->create([
                    'name' => $item['name'],
                    'slug' => $this->makeUniqueSlug($farmers, $item['name']),
                    'price' => $item['price'],
                    'unit' => $item['unit'],
                    'stock_quantity' => $item['stock_quantity'],
                    'category_id' => $item['category_id'] ?? null,
                    'description' => null,
                    'is_available' => true,
                    'is_active' => true,
                ]);
                $applied++;
            }
        }

        return back()->with('success', "Template applied: {$applied} new products added, {$updated} existing products restocked.");
    }

    private function makeUniqueSlug($farmers, string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 2;
        while ($farmers->products()->where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}
