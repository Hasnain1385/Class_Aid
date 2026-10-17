<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $e($title ?? 'Document') ?> - Class Aid</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: white !important;
                color: black !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .print-container {
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
                max-width: 100% !important;
            }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen text-gray-900 font-sans p-4 sm:p-8">

    <!-- Action Bar (Hidden when printed) -->
    <div class="max-w-4xl mx-auto mb-6 flex items-center justify-between no-print">
        <a href="javascript:history.back()" class="inline-flex items-center gap-2 px-4 py-2 bg-white text-gray-700 hover:bg-gray-50 border border-gray-300 rounded-lg text-sm font-medium shadow-sm transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Back</span>
        </a>
        <button onclick="window.print()" class="inline-flex items-center gap-2 px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-semibold shadow-md shadow-indigo-600/30 transition-all">
            <i data-lucide="printer" class="w-4 h-4"></i>
            <span>Print / Save as PDF</span>
        </button>
    </div>

    <!-- Printable Sheet -->
    <div class="max-w-4xl mx-auto bg-white p-8 sm:p-12 rounded-2xl shadow-xl border border-gray-200 print-container">
        <?= $content ?>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
