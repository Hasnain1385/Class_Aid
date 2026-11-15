<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice <?= $e($invoice['invoice_number']) ?> - Class Aid</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        slate: {
                            850: '#151F32',
                            900: '#0F172A',
                            950: '#090D16',
                        }
                    }
                }
            }
        }
    </script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="/assets/css/glassmorphism.css?v=1.1.0">
    <style>
        @media print {
            body {
                background: white !important;
                color: black !important;
                padding: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .print-card {
                border: 1px solid #cbd5e1 !important;
                box-shadow: none !important;
                background: white !important;
                color: black !important;
                max-width: 100% !important;
            }
            .print-text-dark {
                color: #0f172a !important;
            }
            .print-border {
                border-color: #e2e8f0 !important;
            }
            .print-bg-gray {
                background-color: #f8fafc !important;
            }
        }
    </style>
</head>
<body class="min-h-screen py-10 px-4 sm:px-6 antialiased bg-slate-950 text-slate-100 flex flex-col items-center justify-center">

    <!-- Action Bar (Hidden in Print) -->
    <div class="max-w-3xl w-full mx-auto mb-6 flex items-center justify-between gap-4 no-print">
        <a href="/subscription" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 shadow-sm transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Back to Subscriptions</span>
        </a>

        <div class="flex items-center gap-2">
            <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white shadow-sm transition-all cursor-pointer">
                <i data-lucide="printer" class="w-4 h-4"></i>
                <span>Print / Save as PDF</span>
            </button>
        </div>
    </div>

    <!-- Printable Invoice Sheet -->
    <div class="max-w-3xl w-full mx-auto p-8 sm:p-12 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-xl print-card print-text-dark relative overflow-hidden bg-white dark:bg-slate-900">
        
        <!-- Status Watermark / Stamp -->
        <?php 
            $isPaid = ($invoice['status'] === 'paid');
            $statusLabel = $isPaid ? 'PAID' : 'PENDING VERIFICATION';
        ?>
        <div class="absolute top-6 right-8 rotate-12 select-none opacity-80 pointer-events-none">
            <div class="px-5 py-2 rounded-2xl border-4 font-black tracking-widest text-lg sm:text-2xl font-mono uppercase text-center <?= $isPaid ? 'border-emerald-500 text-emerald-500 bg-emerald-500/10' : 'border-amber-500 text-amber-500 bg-amber-500/10' ?>">
                <?= $statusLabel ?>
            </div>
        </div>

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-6 pb-8 border-b border-slate-200 dark:border-slate-800 print-border">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center shadow-md flex-shrink-0">
                    <i data-lucide="graduation-cap" class="w-6 h-6"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white print-text-dark">Class Aid</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400 print-text-dark mt-0.5">Educational Record-Keeping Suite &bull; SaaS Platform</p>
                </div>
            </div>

            <div class="text-left sm:text-right mt-2 sm:mt-0">
                <div class="text-xs uppercase tracking-wider font-bold text-indigo-600 dark:text-indigo-400 font-mono">Official Billing Receipt</div>
                <div class="text-xl font-bold font-mono text-slate-900 dark:text-white print-text-dark mt-0.5 tabular-nums"><?= $e($invoice['invoice_number']) ?></div>
                <div class="text-xs text-slate-500 dark:text-slate-400 print-text-dark mt-1">Issue Date: <strong class="text-slate-800 dark:text-slate-200 print-text-dark"><?= date('F d, Y', strtotime($invoice['issue_date'])) ?></strong></div>
                <?php if (!empty($invoice['paid_at'])): ?>
                    <div class="text-xs text-emerald-600 dark:text-emerald-400 font-semibold mt-0.5">Paid Date: <strong><?= date('F d, Y - H:i', strtotime($invoice['paid_at'])) ?></strong></div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Billing Entity & Customer Details -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-8 py-8 border-b border-slate-200 dark:border-slate-800 print-border text-xs">
            <div>
                <span class="uppercase font-bold text-xs tracking-wider text-slate-400 print-text-dark block mb-1">Billed To (Educator)</span>
                <div class="font-bold text-sm text-slate-900 dark:text-white print-text-dark"><?= $e($invoice['teacher_name']) ?></div>
                <div class="text-slate-600 dark:text-slate-300 print-text-dark mt-0.5"><?= $e($invoice['teacher_email']) ?></div>
                <div class="text-slate-500 dark:text-slate-400 print-text-dark"><?= $e($invoice['school_name'] ?? 'Class Aid Classroom') ?></div>
                <?php if (!empty($invoice['teacher_phone'])): ?>
                    <div class="text-slate-500 dark:text-slate-400 print-text-dark font-mono tabular-nums"><?= $e($invoice['teacher_phone']) ?></div>
                <?php endif; ?>
            </div>

            <div class="sm:text-right">
                <span class="uppercase font-bold text-xs tracking-wider text-slate-400 print-text-dark block mb-1">Payment Method & Reference</span>
                <div class="font-bold text-sm text-indigo-600 dark:text-indigo-400 uppercase"><?= $e($invoice['payment_method']) ?></div>
                <div class="text-slate-600 dark:text-slate-300 print-text-dark font-mono mt-0.5 tabular-nums">TRX / Ref: <?= $e($invoice['payment_reference'] ?? 'PENDING') ?></div>
                <div class="text-slate-500 dark:text-slate-400 print-text-dark">Currency: <span class="font-mono uppercase"><?= $e($invoice['currency']) ?></span></div>
                <div class="text-slate-500 dark:text-slate-400 print-text-dark">Due Date: <?= date('M d, Y', strtotime($invoice['due_date'])) ?></div>
            </div>
        </div>

        <!-- Itemized Table -->
        <div class="py-8">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-800 print-border uppercase font-semibold text-xs text-slate-500 dark:text-slate-400 print-text-dark">
                        <th class="py-2.5">Item Description</th>
                        <th class="py-2.5 text-center">Billing Cycle</th>
                        <th class="py-2.5 text-right font-mono">Rate</th>
                        <th class="py-2.5 text-right font-mono">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 print-border">
                    <tr>
                        <td class="py-4">
                            <div class="font-bold text-sm text-slate-900 dark:text-white print-text-dark"><?= $e($invoice['plan_name']) ?></div>
                            <div class="text-xs text-slate-500 dark:text-slate-400 print-text-dark mt-0.5">
                                Full access to multi-class roll calls, parent WhatsApp journals, and OCR gradebook imports.
                            </div>
                        </td>
                        <td class="py-4 text-center uppercase font-mono text-slate-500 dark:text-slate-400 print-text-dark">
                            <?= $e($invoice['billing_cycle']) ?>
                        </td>
                        <td class="py-4 text-right font-mono text-slate-600 dark:text-slate-300 print-text-dark tabular-nums">
                            $<?= number_format((float) $invoice['amount'], 2) ?>
                        </td>
                        <td class="py-4 text-right font-mono font-bold text-slate-900 dark:text-white print-text-dark tabular-nums">
                            $<?= number_format((float) $invoice['amount'], 2) ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Calculation Summary & Verification Seal -->
        <div class="pt-6 border-t border-slate-200 dark:border-slate-800 print-border flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
            
            <!-- QR Verification Seal -->
            <div class="flex items-center gap-3">
                <?php 
                    $verifyUrl = (\App\Core\Config::get('APP_URL', 'http://localhost:8000')) . '/subscription/invoice/' . $invoice['id'];
                    $qrCodeUrl = \App\Utils\QrCodeGenerator::getQrCodeUrl($verifyUrl);
                ?>
                <img src="<?= $qrCodeUrl ?>" alt="Verification QR" class="w-20 h-20 rounded-xl p-1 bg-white border border-slate-200 print-border flex-shrink-0 shadow-sm">
                <div class="text-xs text-slate-500 dark:text-slate-400 print-text-dark">
                    <div class="font-bold text-slate-800 dark:text-slate-200 print-text-dark">Scan to Verify Invoice</div>
                    <p class="text-xs text-slate-400 mt-0.5 max-w-[220px]">
                        Cryptographically logged on Class Aid Suite database. Valid official invoice.
                    </p>
                </div>
            </div>

            <!-- Total Amount Card -->
            <div class="w-full sm:w-64 space-y-2 text-xs">
                <div class="flex justify-between text-slate-500 dark:text-slate-400 print-text-dark">
                    <span>Subtotal</span>
                    <span class="font-mono tabular-nums">$<?= number_format((float) $invoice['amount'], 2) ?></span>
                </div>
                <div class="flex justify-between text-slate-500 dark:text-slate-400 print-text-dark">
                    <span>Tax (0%)</span>
                    <span class="font-mono tabular-nums">$0.00</span>
                </div>
                <div class="pt-2 border-t border-slate-200 dark:border-slate-800 print-border flex justify-between font-bold text-base text-slate-900 dark:text-white print-text-dark">
                    <span>Total Amount</span>
                    <span class="font-mono text-indigo-600 dark:text-indigo-400 text-lg tabular-nums">$<?= number_format((float) $invoice['amount'], 2) ?></span>
                </div>
            </div>
        </div>

        <!-- Footer / Terms -->
        <div class="mt-12 pt-6 border-t border-slate-200 dark:border-slate-800 print-border text-xs text-slate-500 dark:text-slate-400 print-text-dark text-center space-y-1">
            <p>Thank you for using Class Aid to empower classroom learning and parent engagement.</p>
            <p>Questions about this invoice? Contact support via your teacher workspace portal.</p>
        </div>

    </div>

    <script>
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    </script>
</body>
</html>
