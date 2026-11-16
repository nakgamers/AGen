<?php

namespace App\Imports;

use App\Models\BankAccount;
use App\Models\RmManager;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Events\AfterBatch;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Events\BeforeImport;
use Maatwebsite\Excel\Imports\HeadingRowExtractor;
use Maatwebsite\Excel\Validators\Failure;

class BankAccountImport implements ShouldQueue, SkipsOnError, SkipsOnFailure, ToModel, WithBatchInserts, WithChunkReading, WithEvents, WithHeadingRow, WithUpserts, WithValidation
{
    use SkipsErrors, SkipsFailures;

    public $batchId;

    protected array $rmCache = [];

    private array $rmNameCache = [];

    private int $pendingBatchRows = 0;

    private bool $pendingBatchFailed = false;

    public function __construct($batchId = null)
    {
        $this->batchId = $batchId;
    }

    protected function getRmIdByName(string $name): ?int
    {
        $normalizedName = strtolower(trim($name));

        if (array_key_exists($normalizedName, $this->rmCache)) {
            return $this->rmCache[$normalizedName];
        }

        $rm = RmManager::query()
            ->whereRaw('LOWER(TRIM(name)) = ?', [$normalizedName])
            ->first();

        $this->rmNameCache[$normalizedName] = $rm?->name;
        $this->rmCache[$normalizedName] = $rm?->id;

        return $this->rmCache[$normalizedName];
    }

    public function prepareForValidation($data, $index)
    {
        $cleanData = [];
        foreach ($data as $key => $value) {
            $cleanKey = trim(str_replace("\xEF\xBB\xBF", '', $key));
            $cleanData[$cleanKey] = $value;
        }
        $data = $cleanData;

        if (isset($data['no_rekening']) && is_scalar($data['no_rekening'])) {
            $data['no_rekening'] = trim((string) $data['no_rekening']);
        }

        $rmName = '';
        foreach (['rm_pengelola', 'rm_manager', 'nama_rm'] as $rmColumn) {
            if (isset($data[$rmColumn]) && is_scalar($data[$rmColumn]) && trim((string) $data[$rmColumn]) !== '') {
                $rmName = trim((string) $data[$rmColumn]);
                break;
            }
        }

        if ($rmName === '') {
            $data['rm_pengelola'] = null;
        } else {
            $normalizedRmName = strtolower($rmName);
            $this->getRmIdByName($rmName);
            $data['rm_pengelola'] = $this->rmNameCache[$normalizedRmName] ?? $rmName;
        }

        $data['noa_saat_ini'] = ! empty($data['noa_saat_ini']) ? (int) $data['noa_saat_ini'] : 1;

        if (isset($data['bank']) && is_string($data['bank'])) {
            $data['bank'] = strtolower(trim($data['bank']));
        }

        $cleanCurrency = function ($value) {
            if ($value === null || $value === '') {
                return 0.0;
            }
            if (is_string($value)) {
                $value = str_replace(['Rp', 'rp', '.', ',', ' '], '', $value);
            }

            return (float) ($value ?? 0);
        };

        $data['tunggakan_pokok'] = $cleanCurrency($data['tunggakan_pokok'] ?? 0);
        $data['tunggakan_bunga'] = $cleanCurrency($data['tunggakan_bunga'] ?? 0);
        $data['denda'] = $cleanCurrency($data['denda'] ?? 0);

        return $data;
    }

    public function uniqueBy(): string|array
    {
        return 'account_number';
    }

    public function model(array $row): ?Model
    {
        $rmNameInput = trim($row['rm_pengelola'] ?? $row['rm_manager'] ?? $row['nama_rm'] ?? '');
        $rmManagerId = $this->resolveRmManagerId($rmNameInput);

        if ($this->batchId) {
            $this->pendingBatchRows++;
        }

        return new BankAccount([
            'account_number' => $row['no_rekening'],
            'customer_name' => trim($row['nama_nasabah']),
            'current_noa' => (int) ($row['noa_saat_ini'] ?? 1),
            'segment' => trim($row['segmen']),
            'rm_manager_id' => $rmManagerId,
            'branch' => trim($row['cabang'] ?? ''),
            'kcu_branch_name' => trim($row['kc_cabang'] ?? ''),
            'bank_name' => strtolower(trim($row['bank'])),
            'hb_period' => trim($row['periode_hb'] ?? ''), // Diubah dari 'period' ke 'hb_period'
            'principal_arrears' => $row['tunggakan_pokok'] ?? 0,
            'interest_arrears' => $row['tunggakan_bunga'] ?? 0,
            'penalty_fee' => $row['denda'] ?? 0,
            'billing_address' => trim($row['alamat_tagih'] ?? ''),
            'billing_city' => trim($row['kota_alamat_tagih'] ?? ''),
            'fa_collateral' => strtolower(trim($row['agunan_fa'] ?? '')),
            'collateral_details' => trim($row['rincian_agunan'] ?? ''),
            'collateral_village' => trim($row['kelurahan_agunan'] ?? ''),
            'collateral_district' => trim($row['kecamatan_agunan'] ?? ''),
            'collateral_city' => trim($row['kota_agunan'] ?? ''),
            'collateral_province' => trim($row['provinsi_agunan'] ?? ''),
            'notes' => trim($row['keterangan'] ?? ''),
        ]);
    }

