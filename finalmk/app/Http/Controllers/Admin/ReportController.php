<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Farmer;
use App\Models\Market;
use App\Models\Order;
use App\Models\Product;
use App\Models\Report;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class ReportController extends Controller
{
    public const TYPES = [
        'orders' => 'Orders Report',
        'farmers' => 'Farmers Performance',
        'markets' => 'Markets Summary',
        'customers' => 'Customers Summary',
        'products' => 'Products Catalogue',
        'reviews' => 'Reviews & Ratings',
    ];

    public function index(): View
    {
        $reports = Report::with('generator')->latest()->paginate(12);

        return view('admin.reports.index', [
            'reports' => $reports,
            'types' => self::TYPES,
        ]);
    }

    public function generate(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', 'in:' . implode(',', array_keys(self::TYPES))],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);

        $rows = $this->rowsFor($data['type'], $data['date_from'] ?? null, $data['date_to'] ?? null);
        $filename = Str::slug(self::TYPES[$data['type']]) . '-' . now()->format('Ymd-His') . '.csv';

        $report = Report::create([
            'generated_by' => $request->user()->id,
            'report_type' => $data['type'],
            'params' => ['date_from' => $data['date_from'] ?? null, 'date_to' => $data['date_to'] ?? null, 'rows' => count($rows) - 1],
            'file_path' => 'reports/' . $filename,
        ]);

        // Store CSV on disk so it can be re-downloaded later.
        $diskPath = storage_path('app/public/' . $report->file_path);
        @mkdir(dirname($diskPath), 0755, true);
        $fp = fopen($diskPath, 'w');
        foreach ($rows as $row) {
            fputcsv($fp, $row);
        }
        fclose($fp);

        return redirect()->route('admin.reports.index')->with('success', self::TYPES[$data['type']] . ' generated with ' . (count($rows) - 1) . ' rows.');
    }

    public function download(Report $report): StreamedResponse
    {
        $path = storage_path('app/public/' . $report->file_path);
        abort_unless($report->file_path && file_exists($path), 404, 'Report file not found.');

        return Response::streamDownload(function () use ($path) {
            readfile($path);
        }, basename($path), ['Content-Type' => 'text/csv']);
    }

    public function destroy(Report $report): RedirectResponse
    {
        $path = storage_path('app/public/' . $report->file_path);
        if ($report->file_path && file_exists($path)) {
            @unlink($path);
        }
        $report->delete();

        return back()->with('success', 'Report deleted.');
    }

    private function rowsFor(string $type, ?string $from, ?string $to): array
    {
        return match ($type) {
            'orders' => $this->ordersRows($from, $to),
            'farmers' => $this->farmersRows($from, $to),
            'markets' => $this->marketsRows(),
            'customers' => $this->customersRows($from, $to),
            'products' => $this->productsRows(),
            'reviews' => $this->reviewsRows($from, $to),
            default => [['No data']],
        };
    }

    private function ordersRows(?string $from, ?string $to): array
    {
        $orders = Order::with(['customer', 'farmer', 'market'])
            ->when($from, fn($q) => $q->whereDate('placed_at', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('placed_at', '<=', $to))
            ->orderBy('placed_at')->get();

        $rows = [['Order #', 'Date Placed', 'Customer', 'Farmer', 'Market', 'Pickup Date', 'Slot', 'Status', 'Items', 'Total']];
        foreach ($orders as $o) {
            $rows[] = [
                $o->order_number,
                $o->placed_at?->format('Y-m-d H:i'),
                $o->customer?->name,
                $o->farmer?->stall_name,
                $o->market?->name,
                $o->pickup_date?->format('Y-m-d'),
                $o->pickup_slot,
                $o->status,
                $o->items->sum('quantity'),
                number_format((float) $o->total_amount, 2, '.', ''),
            ];
        }

        return $rows;
    }

    private function farmersRows(?string $from, ?string $to): array
    {
        $farmers = Farmer::with('user')
            ->withCount(['orders as total_orders' => fn($q) => $q->when($from, fn($qq) => $qq->whereDate('placed_at', '>=', $from))->when($to, fn($qq) => $qq->whereDate('placed_at', '<=', $to))])
            ->withCount(['orders as completed_orders' => fn($q) => $q->where('status', 'completed')->when($from, fn($qq) => $qq->whereDate('placed_at', '>=', $from))->when($to, fn($qq) => $qq->whereDate('placed_at', '<=', $to))])
            ->withSum(['orders as revenue' => fn($q) => $q->where('status', 'completed')->when($from, fn($qq) => $qq->whereDate('placed_at', '>=', $from))->when($to, fn($qq) => $qq->whereDate('placed_at', '<=', $to))], 'total_amount')
            ->withCount('products')
            ->get();

        $rows = [['Stall Name', 'Owner', 'Email', 'Status', 'Products', 'Total Orders', 'Completed', 'Revenue', 'Rating']];
        foreach ($farmers as $f) {
            $status = $f->user?->status ?? 'pending';
            $rows[] = [
                $f->stall_name,
                $f->contact_person ?? $f->user?->name,
                $f->user?->email,
                $status === 'active' ? 'Approved' : ucfirst($status),
                $f->products_count,
                $f->total_orders,
                $f->completed_orders,
                number_format((float) $f->revenue, 2, '.', ''),
                number_format((float) $f->averageRating(), 1),
            ];
        }

        return $rows;
    }

    private function marketsRows(): array
    {
        $markets = Market::withCount('farmers')
            ->withCount(['orders' => fn($q) => $q->where('status', 'completed')])
            ->withSum(['orders as revenue' => fn($q) => $q->where('status', 'completed')], 'total_amount')
            ->orderBy('name')->get();

        $rows = [['Market', 'City', 'Address', 'Days', 'Hours', 'Farmers', 'Completed Orders', 'Revenue', 'Status']];
        foreach ($markets as $m) {
            $rows[] = [
                $m->name,
                $m->city,
                $m->address,
                $m->operatingDaysLabel() ?: '—',
                ($m->open_time && $m->close_time) ? $m->open_time . '–' . $m->close_time : '—',
                $m->farmers_count,
                $m->orders_count,
                number_format((float) $m->revenue, 2, '.', ''),
                $m->is_active ? 'Active' : 'Hidden',
            ];
        }

        return $rows;
    }

    private function customersRows(?string $from, ?string $to): array
    {
        $customers = \App\Models\User::where('role', 'customer')
            ->withCount(['orders' => fn($q) => $q->when($from, fn($qq) => $qq->whereDate('placed_at', '>=', $from))->when($to, fn($qq) => $qq->whereDate('placed_at', '<=', $to))])
            ->withCount(['orders as completed_orders' => fn($q) => $q->where('status', 'completed')->when($from, fn($qq) => $qq->whereDate('placed_at', '>=', $from))->when($to, fn($qq) => $qq->whereDate('placed_at', '<=', $to))])
            ->withSum(['orders as spent' => fn($q) => $q->where('status', 'completed')], 'total_amount')
            ->orderByDesc('spent')->get();

        $rows = [['Name', 'Email', 'Phone', 'Status', 'Joined', 'Orders', 'Completed', 'Total Spent']];
        foreach ($customers as $c) {
            $rows[] = [
                $c->name,
                $c->email,
                $c->phone ?? '—',
                $c->status,
                $c->created_at?->format('Y-m-d'),
                $c->orders_count,
                $c->completed_orders,
                number_format((float) $c->spent, 2, '.', ''),
            ];
        }

        return $rows;
    }

    private function productsRows(): array
    {
        $products = Product::with(['farmer', 'category'])->orderBy('farmer_id')->orderBy('name')->get();

        $rows = [['Product', 'Farmer', 'Category', 'Price', 'Unit', 'Stock', 'Available', 'Active', 'Rating']];
        foreach ($products as $p) {
            $rows[] = [
                $p->name,
                $p->farmer?->stall_name,
                $p->category?->name ?? '—',
                number_format((float) $p->price, 2, '.', ''),
                $p->unit,
                $p->stock_quantity,
                $p->is_available ? 'Yes' : 'No',
                $p->is_active ? 'Yes' : 'No',
                number_format((float) $p->averageRating(), 1),
            ];
        }

        return $rows;
    }

    private function reviewsRows(?string $from, ?string $to): array
    {
        $reviews = Review::with(['user', 'farmer', 'product'])
            ->when($from, fn($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('created_at', '<=', $to))
            ->orderBy('created_at')->get();

        $rows = [['Date', 'Customer', 'Farmer', 'Product', 'Rating', 'Comment', 'Farmer Response', 'Hidden']];
        foreach ($reviews as $r) {
            $rows[] = [
                $r->created_at?->format('Y-m-d'),
                $r->user?->name,
                $r->farmer?->stall_name,
                $r->product?->name ?? '(farmer review)',
                $r->rating,
                Str::limit($r->comment ?? '', 120),
                Str::limit($r->farmer_response ?? '', 120),
                $r->is_hidden ? 'Yes' : 'No',
            ];
        }

        return $rows;
    }
}
