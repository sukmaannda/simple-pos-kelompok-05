@extends('layouts.app')

@section('title', 'Import Produk')

@section('content')
    <h1 class="text-lg font-semibold mb-4">Import Produk dari CSV</h1>

    @if (session('success'))
        <div class="bg-green-50 text-green-700 p-3 rounded-md mb-4">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('import.store') }}" enctype="multipart/form-data" class="max-w-md">
        @csrf

        <label class="block mb-3">
            <span class="text-sm font-medium">File CSV</span>
            <input type="file" name="csv" accept=".csv,text/csv" class="mt-1 block w-full text-sm">
            @error('csv')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </label>

        <p class="text-xs text-slate-500 mb-3">
            Column yang dibutuhkan: <code>name</code>, <code>category_id</code>, <code>price</code>.
        </p>

        <button type="submit" class="bg-slate-900 text-white px-4 py-2 rounded-md text-sm">Import</button>
    </form>

    {{-- Tabel error HARUS di luar </form> --}}
    @if ($batch)
        <div class="mt-8 max-w-2xl">
            <h2 class="font-semibold mb-2">Row yang gagal di-import</h2>

            @if ($errors_list->isEmpty())
                <p class="text-sm text-slate-500">
                    Belum ada row bermasalah. Kalau worker belum selesai, refresh halaman ini sebentar lagi.
                </p>
            @else
                <table class="w-full text-sm border">
                    <thead class="bg-slate-100">
                        <tr>
                            <th class="border px-3 py-2 text-left">Row</th>
                            <th class="border px-3 py-2 text-left">Data</th>
                            <th class="border px-3 py-2 text-left">Pesan error</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($errors_list as $e)
                            <tr>
                                <td class="border px-3 py-2">{{ $e->row_number }}</td>
                                <td class="border px-3 py-2"><code>{{ implode(',', $e->row_data ?? []) }}</code></td>
                                <td class="border px-3 py-2 text-red-600">{{ $e->message }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    @endif
@endsection