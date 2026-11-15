<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $e($title ?? 'Document') ?> - Class Aid</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm 12mm 16mm 12mm;
        }
        @media print {
            html, body {
                width: 210mm !important;
                margin: 0 !important;
                padding: 0 !important;
                background: #ffffff !important;
                color: #0f172a !important;
                font-size: 10.5pt !important;
                line-height: 1.4 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .no-print {
                display: none !important;
            }
            .print-container {
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
                margin: 0 !important;
                max-width: 100% !important;
                width: 100% !important;
            }
            tr, .avoid-break {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
            .print-institutional-footer {
                margin-top: 18mm !important;
                page-break-inside: avoid !important;
            }
        }
        @media screen {
            body {
                background: #f1f5f9;
                font-size: 11pt;
            }
            .print-container {
                max-width: 210mm;
                min-height: 297mm;
                margin-left: auto;
                margin-right: auto;
                background: #ffffff;
            }
        }
        table {
            font-size: 10.5pt !important;
        }
        th, td {
            font-size: 10pt !important;
        }
    </style>
</head>
<body class="min-h-screen text-slate-900 font-sans p-4 sm:p-8">

    <!-- Action Bar (Hidden when printed) -->
    <div class="max-w-[210mm] mx-auto mb-6 flex items-center justify-between no-print">
        <a href="javascript:history.back()" class="inline-flex items-center gap-2 px-4 py-2 bg-white text-slate-700 hover:bg-slate-50 border border-slate-300 rounded-xl text-xs font-semibold shadow-sm transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Back to Application</span>
        </a>
        <div class="flex items-center gap-3">
            <span class="text-xs text-slate-500 font-medium hidden sm:inline">Format: A4 Portrait (10.5pt Institutional Typography)</span>
            <button onclick="window.print()" class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-bold shadow-md shadow-indigo-600/25 transition-all cursor-pointer">
                <i data-lucide="printer" class="w-4 h-4"></i>
                <span>Print / Save as PDF</span>
            </button>
        </div>
    </div>

    <!-- Printable A4 Portrait Sheet -->
    <div class="print-container p-8 sm:p-10 rounded-2xl shadow-xl border border-slate-200">
        <?= $content ?>

        <!-- Standardized Institutional App Footer -->
        <div class="print-institutional-footer mt-10 pt-4 border-t-2 border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-2 text-slate-500 text-[10pt]">
            <div class="flex items-center gap-2">
                <div class="w-5 h-5 rounded-md bg-indigo-600 text-white flex items-center justify-center font-black text-[10px]">CA</div>
                <span class="font-bold text-slate-800">Class Aid</span>
                <span>—</span>
                <span class="text-slate-600">Institutional Record-Keeping &amp; Classroom Intelligence Suite</span>
            </div>
            <div class="text-[9.5pt] text-slate-500 flex items-center gap-2">
                <span class="font-medium">Verified Institutional Record</span>
                <span>•</span>
                <span class="font-mono tabular-nums">Printed: <?= date('Y-m-d H:i') ?></span>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
