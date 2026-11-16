<?php

namespace Tests\Unit;

use App\Imports\BankAccountImport;
use App\Models\BankAccount;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Events\AfterBatch;
use Maatwebsite\Excel\Imports\ModelManager;
use Maatwebsite\Excel\Validators\Failure;
use RuntimeException;
use Tests\TestCase;

class BankAccountImportTest extends TestCase
{
    public function test_rm_manager_is_resolved_from_database_and_unknown_names_fail_validation(): void
    {
        if (! Schema::hasTable('rm_managers')) {
            Schema::create('rm_managers', function (Blueprint $table): void {
                $table->id();
                $table->string('name', 150);
                $table->string('phone_number', 20)->nullable();
                $table->timestamps();
            });
        }

        DB::table('rm_managers')->delete();
        $rmId = DB::table('rm_managers')->insertGetId([
            'name' => 'Nama RM Baru',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $import = new BankAccountImport;
        $row = $import->prepareForValidation([
            'no_rekening' => '001234',
            'nama_nasabah' => 'Nasabah',
            'segmen' => 'MIKRO',
            'rm_manager' => ' nama rm baru ',
            'bank' => 'syariah',
            'periode_hb' => '2026-11',
            'agunan_fa' => 'beragunan',
        ], 2);

        $this->assertSame('Nama RM Baru', $row['rm_pengelola']);
        $this->assertFalse(Validator::make($row, $import->rules())->fails());
        $this->assertSame($rmId, $import->model($row)->rm_manager_id);

        $row['rm_pengelola'] = 'RM yang belum terdaftar';
        $validator = Validator::make($row, $import->rules(), $import->customValidationMessages());

        $this->assertTrue($validator->fails());
        $this->assertSame(
            'RM Pengelola belum terdaftar. Tambahkan RM tersebut sebelum mengimpor data.',
            $validator->errors()->first('rm_pengelola')
        );
    }

    public function test_account_number_is_preserved_and_bank_name_is_normalized(): void
    {
        $import = new BankAccountImport;
        $row = $import->prepareForValidation([
            'no_rekening' => ' 001234 ',
            'bank' => ' SyArIaH ',
        ], 2);

        $this->assertSame('001234', $row['no_rekening']);
        $this->assertSame('syariah', $row['bank']);

        $validRow = $import->prepareForValidation([
            'no_rekening' => '001234',
            'nama_nasabah' => 'Nasabah',
            'segmen' => 'MIKRO',
            'bank' => ' KoNvEnSiOnAl ',
            'periode_hb' => '2026-11',
            'agunan_fa' => 'beragunan',
        ], 2);

        $this->assertFalse(Validator::make($validRow, $import->rules())->fails());

        $invalidRow = $import->prepareForValidation([
            'no_rekening' => '12A34',
            'nama_nasabah' => 'Nasabah',
            'segmen' => 'MIKRO',
            'bank' => 'syariah',
            'periode_hb' => '2026-11',
            'agunan_fa' => 'beragunan',
        ], 3);

        $validator = Validator::make($invalidRow, $import->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('no_rekening', $validator->errors()->toArray());
    }

    public function test_import_maps_only_bank_account_model_fields(): void
    {
        $import = new BankAccountImport;
        $model = $import->model([
            'no_rekening' => '001234',
            'nama_nasabah' => 'Nasabah',
            'noa_saat_ini' => 2,
            'segmen' => 'MIKRO',
            'bank' => 'SYARIAH',
            'periode_hb' => '2026-11',
            'agunan_fa' => 'BERAGUNAN',
        ]);

        $this->assertSame('001234', $model->account_number);
        $this->assertSame('syariah', $model->bank_name);
        $this->assertEqualsCanonicalizing((new BankAccount)->getFillable(), array_keys($model->getAttributes()));
    }

    public function test_progress_counts_each_invalid_row_once_and_database_failed_batch_as_failed(): void
    {
        $batchId = 'import-test';
        Cache::put("import_processed_{$batchId}", 0);
        Cache::put("import_success_{$batchId}", 0);
        Cache::put("import_failed_{$batchId}", 0);
        Cache::put("import_errors_{$batchId}", []);

        $import = new BankAccountImport($batchId);
        $import->onFailure(
            new Failure(2, 'bank', ['Bank tidak valid.']),
            new Failure(2, 'periode_hb', ['Periode HB wajib diisi.']),
        );

        $this->assertSame(1, Cache::get("import_processed_{$batchId}"));
        $this->assertSame(1, Cache::get("import_failed_{$batchId}"));
        $this->assertCount(1, Cache::get("import_errors_{$batchId}"));

        $successBatchId = 'import-success-test';
        Cache::put("import_processed_{$successBatchId}", 0);
        Cache::put("import_success_{$successBatchId}", 0);
        Cache::put("import_failed_{$successBatchId}", 0);
        $successImport = new BankAccountImport($successBatchId);
        $successImport->model([
            'no_rekening' => '001235',
            'nama_nasabah' => 'Nasabah',
            'segmen' => 'MIKRO',
            'bank' => 'syariah',
            'periode_hb' => '2026-11',
            'agunan_fa' => 'beragunan',
        ]);
        $successImport->afterBatch(new AfterBatch($this->createMock(ModelManager::class), $successImport, 250, 2));

        $this->assertSame(1, Cache::get("import_processed_{$successBatchId}"));
        $this->assertSame(1, Cache::get("import_success_{$successBatchId}"));
        $this->assertSame(0, Cache::get("import_failed_{$successBatchId}"));

        $import->model([
            'no_rekening' => '001234',
            'nama_nasabah' => 'Nasabah',
            'segmen' => 'MIKRO',
            'bank' => 'syariah',
            'periode_hb' => '2026-11',
            'agunan_fa' => 'beragunan',
        ]);
        $import->onError(new RuntimeException('Database write failed.'));
        $import->afterBatch(new AfterBatch($this->createMock(ModelManager::class), $import, 250, 2));

        $this->assertSame(2, Cache::get("import_processed_{$batchId}"));
        $this->assertSame(0, Cache::get("import_success_{$batchId}"));
        $this->assertSame(2, Cache::get("import_failed_{$batchId}"));
    }
}
