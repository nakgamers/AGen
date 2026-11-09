<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Upload Data Bank Account / Rekening Nasabah') }}
        </h2>
    </x-slot>

    <style>
        .up-wrap { max-width: 52rem; margin: 0 auto; }
        .up-title { font-size: 1rem; font-weight: 600; color: #1f2937; margin: 0 0 .75rem; }
        .up-note { background: #eef2ff; border: 1px solid #c7d2fe; border-radius: .5rem; padding: 1rem 1.25rem; margin-bottom: 1.25rem; color: #3730a3; font-size: .875rem; }
        .up-note ul { margin: .5rem 0 0; padding-left: 1.25rem; list-style: disc; }
        .up-note li { margin-bottom: .25rem; }
        .up-actions { display: flex; flex-wrap: wrap; gap: .5rem; margin-top: .75rem; }
        .up-btn { display: inline-flex; align-items: center; justify-content: center; gap: .5rem; padding: .5rem 1rem; border-radius: .375rem; border: none; font-size: .875rem; font-weight: 500; cursor: pointer; text-decoration: none; }
        .up-btn-primary { background: #4f46e5; color: #fff; }
        .up-btn-primary:hover { background: #4338ca; }
        .up-btn-dark { background: #1f2937; color: #fff; }
        .up-btn-light { background: #fff; color: #3730a3; border: 1px solid #c7d2fe; }
        .up-btn-gray { background: #e5e7eb; color: #374151; }
        .up-btn:disabled { opacity: .6; cursor: not-allowed; }
        .up-details summary { cursor: pointer; font-weight: 500; margin-top: .75rem; }
        .up-chips { display: flex; flex-wrap: wrap; gap: .375rem; margin-top: .5rem; }
        .up-chip { background: #fff; border: 1px solid #c7d2fe; border-radius: 9999px; padding: .125rem .625rem; font-size: .75rem; color: #374151; }

        .up-drop { display: block; position: relative; border: 2px dashed #d1d5db; border-radius: .5rem; padding: 2rem 1rem; text-align: center; cursor: pointer; background: #f9fafb; transition: border-color .15s, background .15s; }
        .up-drop:hover, .up-drop.is-over { border-color: #4f46e5; background: #eef2ff; }
        .up-drop input[type=file] { position: absolute; width: 1px; height: 1px; opacity: 0; }
        .up-drop-text { margin: .5rem 0 0; font-size: .9rem; color: #374151; font-weight: 500; }
        .up-drop-hint { margin: .25rem 0 0; font-size: .75rem; color: #6b7280; }
        .up-fileinfo { display: none; align-items: center; justify-content: space-between; gap: 1rem; margin-top: .75rem; padding: .625rem .875rem; border: 1px solid #a7f3d0; background: #ecfdf5; border-radius: .375rem; font-size: .875rem; color: #065f46; }
        .up-fileinfo.is-shown { display: flex; }
        .up-fileinfo button { background: none; border: none; color: #dc2626; cursor: pointer; font-weight: 500; }
        .up-error { display: none; color: #dc2626; font-size: .8125rem; margin-top: .5rem; }
        .up-submit-row { display: flex; justify-content: flex-end; margin-top: 1rem; }

        .up-steps { display: flex; gap: .5rem; margin-bottom: 1rem; flex-wrap: wrap; }
        .up-step { flex: 1; min-width: 7rem; text-align: center; padding: .375rem .5rem; border-radius: .375rem; background: #f3f4f6; color: #9ca3af; font-size: .8125rem; font-weight: 500; }
        .up-step.is-active { background: #e0e7ff; color: #3730a3; }
        .up-step.is-done { background: #d1fae5; color: #065f46; }

        .up-bar { width: 100%; height: .875rem; background: #e5e7eb; border-radius: 9999px; overflow: hidden; }
        .up-bar-fill { height: 100%; width: 0%; background: #4f46e5; border-radius: 9999px; transition: width .5s; }
        .up-bar-fill.is-done { background: #10b981; }
        .up-progress-text { margin: .5rem 0 1.25rem; font-size: .875rem; color: #4b5563; font-weight: 500; }
        .up-progress-text.is-done { color: #059669; }

        .up-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(11rem, 1fr)); gap: .75rem; margin-bottom: 1.25rem; }
        .up-stat { border-radius: .5rem; padding: .875rem; text-align: center; border: 1px solid; }
        .up-stat-label { font-size: .6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: .03em; margin: 0; }
        .up-stat-value { font-size: 1.5rem; font-weight: 700; margin: .25rem 0 0; }
        .up-stat-total { background: #eff6ff; border-color: #bfdbfe; color: #1e40af; }
        .up-stat-ok { background: #ecfdf5; border-color: #a7f3d0; color: #065f46; }
        .up-stat-fail { background: #fef2f2; border-color: #fecaca; color: #991b1b; }

        .up-errbox { display: none; }
        .up-errbox.is-shown { display: block; }
        .up-errlist { background: #fef2f2; border: 1px solid #fecaca; border-radius: .375rem; padding: .625rem .875rem; max-height: 14rem; overflow-y: auto; font-size: .75rem; color: #991b1b; }
        .up-errlist ul { margin: 0; padding-left: 1.125rem; list-style: disc; }
        .up-errhead { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: .5rem; margin-bottom: .5rem; font-size: .875rem; font-weight: 600; color: #dc2626; }
        .up-footer { margin-top: 1.25rem; display: flex; justify-content: flex-end; }
        .up-hidden { display: none; }
        @keyframes up-spin { to { transform: rotate(360deg); } }
        .up-spin { animation: up-spin 1s linear infinite; }
    </style>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg" style="padding:1.5rem;">
                <div class="up-wrap">

                    <!-- Petunjuk ringkas -->
                    <div class="up-note">
                        <strong>Sebelum upload</strong>
                        <ul>
                            <li>Baris pertama file harus berisi header dengan nama kolom persis sesuai daftar di bawah.</li>
                            <li><strong>NO REKENING</strong> adalah kunci unik: nomor yang sudah ada akan <strong>diperbarui</strong>, yang belum ada akan <strong>ditambahkan</strong>.</li>
                            <li>Mendukung 50.000+ baris, diproses di latar belakang.</li>
                        </ul>

                        <div class="up-actions">
                            <button type="button" id="btn_copy_header" class="up-btn up-btn-light">Salin Header untuk Excel</button>
                            <span id="copy_status" class="up-hidden" style="align-self:center; font-size:.8125rem; color:#065f46;">Header tersalin. Tempel (Ctrl+V) di sel A1 Excel.</span>
                        </div>

                        <details class="up-details">
                            <summary>Lihat daftar kolom (20 kolom)</summary>
                            <div class="up-chips" id="column_chips"></div>
                        </details>
                    </div>

                    <!-- Form upload -->
                    <div id="upload_section">
                        <h3 class="up-title">Pilih File</h3>
                        <form id="importForm" enctype="multipart/form-data">
                            @csrf
                            <label for="file" id="dropzone" class="up-drop">
                                <input type="file" name="file" id="file" accept=".xlsx,.xls,.csv" required>
                                <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" fill="none" viewBox="0 0 24 24" stroke="#6b7280" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                                </svg>
                                <p class="up-drop-text">Klik untuk memilih file, atau seret file ke sini</p>
                                <p class="up-drop-hint">Format: .xlsx, .xls, .csv</p>
                            </label>

                            <div id="file_info" class="up-fileinfo">
                                <span id="file_name"></span>
                                <button type="button" id="btn_remove_file">Hapus</button>
                            </div>
                            <span id="file_error" class="up-error"></span>

                            <div class="up-submit-row">
                                <button type="submit" id="btn_submit" class="up-btn up-btn-primary">
                                    <svg id="spinner" class="up-spin up-hidden" width="16" height="16" fill="none" viewBox="0 0 24 24">
                                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" style="opacity:.25"></circle>
                                        <path fill="currentColor" style="opacity:.75" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                    </svg>
                                    <span id="btn_label">Mulai Upload &amp; Proses</span>
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Progress -->
                    <div id="progress_section" class="up-hidden">
                        <div class="up-steps">
                            <div class="up-step is-done" id="step_1">1. Unggah</div>
                            <div class="up-step is-active" id="step_2">2. Proses</div>
                            <div class="up-step" id="step_3">3. Selesai</div>
                        </div>

                        <div class="up-bar"><div id="progress_bar" class="up-bar-fill"></div></div>
                        <p id="progress_text" class="up-progress-text">Menginisiasi proses... 0%</p>

                        <div class="up-stats">
                            <div class="up-stat up-stat-total">
                                <p class="up-stat-label">Total Baris</p>
                                <p id="stat_total" class="up-stat-value">0</p>
                            </div>
                            <div class="up-stat up-stat-ok">
                                <p class="up-stat-label">Berhasil (Insert/Update)</p>
                                <p id="stat_success" class="up-stat-value">0</p>
                            </div>
                            <div class="up-stat up-stat-fail">
                                <p class="up-stat-label">Dilewati / Error</p>
                                <p id="stat_skipped" class="up-stat-value">0</p>
                            </div>
                        </div>

                        <div id="error_log_container" class="up-errbox">
                            <div class="up-errhead">
                                <span id="error_title">Data yang Dilewati</span>
                                <button type="button" id="btn_download_errors" class="up-btn up-btn-light" style="color:#991b1b; border-color:#fecaca;">Unduh Daftar Error (CSV)</button>
                            </div>
                            <div class="up-errlist"><ul id="error_list"></ul></div>
                        </div>

                        <div class="up-footer">
                            <button type="button" id="btn_reset" class="up-btn up-btn-dark up-hidden">Upload File Baru</button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function () {
            var pollInterval = null;
            var pollFailCount = 0;
            var allErrors = [];
            var MAX_SHOWN_ERRORS = 100;

            var COLUMNS = [
                'NO REKENING', 'NAMA NASABAH', 'NOA SAAT INI', 'SEGMEN', 'RM PENGELOLA',
                'CABANG', 'KC CABANG', 'BANK', 'TUNGGAKAN POKOK', 'TUNGGAKAN BUNGA',
                'DENDA', 'ALAMAT TAGIH', 'KOTA ALAMAT TAGIH', 'AGUNAN FA', 'RINCIAN AGUNAN',
                'KELURAHAN AGUNAN', 'KECAMATAN AGUNAN', 'KOTA AGUNAN', 'PROVINSI AGUNAN', 'KETERANGAN'
            ];

            // Daftar kolom (chip)
            COLUMNS.forEach(function (c, i) {
                $('#column_chips').append($('<span class="up-chip">').text((i + 1) + '. ' + c));
            });

            // Salin header (dipisah tab, jadi menempel per kolom di Excel apa pun pengaturan regional-nya)
            $('#btn_copy_header').on('click', function () {
                var text = COLUMNS.join('\t');
                function done() {
                    $('#copy_status').removeClass('up-hidden');
                    setTimeout(function () { $('#copy_status').addClass('up-hidden'); }, 4000);
                }
                if (navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard.writeText(text).then(done);
                } else {
                    var ta = $('<textarea>').val(text).css({ position: 'fixed', opacity: 0 }).appendTo('body');
                    ta[0].select();
                    document.execCommand('copy');
                    ta.remove();
                    done();
                }
            });

            // ---- Pemilihan file & drag-drop ----
            function formatSize(bytes) {
                if (bytes < 1024) return bytes + ' B';
                if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
                return (bytes / 1048576).toFixed(1) + ' MB';
            }

            function showFileError(msg) {
                $('#file_error').text(msg).css('display', msg ? 'block' : 'none');
            }

            function refreshFileInfo() {
                var f = $('#file')[0].files[0];
                if (f) {
                    $('#file_name').text(f.name + ' (' + formatSize(f.size) + ')');
                    $('#file_info').addClass('is-shown');
                } else {
                    $('#file_info').removeClass('is-shown');
                }
            }

            function validateFile() {
                var f = $('#file')[0].files[0];
                if (!f) return true;
                var ext = f.name.split('.').pop().toLowerCase();
                if (['xlsx', 'xls', 'csv'].indexOf(ext) === -1) {
                    showFileError('Format file tidak didukung. Gunakan .xlsx, .xls, atau .csv.');
                    $('#file').val('');
                    refreshFileInfo();
                    return false;
                }
                showFileError('');
                return true;
            }

            $('#file').on('change', function () {
                validateFile();
                refreshFileInfo();
            });

            $('#btn_remove_file').on('click', function () {
                $('#file').val('');
                refreshFileInfo();
                showFileError('');
            });

            $('#dropzone').on('dragover dragenter', function (e) {
                e.preventDefault();
                $(this).addClass('is-over');
            }).on('dragleave drop', function (e) {
                e.preventDefault();
                $(this).removeClass('is-over');
            }).on('drop', function (e) {
                var files = e.originalEvent.dataTransfer.files;
                if (files && files.length) {
                    $('#file')[0].files = files;
                    validateFile();
                    refreshFileInfo();
                }
            });

            // ---- Step indicator ----
            function setStep(n) {
                $('#step_1').toggleClass('is-done', n >= 2).toggleClass('is-active', n === 1);
                $('#step_2').toggleClass('is-done', n >= 3).toggleClass('is-active', n === 2);
                $('#step_3').toggleClass('is-done', n >= 3).toggleClass('is-active', false);
            }

            // ---- Submit ----
            $('#importForm').on('submit', function (e) {
                e.preventDefault();
                if (!validateFile()) return;

                var formData = new FormData(this);
                var $btn = $('#btn_submit');

                showFileError('');
                $btn.prop('disabled', true);
                $('#spinner').removeClass('up-hidden');
                $('#btn_label').text('Mengunggah... 0%');

                $.ajax({
                    url: "{{ route('bank-accounts.import-ajax') }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    xhr: function () {
                        var xhr = $.ajaxSettings.xhr();
                        if (xhr.upload) {
                            xhr.upload.addEventListener('progress', function (ev) {
                                if (ev.lengthComputable) {
                                    $('#btn_label').text('Mengunggah... ' + Math.round(ev.loaded / ev.total * 100) + '%');
                                }
                            });
                        }
                        return xhr;
                    },
                    success: function (response) {
                        $('#upload_section').slideUp();
                        $('#progress_section').removeClass('up-hidden').hide().slideDown();
                        setStep(2);
                        startPolling(response.batch_id);
                    },
                    error: function (xhr) {
                        $btn.prop('disabled', false);
                        $('#spinner').addClass('up-hidden');
                        $('#btn_label').text('Mulai Upload & Proses');

                        var msg = 'Terjadi kesalahan sistem.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        showFileError(msg);
                    }
                });
            });

            // ---- Polling ----
            function startPolling(batchId) {
                pollFailCount = 0;
                pollInterval = setInterval(function () {
                    $.ajax({
                        url: "/api/import-status/" + batchId,
                        type: "GET",
                        success: function (res) {
                            pollFailCount = 0;
                            var percent = res.progress;

                            $('#progress_bar').css('width', percent + '%');
                            $('#progress_text').text('Memproses... ' + percent + '% (' + res.processed + ' dari ' + res.total + ' data)');
                            $('#stat_total').text(res.total);
                            $('#stat_success').text(res.success);
                            $('#stat_skipped').text(res.failed);

                            if (res.is_finished) {
                                clearInterval(pollInterval);
                                setStep(3);
                                $('#progress_bar').addClass('is-done').css('width', '100%');
                                $('#progress_text').addClass('is-done').text(
                                    res.failed > 0
                                        ? 'Selesai. ' + res.failed + ' data dilewati, lihat daftar di bawah.'
                                        : 'Pemrosesan selesai!'
                                );
                                $('#btn_reset').removeClass('up-hidden');

                                if (res.errors && res.errors.length > 0) {
                                    allErrors = res.errors;
                                    var shown = allErrors.slice(0, MAX_SHOWN_ERRORS);
                                    var $list = $('#error_list').empty();
                                    shown.forEach(function (err) {
                                        $list.append($('<li>').text('Baris ' + err.row + ': ' + err.message));
                                    });
                                    $('#error_title').text(
                                        allErrors.length > MAX_SHOWN_ERRORS
                                            ? 'Data yang Dilewati (menampilkan ' + MAX_SHOWN_ERRORS + ' dari ' + allErrors.length + ')'
                                            : 'Data yang Dilewati (' + allErrors.length + ')'
                                    );
                                    $('#error_log_container').addClass('is-shown');
                                }
                            }
                        },
                        error: function () {
                            pollFailCount++;
                            if (pollFailCount >= 5) {
                                clearInterval(pollInterval);
                                $('#progress_text').text('Gagal mengambil status proses. Muat ulang halaman lalu periksa hasil import.');
                                $('#btn_reset').removeClass('up-hidden');
                            }
                        }
                    });
                }, 2000);
            }

            // ---- Unduh daftar error (CSV) ----
            $('#btn_download_errors').on('click', function () {
                if (!allErrors.length) return;
                var lines = ['Baris,Pesan'];
                allErrors.forEach(function (err) {
                    var msg = String(err.message).replace(/"/g, '""');
                    lines.push(err.row + ',"' + msg + '"');
                });
                var blob = new Blob(['\ufeff' + lines.join('\r\n')], { type: 'text/csv;charset=utf-8;' });
                var a = document.createElement('a');
                a.href = URL.createObjectURL(blob);
                a.download = 'daftar-error-import.csv';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(a.href);
            });

            // ---- Reset ----
            $('#btn_reset').on('click', function () {
                if (pollInterval) clearInterval(pollInterval);
                $('#progress_section').slideUp(function () { $(this).addClass('up-hidden'); });
                $('#upload_section').slideDown();
                $('#importForm')[0].reset();
                refreshFileInfo();
                showFileError('');

                $('#btn_submit').prop('disabled', false);
                $('#spinner').addClass('up-hidden');
                $('#btn_label').text('Mulai Upload & Proses');

                $('#progress_bar').css('width', '0%').removeClass('is-done');
                $('#progress_text').text('Menginisiasi proses... 0%').removeClass('is-done');
                $('#stat_total, #stat_success, #stat_skipped').text('0');
                $('#error_log_container').removeClass('is-shown');
                $('#error_list').empty();
                $('#btn_reset').addClass('up-hidden');
                allErrors = [];
                setStep(2);
            });
        });
    </script>
</x-app-layout>