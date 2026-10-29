<?php

namespace App\Http\Controllers;

use App\Exports\SalesReportExport;
use App\Jobs\ProcessProductImport;
use App\Models\Category;
use App\Models\ImportError;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\DB;


class ReportController extends Controller
{
    public function index(Request $request): View
    {
        [$from, $to] = $this->resolveDateRange($request);
        $categoryId = $this->resolveCategoryId($request);
        $query = $this->filteredQuery($from, $to, $categoryId);

        return view('reports.index', [
            'totalPenjualan' => $query->sum('total'),
            'jumlahTransaksi' => $query->count(),
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'categories' => Category::orderBy('name')->get(),
            'categoryId' => $categoryId,
        ]);
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        [$from, $to] = $this->resolveDateRange($request);
        $query = $this->filteredQuery($from, $to, $this->resolveCategoryId($request));

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['ID', 'Tanggal', 'Kasir', 'Total']);

            foreach ($query->lazyById() as $t) {
                fputcsv($out, [$t->id, $t->created_at, $t->user->name, $t->total]);
            }

            fclose($out);
        }, 'laporan-penjualan.csv');
    }

    public function exportExcel(Request $request): BinaryFileResponse
    {
        [$from, $to] = $this->resolveDateRange($request);

        return Excel::download(
            new SalesReportExport($this->filteredQuery($from, $to, $this->resolveCategoryId($request))),
            'laporan-penjualan.xlsx'
        );
    }

    public function exportPdf(Request $request): Response
    {
        [$from, $to] = $this->resolveDateRange($request);

    $topProducts = DB::table('transaction_details')
        ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
        ->join('products', 'transaction_details.product_id', '=', 'products.id')
        ->whereBetween('transactions.created_at', [$from, $to])
        ->select('products.name', DB::raw('SUM(transaction_details.qty) as total_qty'))
        ->groupBy('products.id', 'products.name')
        ->orderByDesc('total_qty')
        ->limit(5)
        ->get();

        $pdf = Pdf::loadView('reports.pdf', [
            'totalPenjualan' => $this->filteredQuery($from, $to)->sum('total'),
            'jumlahTransaksi' => $this->filteredQuery($from, $to)->count(),
            'transactions' => $this->filteredQuery($from, $to)->latest()->limit(50)->get(),
            'topProducts' => $topProducts,
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
        ]);

        return $pdf->download('laporan-penjualan.pdf');
}
    public function importForm(Request $request): View
    {
        $batch = $request->query('batch');

        return view('reports.import', [
            'batch' => $batch,
            'errors_list' => $batch
                ? ImportError::where('batch_id', $batch)->orderBy('row_number')->get()
                : collect(),
        ]);
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'csv' => ['required', 'file', 'mimes:csv,txt'],
        ]);

        $path = $request->file('csv')->store('imports');
        $batchId = (string) Str::uuid();

        ProcessProductImport::dispatch($path, $batchId);

        return redirect()
            ->route('import.create', ['batch' => $batchId])
            ->with('success', 'Import sedang diproses di background.');
    }

    private function resolveDateRange(Request $request): array
    {
        $from = $request->date('from')?->startOfDay() ?? now()->startOfMonth();
        $to = $request->date('to')?->endOfDay() ?? now()->endOfDay();

        return [$from, $to];
    }

    private function resolveCategoryId(Request $request): ?int
    {
        $id = $request->integer('category_id');

        if ($id <= 0 || ! Category::whereKey($id)->exists()) {
            return null;
        }

        return $id;
    }

    private function filteredQuery($from, $to, ?int $categoryId = null): Builder
    {
        return Transaction::with('user')
            ->whereBetween('created_at', [$from, $to])
            ->when($categoryId, function (Builder $q, int $id) {
                $q->whereHas('details.product', fn ($p) => $p->where('category_id', $id));
            });
    }
}