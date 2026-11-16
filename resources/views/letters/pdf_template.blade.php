<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat - {{ $letter->letter_number }}</title>
    <style>
        /* Gunakan A4 dengan margin yang konsisten pada preview dan PDF. */
        @page {
            size: A4;
            margin: 15mm 20mm;
        }

        /* Perkecil font dan line height agar isi surat tetap ringkas. */
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9.5pt; /* Lebih kecil dari sebelumnya (10pt) */
            line-height: 1.3; /* Jarak antar baris dirapatkan */
            color: #000;
            margin: 0;
            padding: 0;
            background-color: #f9fafb;
        }

        /* Kanvas preview mengikuti dimensi fisik kertas A4. */
        .letter-body {
            background-color: #ffffff;
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            padding: 15mm 20mm;
            box-sizing: border-box;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        /* Styling Bilah Tombol Preview (Tetap Sama) */
        .no-print-bar {
            background-color: #1f2937;
            padding: 12px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            font-family: sans-serif;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .no-print-bar .title { color: #ffffff; font-size: 14px; font-weight: 600; }
        .btn-group { display: flex; gap: 10px; }
        .btn {
            display: inline-block; padding: 8px 16px; font-size: 13px;
            font-weight: 600; text-decoration: none; border-radius: 6px;
            cursor: pointer; border: none;
        }
        .btn-download { background-color: #2563eb; color: #ffffff; }
        .btn-print { background-color: #059669; color: #ffffff; }
        .btn-back { background-color: #4b5563; color: #ffffff; }

        /* Mencegah tabel dan list terpotong di tengah halaman */
        table, tr, td, th, tbody, thead, tfoot {
            page-break-inside: avoid !important;
        }

        @if(isset($isPdf))
        /* Margin halaman sudah diatur lewat @page, jadi wrapper PDF tidak perlu padding. */

        body { background-color: #ffffff; }
        .letter-body {
            width: auto;
            min-height: 0;
            margin: 0;
            padding: 0;
            box-shadow: none;
        }
        @endif

        @media print {
            body { background-color: #ffffff; }
            .letter-body {
                width: 100%;
                min-height: 0;
                padding: 0;
                box-shadow: none;
            }
            .no-print-bar {
                display: none !important;
                visibility: hidden !important;
                height: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            @page {
                size: A4;
                margin: 15mm 20mm;
            }
        }
    </style>
</head>
<body>

    @if(!isset($isPdf))
    <div class="no-print-bar">
        <span class="title">Preview Surat: {{ $letter->letter_number }}</span>
        <div class="btn-group">
            <a href="{{ route('letters.create') }}" class="btn btn-back">← Kembali</a>
            <button type="button" onclick="window.print()" class="btn btn-print">Cetak</button>
            <a href="{{ route('letters.export-pdf', $letter->id) }}" class="btn btn-download">Download PDF</a>
        </div>
    </div>
    @endif

    <div class="letter-body content">
        {!! $content !!}
    </div>

    @if(!isset($isPdf))
    <script>
        let originalLetterZoom = '';

        window.addEventListener('beforeprint', () => {
            const letter = document.querySelector('.letter-body');
            if (!letter) return;

            originalLetterZoom = letter.style.zoom;
            letter.style.zoom = '1';
            const printableHeight = (297 - 30) * 96 / 25.4;
            const scale = Math.min(1, printableHeight / letter.scrollHeight);
            letter.style.zoom = String(scale);
        });

        window.addEventListener('afterprint', () => {
            const letter = document.querySelector('.letter-body');
            if (letter) letter.style.zoom = originalLetterZoom;
        });
    </script>
    @endif

</body>
</html>