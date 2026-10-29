<?php

namespace App\Imports;

use App\Models\ImportError;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\Failure;

class ProductsImport implements ToModel, WithHeadingRow, WithChunkReading, WithValidation, SkipsOnFailure, SkipsEmptyRows
{
    public function __construct(private string $batchId) {}

    public function model(array $row): ?Model
    {
        // updateOrCreate menjaga import tetap idempotent (aman diulang).
        Product::updateOrCreate(
            ['name' => $row['name'], 'category_id' => $row['category_id']],
            ['price' => $row['price']],
        );

        // Model sudah disimpan di atas, jadi return null supaya library tidak insert lagi.
        return null;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'category_id' => ['required', 'exists:categories,id'],
            'price' => ['required', 'integer', 'min:0'],
        ];
    }

    /**
     * Dipanggil library untuk row yang gagal validasi.
     * Satu row yang salah di dua kolom menghasilkan dua Failure,
     * jadi digabung per nomor row supaya tercatat sebagai satu error.
     */
    public function onFailure(Failure ...$failures): void
    {
        foreach (collect($failures)->groupBy(fn (Failure $f) => $f->row()) as $rowNumber => $rowFailures) {
            ImportError::create([
                'batch_id' => $this->batchId,
                'row_number' => $rowNumber, // baris 1 = header, jadi data pertama = 2
                'row_data' => $rowFailures->first()->values(),
                'message' => $rowFailures->flatMap(fn (Failure $f) => $f->errors())->implode(' '),
            ]);
        }
    }

    public function chunkSize(): int
    {
        return 500;
    }
}