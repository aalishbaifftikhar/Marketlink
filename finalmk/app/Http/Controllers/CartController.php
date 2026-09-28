<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    private function items(Request $request): array
    {
        return $request->session()->get('cart', []);
    }

    private function save(Request $request, array $items): void
    {
        $request->session()->put('cart', $items);
    }

    /** @return array<int, array{farmer: \App\Models\Farmer, items: array, subtotal: float}> */
    public static function grouped(Request $request): array
    {
        $cart = $request->session()->get('cart', []);
        if (empty($cart)) {
            return [];
        }

        $products = Product::available()->with('farmer.user')->whereIn('id', array_keys($cart))->get()->keyBy('id');

        $groups = [];
        foreach ($cart as $productId => $qty) {
            $product = $products->get($productId);
            if (! $product) {
                continue; // no longer available — dropped from the cart view
            }

            $qty = min((int) $qty, $product->stock_quantity);
            $farmerId = $product->farmer_id;
            $groups[$farmerId]['farmer'] = $product->farmer;
            $groups[$farmerId]['items'][] = ['product' => $product, 'qty' => $qty];
        }

        foreach ($groups as $id => &$group) {
            $group['subtotal'] = collect($group['items'])->sum(fn ($i) => $i['product']->price * $i['qty']);
        }

        return $groups;
    }

    public function index(Request $request): View
    {
        $groups = self::grouped($request);

        return view('customer.cart', [
            'groups' => $groups,
            'grandTotal' => collect($groups)->sum('subtotal'),
            'count' => collect($groups)->sum(fn ($g) => count($g['items'])),
        ]);
    }

    public function add(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:99'],
        ]);

        $product = Product::available()->findOrFail($data['product_id']);
        $qty = $data['quantity'] ?? 1;

        $cart = $this->items($request);
        $existing = $cart[$product->id] ?? 0;

        if ($existing + $qty > $product->stock_quantity) {
            return back()->with('error', 'Only ' . $product->stock_quantity . ' ' . $product->unit . ' of ' . $product->name . ' available.');
        }

        $cart[$product->id] = $existing + $qty;
        $this->save($request, $cart);

        return back()->with('success', $product->name . ' added to your basket.');
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:0', 'max:99'],
        ]);

        $cart = $this->items($request);

        if ($data['quantity'] == 0) {
            unset($cart[$data['product_id']]);
        } else {
            $product = Product::available()->find($data['product_id']);
            if (! $product || $data['quantity'] > $product->stock_quantity) {
                return back()->with('error', 'Not enough stock for that quantity.');
            }
            $cart[$data['product_id']] = (int) $data['quantity'];
        }

        $this->save($request, $cart);

        return back()->with('success', 'Basket updated.');
    }

    public function remove(Request $request): RedirectResponse
    {
        $data = $request->validate(['product_id' => ['required', 'exists:products,id']]);

        $cart = $this->items($request);
        unset($cart[$data['product_id']]);
        $this->save($request, $cart);

        return back()->with('success', 'Item removed from your basket.');
    }

    public function clear(Request $request): RedirectResponse
    {
        $this->save($request, []);

        return back()->with('success', 'Basket cleared.');
    }
}
