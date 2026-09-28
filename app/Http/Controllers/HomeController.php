<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Farmer;
use App\Models\Market;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $stats = [
            'farmers' => Farmer::whereHas('user', fn ($q) => $q->where('status', 'active'))->count(),
            'customers' => User::where('role', 'customer')->count(),
            'markets' => Market::active()->count(),
            'orders' => Order::count(),
        ];

        return view('home', [
            'stats' => $stats,
            'markets' => Market::active()->withCount('farmers')->take(6)->get(),
            'farmers' => Farmer::whereHas('user', fn ($q) => $q->where('status', 'active'))
                ->withCount('products')
                ->get()
                ->sortByDesc(fn ($f) => $f->averageRating())
                ->take(4)
                ->values(),
            'products' => Product::available()->with(['farmer', 'category'])->latest()->take(8)->get(),
            'categories' => Category::active()->orderBy('sort_order')->take(6)->get(),
        ]);
    }

    public function about(): View
    {
        return view('about');
    }

    public function contact(): View
    {
        return view('contact', ['settings' => settings()]);
    }
}
