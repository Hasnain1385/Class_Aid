<form method="POST" action="/register" enctype="multipart/form-data" class="space-y-8" id="register-form">
    <?= $csrf() ?>

    <!-- Step 1: Account Information -->
    <div>
        <div class="flex items-center gap-2 mb-4 pb-2 border-b border-gray-200/40 dark:border-white/10">
            <span class="w-6 h-6 rounded-full bg-gradient-to-tr from-indigo-600 to-violet-500 text-white font-bold text-xs flex items-center justify-center shadow-md shadow-indigo-500/20">
                1
            </span>
            <h2 class="text-sm font-bold uppercase tracking-wider text-gray-900 dark:text-white">
                Teacher Credentials & School Identity
            </h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                    Your Full Name <span class="text-rose-500">*</span>
                </label>
                <div class="glass-input-group">
                    <div class="input-addon-box">
                        <i data-lucide="user" class="w-4 h-4"></i>
                    </div>
                    <input type="text" id="name" name="name" required placeholder="e.g. Mr. David Clark">
                </div>
            </div>

            <div>
                <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                    Work / School Email <span class="text-rose-500">*</span>
                </label>
                <div class="glass-input-group">
                    <div class="input-addon-box">
                        <i data-lucide="mail" class="w-4 h-4"></i>
                    </div>
                    <input type="email" id="email" name="email" required placeholder="teacher@school.edu" class="font-mono">
                </div>
            </div>

            <div>
                <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                    Password (Min 6 Characters) <span class="text-rose-500">*</span>
                </label>
                <div class="glass-input-group">
                    <div class="input-addon-box">
                        <i data-lucide="lock" class="w-4 h-4"></i>
                    </div>
                    <input type="password" id="password" name="password" minlength="6" required placeholder="••••••••">
                </div>
            </div>

            <div>
                <label for="school_name" class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                    School / Institution Name
                </label>
                <div class="glass-input-group">
                    <div class="input-addon-box">
                        <i data-lucide="building" class="w-4 h-4"></i>
                    </div>
                    <input type="text" id="school_name" name="school_name" placeholder="e.g. St. Jude High School">
                </div>
            </div>

            <div class="sm:col-span-2">
                <label for="phone" class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                    Phone / WhatsApp Number (For Parent Communication Journal)
                </label>
                <div class="glass-input-group">
                    <div class="input-addon-box">
                        <i data-lucide="phone" class="w-4 h-4"></i>
                    </div>
                    <input type="text" id="phone" name="phone" placeholder="+1 555-0199" class="font-mono">
                </div>
            </div>
        </div>
    </div>

    <!-- Step 2: Choose Subscription Plan -->
    <div>
        <div class="flex items-center justify-between mb-2 pb-2 border-b border-gray-200/40 dark:border-white/10">
            <div class="flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-gradient-to-tr from-indigo-600 to-violet-500 text-white font-bold text-xs flex items-center justify-center shadow-md shadow-indigo-500/20">
                    2
                </span>
                <h2 class="text-sm font-bold uppercase tracking-wider text-gray-900 dark:text-white">
                    Select SaaS Subscription Tier
                </h2>
            </div>
            <span class="text-[11px] text-gray-400 hidden sm:inline">Automatic 14-Day Full Trial Active</span>
        </div>
        <p class="text-xs text-gray-500 dark:text-gray-400 mb-5">
            All plans provide workspace data isolation, student rosters, parent journals, roll calls, and OCR gradebook imports.
        </p>

        <!-- Responsive Plan Grid: 1 col on mobile, 2 on tablet, 3 on desktop -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4" id="plan-cards-container">
            <?php foreach ($plans as $pCode => $p): ?>
                <?php 
                    $isSelected = ($pCode === $selectedPlan);
                    $isPopular = !empty($p['popular']);
                ?>
                <div onclick="selectPlanCard('<?= $pCode ?>')" 
                     id="plan-card-<?= $pCode ?>" 
                     class="plan-card relative flex flex-col justify-between p-4 rounded-2xl border-2 cursor-pointer transition-all hover:scale-[1.02] <?= $isSelected ? 'border-indigo-600 bg-indigo-500/10 ring-2 ring-indigo-500/30 shadow-xl' : 'border-gray-200/60 dark:border-white/10 hover:border-indigo-400/50 bg-gray-50/40 dark:bg-white/5' ?>">
                    
                    <input type="radio" name="plan_code" value="<?= $pCode ?>" <?= $isSelected ? 'checked' : '' ?> id="radio-<?= $pCode ?>" class="sr-only">

                    <div>
                        <!-- Header badge -->
                        <div class="flex items-center justify-between mb-2">
                            <?php if ($isPopular): ?>
                                <span class="inline-flex items-center gap-1 text-[9px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full bg-gradient-to-r from-indigo-600 to-violet-600 text-white shadow-sm">
                                    <i data-lucide="sparkles" class="w-3 h-3"></i> Most Popular
                                </span>
                            <?php elseif (!empty($p['discount'])): ?>
                                <span class="text-[9px] font-bold uppercase px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30">
                                    <?= $e($p['discount']) ?>
                                </span>
                            <?php else: ?>
                                <div></div>
                            <?php endif; ?>

                            <!-- Radio indicator circle -->
                            <div id="check-icon-<?= $pCode ?>" class="w-4 h-4 rounded-full border-2 <?= $isSelected ? 'border-indigo-600 bg-indigo-600 text-white' : 'border-gray-300 dark:border-white/20' ?> flex items-center justify-center transition-colors">
                                <i data-lucide="check" class="w-3 h-3 <?= $isSelected ? 'block' : 'hidden' ?>"></i>
                            </div>
                        </div>

                        <h3 class="font-extrabold text-sm text-gray-900 dark:text-white"><?= $e($p['name']) ?></h3>
                        
                        <div class="my-3 font-mono">
                            <span class="text-2xl font-black text-gray-900 dark:text-white">$<?= number_format($p['price'], 2) ?></span>
                            <span class="text-[10px] text-gray-500">/ <?= $e($p['cycle']) ?></span>
                        </div>

                        <ul class="space-y-1.5 text-[11px] text-gray-600 dark:text-gray-300">
                            <?php foreach (array_slice($p['features'], 0, 3) as $feat): ?>
                                <li class="flex items-start gap-1.5">
                                    <i data-lucide="check" class="w-3.5 h-3.5 text-emerald-500 flex-shrink-0 mt-0.5"></i>
                                    <span><?= $e($feat) ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <div class="mt-4 pt-3 border-t border-gray-200/40 dark:border-white/10 text-center">
                        <span id="label-action-<?= $pCode ?>" class="text-xs font-bold <?= $isSelected ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-400' ?>">
                            <?= $isSelected ? 'Selected Plan' : 'Select Plan' ?>
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Step 3: Payment Scanning & QR Code Verification -->
    <div id="step-3-container" class="space-y-4">
        <div class="flex items-center justify-between pb-2 border-b border-gray-200/40 dark:border-white/10">
            <div class="flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-gradient-to-tr from-indigo-600 to-violet-500 text-white font-bold text-xs flex items-center justify-center shadow-md shadow-indigo-500/20">
                    3
                </span>
                <h2 class="text-sm font-bold uppercase tracking-wider text-gray-900 dark:text-white" id="step-3-title">
                    Payment QR Scan & Activation
                </h2>
            </div>
            <div id="step-3-badge" class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400 flex items-center gap-1">
                <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                <span>No Card Details Needed</span>
            </div>
        </div>

        <!-- 14-Day Free Trial Notice Panel -->
        <div id="trial-panel" class="<?= ($selectedPlan === 'trial') ? 'block' : 'hidden' ?> p-5 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-900 dark:text-emerald-300 space-y-2">
            <div class="flex items-center gap-2 font-black text-sm text-emerald-700 dark:text-emerald-300">
                <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-500"></i>
                <span>14-Day Full Access Free Trial Selected</span>
            </div>
            <p class="text-xs text-gray-600 dark:text-gray-300">
                Zero payment or QR scan required today. Your educator workspace will be instantly unlocked for 14 days upon registration. You can explore all roster tools, parent communication logs, and attendance registers without risk.
            </p>
        </div>

        <!-- Paid Plan QR Verification Panel (NO Card Fields!) -->
        <div id="payment-panel" class="<?= ($selectedPlan !== 'trial') ? 'block' : 'hidden' ?> space-y-4">
            
            <!-- Summary bar showing selected tier & price in USD and PKR -->
            <div class="p-4 rounded-2xl bg-indigo-50/60 dark:bg-white/5 border border-indigo-200/50 dark:border-white/10 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-indigo-600/10 dark:bg-indigo-400/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center flex-shrink-0">
                        <i data-lucide="qr-code" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <div class="text-[10px] uppercase font-bold text-gray-400">Selected SaaS Plan:</div>
                        <div class="text-sm font-extrabold text-gray-900 dark:text-white" id="summary-plan-name">
                            <?= $e($plans[$selectedPlan]['name'] ?? 'Annual Professional Plan') ?>
                        </div>
                    </div>
                </div>

                <div class="flex items-baseline gap-3">
                    <div class="text-right">
                        <div class="text-xl sm:text-2xl font-black font-mono text-indigo-600 dark:text-indigo-400" id="summary-plan-price">
                            $<?= number_format($plans[$selectedPlan]['price'] ?? 89.99, 2) ?>
                        </div>
                        <div class="text-[11px] font-mono text-gray-500 font-bold" id="summary-plan-pkr">
                            ≈ PKR <?= number_format(($plans[$selectedPlan]['price'] ?? 89.99) * 280) ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Instant Access Trial Notice -->
            <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-950 dark:text-emerald-200 flex items-start gap-3">
                <div class="p-1 rounded-lg bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex-shrink-0 mt-0.5">
                    <i data-lucide="zap" class="w-4 h-4"></i>
                </div>
                <div class="text-xs space-y-1">
                    <div class="font-extrabold text-emerald-700 dark:text-emerald-300">
                        Instant Access Guaranteed: Active in Trial Mode While Payment Verifies
                    </div>
                    <p class="text-gray-600 dark:text-gray-300 leading-relaxed">
                        You do not have to wait for manual verification! Upon clicking Register, your educator account immediately enters <strong>14-Day Full-Access Trial Mode</strong>. You will have instant access to all features (students, attendance, parent calls, gradebook). Once our administrator verifies your payment, your subscription will automatically switch to your selected plan!
                    </p>
                </div>
            </div>

            <?php if (!empty($methods)): ?>
                <!-- Payment Method Tabs -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-2">
                        Select Payment Method for QR Scan:
                    </label>

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                        <?php 
                            $firstMethodCode = $methods[0]['code'];
                        ?>
                        <?php foreach ($methods as $m): ?>
                            <button type="button" 
                                    onclick="switchRegMethod('<?= $m['code'] ?>')" 
                                    id="reg-tab-btn-<?= $m['code'] ?>" 
                                    class="reg-method-btn py-2 px-3 rounded-xl border text-xs font-bold flex items-center justify-center gap-1.5 transition-all <?= ($m['code'] === $firstMethodCode) ? 'border-emerald-500 bg-emerald-500/15 text-emerald-600 dark:text-emerald-300 shadow-md ring-2 ring-emerald-500/20' : 'border-gray-200/50 dark:border-white/10 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5' ?>">
                                <i data-lucide="qr-code" class="w-3.5 h-3.5"></i>
                                <span class="truncate"><?= $e($m['title']) ?></span>
                            </button>
                        <?php endforeach; ?>
                    </div>

                    <!-- Hidden input to track selected payment method -->
                    <input type="hidden" name="payment_method" id="selected_payment_method" value="<?= $firstMethodCode ?>">

                    <!-- Payment Gateway QR Details Cards -->
                    <?php foreach ($methods as $m): ?>
                        <?php 
                            $isDefault = ($m['code'] === $firstMethodCode);
                            $rate = (float) ($m['conversion_rate'] ?: 280.0);
                            $localPrice = round(($plans[$selectedPlan]['price'] ?? 89.99) * $rate);
                        ?>
                        <div id="reg-method-panel-<?= $m['code'] ?>" 
                             data-rate="<?= $rate ?>"
                             class="reg-method-panel <?= $isDefault ? 'block' : 'hidden' ?> mt-3 p-5 rounded-2xl bg-gray-50/60 dark:bg-white/5 border border-gray-200/50 dark:border-white/10 space-y-4">
                            
                            <div class="flex flex-col sm:flex-row items-center gap-5 pb-4 border-b border-gray-200/40 dark:border-white/10">
                                <!-- Vector QR Code Image -->
                                <div class="flex-shrink-0 text-center">
                                    <div class="p-2.5 rounded-2xl bg-white border-2 border-emerald-500 shadow-xl inline-block relative">
                                        <img src="<?= $m['qr_url'] ?>" alt="<?= $e($m['title']) ?> QR" class="w-36 h-36 rounded-lg object-contain">
                                        <div class="absolute -bottom-2 left-1/2 -translate-x-1/2 bg-emerald-600 text-white text-[9px] font-black px-2 py-0.5 rounded-full uppercase tracking-wider whitespace-nowrap shadow">
                                            Scan in <?= $e($m['code']) ?> App
                                        </div>
                                    </div>
                                </div>

                                <!-- Account target info -->
                                <div class="flex-1 space-y-2 text-xs">
                                    <div class="flex items-center gap-2">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30">
                                            <?= $e($m['title']) ?>
                                        </span>
                                    </div>
                                    
                                    <div>
                                        <span class="text-[10px] uppercase font-bold text-gray-400 block">Account Title:</span>
                                        <span class="text-sm font-extrabold text-gray-900 dark:text-white"><?= $e($m['account_title']) ?></span>
                                    </div>

                                    <div>
                                        <span class="text-[10px] uppercase font-bold text-gray-400 block">Account Number / Mobile:</span>
                                        <div class="flex items-center gap-2">
                                            <span class="text-base font-black font-mono text-emerald-600 dark:text-emerald-400"><?= $e($m['account_number']) ?></span>
                                            <button type="button" onclick="navigator.clipboard.writeText('<?= $e($m['account_number']) ?>'); alert('Account number copied!');" class="p-1 rounded text-gray-400 hover:text-white" title="Copy Number">
                                                <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <div>
                                        <span class="text-[10px] uppercase font-bold text-gray-400 block">Amount to Transfer:</span>
                                        <span class="text-base font-black font-mono text-gray-900 dark:text-white reg-local-price" data-code="<?= $m['code'] ?>">
                                            PKR <?= number_format($localPrice) ?>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Instructions -->
                            <div class="p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-xs text-gray-700 dark:text-gray-300">
                                <span class="font-bold text-[11px] text-emerald-700 dark:text-emerald-400 uppercase tracking-wider block mb-0.5">How to Pay:</span>
                                <p class="text-[11px] leading-relaxed whitespace-pre-line"><?= $e($m['instructions']) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Transaction Reference Inputs -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                <div>
                    <label for="transaction_id" class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                        Transaction ID (TRX ID)
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <i data-lucide="receipt" class="w-4 h-4"></i>
                        </div>
                        <input type="text" id="transaction_id" name="transaction_id" placeholder="e.g. 11098234712 (Optional now)" class="w-full pl-9 pr-4 py-2.5 text-xs sm:text-sm rounded-xl glass-input placeholder-gray-400 font-mono font-bold uppercase">
                    </div>
                    <span class="text-[10px] text-gray-400 mt-1 block">From your EasyPaisa / Bank confirmation SMS. You can also provide it later from your dashboard.</span>
                </div>

                <div>
                    <label for="sender_phone" class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                        Sender Mobile Number (Optional)
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <i data-lucide="phone-call" class="w-4 h-4"></i>
                        </div>
                        <input type="text" id="sender_phone" name="sender_phone" placeholder="0300-1234567" class="w-full pl-9 pr-4 py-2.5 text-xs sm:text-sm rounded-xl glass-input placeholder-gray-400 font-mono">
                    </div>
                </div>

                <div class="sm:col-span-2">
                    <label for="payment_proof" class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                        Upload Screenshot Slip / Receipt (Optional)
                    </label>
                    <input type="file" id="payment_proof" name="payment_proof" accept="image/*,application/pdf" class="w-full px-3 py-2 text-xs rounded-xl glass-input text-gray-400 file:mr-3 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-600 file:text-white hover:file:bg-emerald-500">
                </div>
            </div>
        </div>
    </div>

    <!-- Submit Button & Footer Link -->
    <div class="text-center pt-2 space-y-4">
        <button type="submit" id="submit-btn" class="w-full sm:w-auto px-10 py-3.5 rounded-xl font-bold text-sm text-white bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 shadow-xl shadow-indigo-600/30 inline-flex items-center justify-center gap-2 group transition-all">
            <span id="submit-btn-text">
                <?= ($selectedPlan === 'trial') ? 'Start 14-Day Free Trial & Launch Workspace' : 'Activate Workspace (Trial Mode Active) &rarr;' ?>
            </span>
            <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
        </button>

        <div class="text-xs text-gray-500 dark:text-gray-400">
            Already registered? <a href="/login" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline">Sign In to your account</a>
        </div>
    </div>
