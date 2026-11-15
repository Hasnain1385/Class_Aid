<div class="max-w-3xl mx-auto py-8 px-4">
    
    <!-- Lock & Shield Header -->
    <div class="text-center mb-6">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-3xl bg-amber-500/15 text-amber-500 dark:text-amber-400 border border-amber-500/30 shadow-lg shadow-amber-500/10 mb-3 animate-bounce">
            <i data-lucide="lock" class="w-8 h-8"></i>
        </div>
        <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900 dark:text-white">
            Workspace Locked — Payment Verification
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1 max-w-lg mx-auto">
            Select your preferred payment method below (EasyPaisa QR, JazzCash, Bank Transfer, or Card) to unlock your educator workspace.
        </p>
    </div>

    <!-- Main Payment Verification Card -->
    <div class="glass-card p-6 sm:p-8 space-y-6 border border-amber-500/30 shadow-xl">
        
        <!-- Order Summary & Selected Tier -->
        <div class="p-4 rounded-2xl bg-amber-500/10 dark:bg-amber-500/5 border border-amber-500/20 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400 block mb-0.5">
                    Selected SaaS Tier
                </span>
                <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <span><?= $e($selectedPlan['name']) ?></span>
                    <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-indigo-600 text-white"><?= strtoupper($selectedPlan['cycle']) ?></span>
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Unlimited students, attendance roll calls, parent WhatsApp audits, and OCR grading.
                </p>
            </div>

            <div class="text-left sm:text-right flex-shrink-0">
                <div class="text-2xl sm:text-3xl font-black font-mono text-slate-900 dark:text-white tabular-nums">
                    $<?= number_format($selectedPlan['price'], 2) ?>
                </div>
                <div class="text-xs text-slate-500 dark:text-slate-400 uppercase font-mono tabular-nums">
                    ≈ PKR <?= number_format($selectedPlan['price'] * 280, 0) ?>
                </div>
            </div>
        </div>

        <!-- Switch Plan Dropdown -->
        <div>
            <label for="plan-switcher" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                Switch Plan or Choose Free Trial
            </label>
            <select id="plan-switcher" onchange="window.location.href='/subscription/paywall?plan=' + this.value" class="w-full px-3 py-2 text-xs rounded-xl glass-input font-medium">
                <?php foreach ($plans as $pCode => $p): ?>
                    <option value="<?= $pCode ?>" <?= ($pCode === $selectedPlanCode) ? 'selected' : '' ?>>
                        <?= $e($p['name']) ?> — $<?= number_format($p['price'], 2) ?> / <?= $e($p['cycle']) ?> <?= !empty($p['discount']) ? ' (' . $p['discount'] . ')' : '' ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <?php if ($selectedPlanCode === 'trial'): ?>
            <!-- 14-Day Free Trial Notice & 1-Click Activation -->
            <div class="p-5 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-900 dark:text-emerald-200 space-y-3">
                <div class="flex items-center gap-2 font-bold text-sm">
                    <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-500"></i>
                    <span>14-Day Free Trial Selected</span>
                </div>
                <p class="text-xs text-emerald-800 dark:text-emerald-300">
                    You can activate full access immediately for 14 days without entering credit card or payment details.
                </p>
                <form method="POST" action="/subscription/verify">
                    <?= $csrf() ?>
                    <input type="hidden" name="plan_code" value="trial">
                    <input type="hidden" name="payment_method" value="trial">
                    <button type="submit" class="w-full py-3 px-4 rounded-xl font-bold text-xs text-white bg-emerald-600 hover:bg-emerald-500 shadow-sm flex items-center justify-center gap-2 transition-all cursor-pointer">
                        <i data-lucide="unlock" class="w-4 h-4"></i>
                        <span>Activate 14-Day Free Trial & Unlock Workspace</span>
                    </button>
                </form>
            </div>
        <?php else: ?>

            <!-- Payment Method Tabs -->
            <div class="space-y-4">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 block">
                    Choose Payment Gateway:
                </span>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                    <?php 
                        $firstMethod = !empty($methods) ? $methods[0]['code'] : 'easypaisa';
                    ?>
                    <?php foreach ($methods as $m): ?>
                        <button type="button" onclick="switchMethodTab('<?= $m['code'] ?>')" id="tab-btn-<?= $m['code'] ?>" class="method-tab-btn py-2.5 px-3 rounded-xl border text-xs font-bold flex flex-col items-center justify-center gap-1 transition-all <?= ($m['code'] === $firstMethod) ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 shadow-sm ring-2 ring-emerald-500/20' : 'border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' ?>">
                            <i data-lucide="qr-code" class="w-4 h-4"></i>
                            <span class="truncate max-w-full"><?= $e($m['title']) ?></span>
                        </button>
                    <?php endforeach; ?>
                </div>

                <!-- Immediate Trial Mode Assurance Notice -->
                <div class="p-4 rounded-2xl bg-indigo-50/50 dark:bg-indigo-950/20 border border-indigo-200 dark:border-indigo-900/50 text-indigo-950 dark:text-indigo-200 flex items-start gap-3">
                    <i data-lucide="zap" class="w-5 h-5 text-indigo-500 flex-shrink-0 mt-0.5"></i>
                    <div class="text-xs space-y-1">
                        <div class="font-bold text-indigo-700 dark:text-indigo-300">Instant 14-Day Full Trial Mode Activation</div>
                        <p class="text-slate-600 dark:text-slate-300">
                            Submitting your payment details below immediately unlocks your educator workspace in <strong>14-Day Full Access Trial Mode</strong>. You do not need to wait for manual verification to start taking attendance and logging calls. Once verified by our admin, your account will automatically switch to your selected plan!
                        </p>
                    </div>
                </div>

                <!-- Panels for Each Method -->
                <?php foreach ($methods as $m): ?>
                    <?php 
                        $isDefault = ($m['code'] === $firstMethod);
                        $rate = (float) ($m['conversion_rate'] ?: 280.0);
                        $localPrice = round($selectedPlan['price'] * $rate);
                    ?>
                    <div id="method-panel-<?= $m['code'] ?>" class="method-panel <?= $isDefault ? 'block' : 'hidden' ?> space-y-5 p-5 rounded-2xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800">
                        
                        <!-- Header with QR code and Account info -->
                        <div class="flex flex-col sm:flex-row items-center gap-5 pb-4 border-b border-slate-200/60 dark:border-slate-800">
                            <!-- QR Code with Scanner Glow -->
                            <div class="flex-shrink-0 text-center">
                                <div class="p-2.5 rounded-2xl bg-white border-2 border-emerald-500 shadow-lg inline-block relative group">
                                    <img src="<?= $m['qr_url'] ?>" alt="<?= $e($m['title']) ?> QR" class="w-36 h-36 rounded-lg object-contain">
                                    <div class="absolute -bottom-2 left-1/2 -translate-x-1/2 bg-emerald-600 text-white text-xs font-bold px-2 py-0.5 rounded-full uppercase tracking-wider whitespace-nowrap shadow">
                                        Scan in <?= $e($m['code']) ?> App
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Target Info -->
                            <div class="flex-1 space-y-2 text-xs">
                                <div class="flex items-center gap-2">
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase badge-emerald">
                                        Direct <?= $e($m['title']) ?>
                                    </span>
                                </div>
                                
                                <div>
                                    <span class="text-xs uppercase font-bold text-slate-400 block">Account Title:</span>
                                    <span class="text-sm font-bold text-slate-900 dark:text-white"><?= $e($m['account_title']) ?></span>
                                </div>

                                <div>
                                    <span class="text-xs uppercase font-bold text-slate-400 block">Account Number / Mobile:</span>
                                    <div class="flex items-center gap-2">
                                        <span class="text-base font-bold font-mono text-emerald-600 dark:text-emerald-400 tabular-nums"><?= $e($m['account_number']) ?></span>
                                        <button type="button" onclick="navigator.clipboard.writeText('<?= $e($m['account_number']) ?>'); alert('Account number copied!');" class="p-1 rounded text-slate-400 hover:text-slate-600 dark:hover:text-white" title="Copy Number">
                                            <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="pt-1">
                                    <span class="text-xs uppercase font-bold text-slate-400 block">Amount to Transfer:</span>
                                    <span class="text-lg font-black font-mono text-slate-900 dark:text-white tabular-nums">
                                        PKR <?= number_format($localPrice) ?>
                                    </span>
                                    <span class="text-xs text-slate-500 tabular-nums">($<?= number_format($selectedPlan['price'], 2) ?> USD)</span>
                                </div>
                            </div>
                        </div>

                        <!-- Instructions -->
                        <div class="p-3.5 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-xs text-slate-700 dark:text-slate-300 space-y-1">
                            <span class="font-bold text-xs text-emerald-700 dark:text-emerald-400 uppercase tracking-wider block">How to Pay:</span>
                            <p class="whitespace-pre-line text-xs leading-relaxed"><?= $e($m['instructions']) ?></p>
                        </div>

                        <!-- Verification Submission Form -->
                        <form method="POST" action="/subscription/verify" enctype="multipart/form-data" class="space-y-4">
                            <?= $csrf() ?>
                            <input type="hidden" name="plan_code" value="<?= $selectedPlanCode ?>">
                            <input type="hidden" name="payment_method" value="<?= $m['code'] ?>">

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                        Transaction ID (TRX ID) *
                                    </label>
                                    <input type="text" name="transaction_id" required placeholder="e.g. 11098234712 or TID-998" class="w-full px-3 py-2 text-xs rounded-xl glass-input font-mono font-bold uppercase">
                                    <span class="text-xs text-slate-400 mt-1 block">Found in the SMS receipt or payment confirmation screen.</span>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                        Sender Phone / Account (Optional)
                                    </label>
                                    <input type="text" name="sender_phone" placeholder="Your sender mobile number" class="w-full px-3 py-2 text-xs rounded-xl glass-input font-mono tabular-nums">
                                </div>

                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                        Upload Screenshot Receipt / Slip (Optional)
                                    </label>
                                    <input type="file" name="payment_proof" accept="image/*,application/pdf" class="w-full px-3 py-2 text-xs rounded-xl glass-input text-slate-400 file:mr-3 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-600 file:text-white hover:file:bg-emerald-500">
                                </div>
                            </div>

                            <button type="submit" class="w-full py-3 px-4 rounded-xl font-bold text-xs text-white bg-indigo-600 hover:bg-indigo-500 shadow-sm flex items-center justify-center gap-2 transition-all cursor-pointer">
                                <i data-lucide="check-circle" class="w-4 h-4"></i>
                                <span>Submit <?= $e($m['title']) ?> & Launch Workspace (Trial Active) &rarr;</span>
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php endif; ?>

        <!-- Trust Badges & Guarantee -->
        <div class="pt-4 border-t border-slate-200/60 dark:border-slate-800 flex flex-wrap items-center justify-around text-center gap-4 text-xs text-slate-500 dark:text-slate-400">
            <div class="flex items-center gap-1.5">
                <i data-lucide="shield-check" class="w-4 h-4 text-emerald-500"></i>
                <span>EasyPaisa & Bank Audited</span>
            </div>
            <div class="flex items-center gap-1.5">
                <i data-lucide="file-text" class="w-4 h-4 text-indigo-500"></i>
                <span>Official PDF Invoice Emailed</span>
            </div>
            <div class="flex items-center gap-1.5">
                <i data-lucide="zap" class="w-4 h-4 text-amber-500"></i>
                <span>Fast Manual / Auto Unlock</span>
            </div>
        </div>

    </div>

</div>

<script>
    function switchMethodTab(code) {
        document.querySelectorAll('.method-tab-btn').forEach(btn => {
            btn.className = 'method-tab-btn py-2.5 px-3 rounded-xl border text-xs font-bold flex flex-col items-center justify-center gap-1 transition-all border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800';
        });

        const activeBtn = document.getElementById('tab-btn-' + code);
        if (activeBtn) {
            activeBtn.className = 'method-tab-btn py-2.5 px-3 rounded-xl border text-xs font-bold flex flex-col items-center justify-center gap-1 transition-all border-emerald-500 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 shadow-sm ring-2 ring-emerald-500/20';
        }

        document.querySelectorAll('.method-panel').forEach(panel => {
            panel.classList.add('hidden');
        });

        const activePanel = document.getElementById('method-panel-' + code);
        if (activePanel) {
            activePanel.classList.remove('hidden');
        }

        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }
</script>