    private function resolveRmManagerId(string $name): ?int
    {
        return $name === '' ? null : $this->getRmIdByName($name);
    }

    public function rules(): array
    {
        return [
            'no_rekening' => ['required', 'string', 'regex:/^[0-9]+$/D', 'max:255'],
            'nama_nasabah' => ['required', 'string', 'max:255'],
            'noa_saat_ini' => ['nullable', 'integer', 'min:1'],
            'segmen' => ['required', 'string', 'max:100'],
            'rm_pengelola' => [
                'nullable',
                'string',
                'max:150',
                Rule::in(array_values(array_unique(array_filter($this->rmNameCache)))),
            ],
            'bank' => ['required', Rule::in(['konvensional', 'syariah'])],
            'periode_hb' => ['required', 'string', 'max:255'],
            'agunan_fa' => ['required', 'string', 'max:50'],
            'tunggakan_pokok' => ['nullable', 'numeric'],
            'tunggakan_bunga' => ['nullable', 'numeric'],
            'denda' => ['nullable', 'numeric'],
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            'no_rekening.required' => 'Nomor rekening wajib diisi.',
            'no_rekening.regex' => 'Nomor rekening hanya boleh berisi angka.',
            'nama_nasabah.required' => 'Nama nasabah tidak boleh kosong.',
            'segmen.required' => 'Segmen wajib diisi.',
            'rm_pengelola.in' => 'RM Pengelola belum terdaftar. Tambahkan RM tersebut sebelum mengimpor data.',
            'bank.required' => 'Bank wajib diisi.',
            'periode_hb.required' => 'Periode HB wajib diisi.',
            'agunan_fa.required' => 'Agunan FA wajib diisi.',
        ];
    }

    public function onFailure(Failure ...$failures): void
    {
        if (! $this->batchId) {
            return;
        }

        $failuresByRow = [];

        foreach ($failures as $failure) {
            $failuresByRow[$failure->row()][] = $failure->errors();
        }

        foreach ($failuresByRow as $row => $rowErrors) {
            Cache::increment("import_processed_{$this->batchId}");
            Cache::increment("import_failed_{$this->batchId}");
            $messages = array_merge(...$rowErrors);
            $this->appendImportError($row, implode(', ', array_unique($messages)));
        }
    }

    public function onError(\Throwable $e): void
    {
        if (! $this->batchId) {
            return;
        }

        $this->pendingBatchFailed = true;
        $this->appendImportError('Sistem', 'Error DB: '.$e->getMessage());
    }

    public function registerEvents(): array
    {
        return [
            BeforeImport::class => [$this, 'beforeImport'],
            AfterBatch::class => [$this, 'afterBatch'],
            AfterImport::class => [$this, 'afterImport'],
        ];
    }

    public function beforeImport(BeforeImport $event): void
    {
        if (! $this->batchId) {
            return;
        }

        $totalRowsData = $event->reader->getTotalRows();
        $totalRows = array_sum($totalRowsData);
        $headingRows = HeadingRowExtractor::headingRow($this) * count($totalRowsData);
        Cache::put("import_total_{$this->batchId}", max(0, $totalRows - $headingRows), now()->addHours(2));
    }

    public function afterBatch(AfterBatch $event): void
    {
        if ($this->batchId && $this->pendingBatchRows > 0) {
            Cache::increment("import_processed_{$this->batchId}", $this->pendingBatchRows);
            $counter = $this->pendingBatchFailed ? 'failed' : 'success';
            Cache::increment("import_{$counter}_{$this->batchId}", $this->pendingBatchRows);
        }

        $this->pendingBatchRows = 0;
        $this->pendingBatchFailed = false;
    }

    public function afterImport(AfterImport $event): void
    {
        if ($this->batchId) {
            Cache::put("import_finished_{$this->batchId}", true, now()->addHours(2));
        }
    }

    private function appendImportError(int|string $row, string $message): void
    {
        $errors = Cache::get("import_errors_{$this->batchId}", []);
        if (count($errors) >= 100) {
            return;
        }

        $errors[] = ['row' => $row, 'message' => $message];
        Cache::put("import_errors_{$this->batchId}", $errors, now()->addHours(2));
    }

    public function chunkSize(): int
    {
        return 250;
    }

    public function batchSize(): int
    {
        return 250;
    }
}