</form>

<script>
    const plansData = <?= json_encode($plans) ?>;

    function selectPlanCard(code) {
        document.querySelectorAll('.plan-card').forEach(card => {
            card.className = 'plan-card relative flex flex-col justify-between p-4 rounded-2xl border-2 cursor-pointer transition-all hover:scale-[1.02] border-gray-200/60 dark:border-white/10 hover:border-indigo-400/50 bg-gray-50/40 dark:bg-white/5';
        });

        document.querySelectorAll('input[name="plan_code"]').forEach(r => {
            r.checked = (r.value === code);
        });

        document.querySelectorAll('[id^="check-icon-"]').forEach(iconBox => {
            iconBox.className = 'w-4 h-4 rounded-full border-2 border-gray-300 dark:border-white/20 flex items-center justify-center transition-colors';
            const svg = iconBox.querySelector('i, svg');
            if (svg) svg.classList.add('hidden');
        });

        document.querySelectorAll('[id^="label-action-"]').forEach(lbl => {
            lbl.className = 'text-xs font-bold text-gray-400';
            lbl.textContent = 'Select Plan';
        });

        // Activate selected card
        const card = document.getElementById('plan-card-' + code);
        if (card) {
            card.className = 'plan-card relative flex flex-col justify-between p-4 rounded-2xl border-2 cursor-pointer transition-all hover:scale-[1.02] border-indigo-600 bg-indigo-500/10 ring-2 ring-indigo-500/30 shadow-xl';
        }

        const iconBox = document.getElementById('check-icon-' + code);
        if (iconBox) {
            iconBox.className = 'w-4 h-4 rounded-full border-2 border-indigo-600 bg-indigo-600 text-white flex items-center justify-center transition-colors';
            const svg = iconBox.querySelector('i, svg');
            if (svg) svg.classList.remove('hidden');
        }

        const lbl = document.getElementById('label-action-' + code);
        if (lbl) {
            lbl.className = 'text-xs font-bold text-indigo-600 dark:text-indigo-400';
            lbl.textContent = 'Selected Plan';
        }

        // Update Step 3
        const selected = plansData[code] || {};
        const trialPanel = document.getElementById('trial-panel');
        const paymentPanel = document.getElementById('payment-panel');
        const submitBtnText = document.getElementById('submit-btn-text');
        const summaryPlanName = document.getElementById('summary-plan-name');
        const summaryPlanPrice = document.getElementById('summary-plan-price');
        const summaryPlanPkr = document.getElementById('summary-plan-pkr');

        if (code === 'trial') {
            if (trialPanel) trialPanel.classList.remove('hidden');
            if (paymentPanel) paymentPanel.classList.add('hidden');
            if (submitBtnText) submitBtnText.textContent = 'Start 14-Day Free Trial & Launch Workspace';
        } else {
            if (trialPanel) trialPanel.classList.add('hidden');
            if (paymentPanel) paymentPanel.classList.remove('hidden');
            const price = Number(selected.price || 0);
            if (summaryPlanName) summaryPlanName.textContent = selected.name || 'Selected Tier';
            if (summaryPlanPrice) summaryPlanPrice.textContent = '$' + price.toFixed(2);
            if (summaryPlanPkr) summaryPlanPkr.textContent = '≈ PKR ' + Math.round(price * 280).toLocaleString();
            if (submitBtnText) submitBtnText.textContent = 'Activate Workspace (Trial Mode Active) →';

            // Update PKR amounts in each method panel
            document.querySelectorAll('.reg-local-price').forEach(el => {
                const methodCode = el.dataset.code;
                const panel = document.getElementById('reg-method-panel-' + methodCode);
                const rate = panel ? parseFloat(panel.dataset.rate || 280) : 280;
                el.textContent = 'PKR ' + Math.round(price * rate).toLocaleString();
            });
        }

        lucide.createIcons();
    }

    function switchRegMethod(code) {
        document.querySelectorAll('.reg-method-btn').forEach(btn => {
            btn.className = 'reg-method-btn py-2 px-3 rounded-xl border text-xs font-bold flex items-center justify-center gap-1.5 transition-all border-gray-200/50 dark:border-white/10 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5';
        });

        const activeBtn = document.getElementById('reg-tab-btn-' + code);
        if (activeBtn) {
            activeBtn.className = 'reg-method-btn py-2 px-3 rounded-xl border text-xs font-bold flex items-center justify-center gap-1.5 transition-all border-emerald-500 bg-emerald-500/15 text-emerald-600 dark:text-emerald-300 shadow-md ring-2 ring-emerald-500/20';
        }

        document.querySelectorAll('.reg-method-panel').forEach(panel => {
            panel.classList.add('hidden');
        });

        const activePanel = document.getElementById('reg-method-panel-' + code);
        if (activePanel) {
            activePanel.classList.remove('hidden');
        }

        const hiddenInput = document.getElementById('selected_payment_method');
        if (hiddenInput) {
            hiddenInput.value = code;
        }

        lucide.createIcons();
    }
</script>