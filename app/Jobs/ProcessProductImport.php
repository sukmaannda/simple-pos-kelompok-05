<?php

namespace App\Jobs;

use App\Models\ImportError;
use App\Models\Product;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProcessProductImport implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public array $backoff = [10, 60];
    public int $timeout = 60;

    public function __construct(
        private string $csvPath,
        private string $batchId,
    ) {}

    public function handle(): void
    {
        // Bersihkan error dari attempt sebelumnya supaya retry tidak mencatat dobel.
        ImportError::where('batch_id', $this->batchId)->delete();

        // Tanpa FILE_SKIP_EMPTY_LINES: nomor baris tetap sesuai file asli.
        $lines = file(Storage::path($this->csvPath), FILE_IGNORE_NEW_LINES);
        $header = str_getcsv(array_shift($lines) ?? '');
        $header[0] = ltrim($header[0] ?? '', "\xEF\xBB\xBF"); // buang BOM dari Excel
        $header = array_map('trim', $header);

        foreach ($lines as $index => $line) {
            $rowNumber = $index + 2; // baris 1 = header

            if (trim($line) === '') {
                continue;
            }

            $row = str_getcsv($line);

            if (count($row) !== count($header)) {
                $this->recordError($rowNumber, $row, 'Jumlah kolom tidak sesuai dengan header.');
                continue;
            }

            $validator = Validator::make(array_combine($header, $row), [
                'name' => ['required', 'string'],
                'category_id' => ['required', 'exists:categories,id'],
                'price' => ['required', 'integer', 'min:0'],
            ]);

            if ($validator->fails()) {
                $this->recordError($rowNumber, $row, implode(' ', $validator->errors()->all()));
                continue;
            }

            $data = $validator->validated();

            Product::updateOrCreate(
                ['name' => $data['name'], 'category_id' => $data['category_id']],
                ['price' => $data['price']],
            );
        }

        Storage::delete($this->csvPath);
    }

    private function recordError(int $rowNumber, array $row, string $message): void
    {
        ImportError::create([
            'batch_id' => $this->batchId,
            'row_number' => $rowNumber,
            'row_data' => $row,
            'message' => $message,
        ]);
    }
}