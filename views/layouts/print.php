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
            margin: 8mm 8mm 10mm 8mm;
        }
        @media print {
            html, body {
                width: 210mm !important;
                height: 100% !important;
                min-height: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                background: #ffffff !important;
                color: #0f172a !important;
                font-size: 10pt !important;
                line-height: 1.25 !important;
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
                min-height: 275mm !important; /* Full A4 page height (297mm) minus margins */
                display: flex !important;
                flex-direction: column !important;
                justify-content: space-between !important;
                box-sizing: border-box !important;
            }
            .print-body-content {
                flex: 1 0 auto !important;
                width: 100% !important;
            }
            .print-institutional-footer {
                margin-top: auto !important;
                flex-shrink: 0 !important;
                width: 100% !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
                padding-top: 3mm !important;
                border-top: 1px solid #cbd5e1 !important;
            }
            tr, .avoid-break {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
            table.compact-table th, table.compact-table td {
                padding: 1.2mm 1.8mm !important;
                line-height: 1.15 !important;
                font-size: 8.5pt !important;
            }
            table.compact-table {
                font-size: 8.5pt !important;
            }
        }
        @media screen {
            body {
                background: #f1f5f9;
                font-size: 11pt;
                min-height: 100vh;
            }
            .print-container {
                max-width: 210mm;
                min-height: 297mm;
                margin-left: auto;
                margin-right: auto;
                background: #ffffff;
                display: flex !important;
                flex-direction: column !important;
                justify-content: space-between !important;
                box-sizing: border-box !important;
                padding: 10mm !important;
            }
            .print-body-content {
                flex: 1 0 auto !important;
                width: 100% !important;
            }
            .print-institutional-footer {
                margin-top: auto !important;
                flex-shrink: 0 !important;
                width: 100% !important;
                padding-top: 0.75rem !important;
                border-top: 1px solid #cbd5e1 !important;
            }
            table.compact-table th, table.compact-table td {
                padding: 0.28rem 0.45rem !important;
                line-height: 1.15 !important;
                font-size: 0.75rem !important;
            }
        }
        table {
            font-size: 10pt !important;
        }
        th, td {
            font-size: 9.5pt !important;
        }
    </style>
</head>
<body class="min-h-screen text-slate-900 font-sans p-4 sm:p-8">

    <!-- Action Bar (Hidden when printed) -->
    <div class="max-w-[210mm] mx-auto mb-6 flex items-center justify-between no-print">
        <a id="backToAppBtn" href="<?= $e($backUrl ?? '/attendance/reports') ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-white text-slate-700 hover:bg-slate-50 border border-slate-300 rounded-xl text-xs font-semibold shadow-sm transition-colors cursor-pointer">
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
        <div class="print-body-content">
            <?= $content ?>
        </div>

        <!-- Simplified Print Footer (Just Class Aid & Print Date, pinned to bottom) -->
        <div class="print-institutional-footer flex items-center justify-between text-slate-600 text-[9.5pt]">
            <span class="font-bold text-slate-900 tracking-tight">Class Aid</span>
            <span class="font-mono text-[9pt] tabular-nums text-slate-500">Printed: <?= date('Y-m-d H:i') ?></span>
        </div>
    </div>

    <script>
        lucide.createIcons();

        document.getElementById('backToAppBtn')?.addEventListener('click', function(e) {
            // If opened as a popup or separate window with opener, attempt to close
            if (window.opener && !window.opener.closed) {
                try {
                    window.close();
                } catch (err) {}
            }
            // If window is still open (browser blocked close, or opened in regular tab)
            // check if we can go back within application history
            if (window.history.length > 1 && document.referrer && document.referrer.indexOf(window.location.host) !== -1) {
                e.preventDefault();
                window.history.back();
            }
            // If history is empty or external, allow standard href navigation to backUrl
        });
    </script>
</body>
</html>
