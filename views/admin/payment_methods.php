<div class="space-y-6">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="/admin" class="text-xs text-slate-400 hover:text-indigo-500 flex items-center gap-1 transition-colors">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    <span>Command Center</span>
                </a>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white mt-1">
                Payment Gateways & EasyPaisa QR Setup
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                Configure manual and automatic payment methods, account titles, and QR code text payloads for student subscriptions.
            </p>
        </div>

        <button type="button" onclick="document.getElementById('modal-create-method').classList.remove('hidden')" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-500 shadow-sm transition-all cursor-pointer">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Add Payment Method</span>
        </button>
    </div>

    <!-- Payment Methods Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($methods as $m): ?>
            <?php 
                $isActive = (bool) $m['is_active'];
                $isEasyPaisa = ($m['code'] === 'easypaisa');
            ?>
            <div class="glass-card p-6 rounded-2xl flex flex-col justify-between border <?= $isActive ? ($isEasyPaisa ? 'border-emerald-500/50 bg-emerald-50/20 dark:bg-emerald-950/20' : 'border-indigo-500/30') : 'border-slate-200 dark:border-slate-800 opacity-60' ?> relative overflow-hidden">
                
                <div>
                    <!-- Badge & Status -->
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <span class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-900 dark:text-white font-bold text-xs uppercase font-mono border border-slate-200 dark:border-slate-700">
                                <?= strtoupper(substr($m['code'], 0, 2)) ?>
                            </span>
                            <div>
                                <h3 class="font-bold text-base text-slate-900 dark:text-white"><?= $e($m['title']) ?></h3>
                                <div class="text-xs font-mono text-slate-400">Code: <span class="text-indigo-600 dark:text-indigo-400 font-bold"><?= $e($m['code']) ?></span></div>
                            </div>
                        </div>

                        <form method="POST" action="/admin/payment-methods/<?= $m['id'] ?>/toggle">
                            <?= $csrf() ?>
                            <button type="submit" class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase transition-colors cursor-pointer <?= $isActive ? 'badge-emerald' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-700' ?>">
                                <?= $isActive ? 'Active' : 'Disabled' ?>
                            </button>
                        </form>
                    </div>

                    <!-- QR Code Preview & Account Details -->
                    <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200/60 dark:border-slate-800 mb-4 flex items-center gap-4">
                        <div class="flex-shrink-0">
                            <img src="<?= $m['qr_url'] ?>" alt="<?= $e($m['title']) ?> QR" class="w-20 h-20 rounded-lg p-1 bg-white border border-slate-200 shadow-sm">
                        </div>
                        <div class="text-xs space-y-1 overflow-hidden">
                            <div>
                                <span class="text-xs uppercase font-bold text-slate-400 block">Account Title</span>
                                <strong class="text-slate-900 dark:text-white truncate block"><?= $e($m['account_title'] ?: 'Not Specified') ?></strong>
                            </div>
                            <div>
                                <span class="text-xs uppercase font-bold text-slate-400 block">Account / Mobile #</span>
                                <span class="font-mono text-indigo-600 dark:text-indigo-400 font-bold truncate block tabular-nums"><?= $e($m['account_number'] ?: 'Not Specified') ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Scanned QR Payload Preview -->
                    <div class="text-xs mb-3">
                        <span class="text-xs uppercase font-bold text-slate-400 block mb-0.5">Scanned Text / Payload:</span>
                        <div class="p-2.5 rounded-lg bg-slate-100 dark:bg-slate-950 font-mono text-xs text-slate-700 dark:text-slate-300 truncate max-w-full border border-slate-200 dark:border-slate-800" title="<?= $e($m['qr_payload']) ?>">
                            <?= $e($m['qr_payload'] ?: 'None (auto-generated placeholder)') ?>
                        </div>
                    </div>

                    <!-- Instructions preview -->
                    <div class="text-xs text-slate-500 dark:text-slate-400 mb-4">
                        <span class="text-xs uppercase font-bold text-slate-400 block mb-0.5">Instructions:</span>
                        <p class="text-xs text-slate-700 dark:text-slate-300 line-clamp-2"><?= nl2br($e($m['instructions'])) ?></p>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between gap-2">
                    <button type="button" onclick="editMethod(<?= htmlspecialchars(json_encode($m), ENT_QUOTES, 'UTF-8') ?>)" class="flex-1 py-1.5 px-3 rounded-lg text-xs font-semibold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950/40 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 border border-indigo-200 dark:border-indigo-800 transition-colors flex items-center justify-center gap-1.5 cursor-pointer">
                        <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                        <span>Edit Settings & QR</span>
                    </button>

                    <form method="POST" action="/admin/payment-methods/<?= $m['id'] ?>/delete" onsubmit="return confirm('Are you sure you want to delete this payment method?')">
                        <?= $csrf() ?>
                        <button type="submit" class="p-1.5 rounded-lg text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors cursor-pointer" title="Delete Method">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

</div>

<!-- Modal: Edit Payment Method -->
<div id="modal-edit-method" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
    <div class="glass-card max-w-xl w-full p-6 sm:p-8 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl relative bg-white dark:bg-slate-900 text-slate-900 dark:text-white">
        <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100 dark:border-slate-800">
            <h2 class="text-base font-bold flex items-center gap-2">
                <i data-lucide="settings-2" class="w-5 h-5 text-indigo-500"></i>
                <span id="edit-modal-title">Configure Payment Gateway & QR Code</span>
            </h2>
            <button type="button" onclick="document.getElementById('modal-edit-method').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 dark:hover:text-white">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form id="edit-method-form" method="POST" action="" enctype="multipart/form-data" class="space-y-4">
            <?= $csrf() ?>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Display Title *</label>
                    <input type="text" id="edit-title" name="title" required class="w-full px-3 py-2 text-xs rounded-xl glass-input">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Account Title (Beneficiary Name) *</label>
                    <input type="text" id="edit-account-title" name="account_title" required class="w-full px-3 py-2 text-xs rounded-xl glass-input">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Mobile / Account Number *</label>
                    <input type="text" id="edit-account-number" name="account_number" required class="w-full px-3 py-2 text-xs rounded-xl glass-input font-mono tabular-nums">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">1 USD to Local Currency Rate (e.g. 280 PKR)</label>
                    <input type="number" step="0.01" id="edit-conversion-rate" name="conversion_rate" value="280.00" class="w-full px-3 py-2 text-xs rounded-xl glass-input font-mono tabular-nums">
                </div>
            </div>

            <!-- Scanned QR Code Text Payload -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                    Scanned Text of QR Code (Payload String)
                </label>
                <textarea id="edit-qr-payload" name="qr_payload" rows="3" placeholder="Paste the exact raw scanned text of the EasyPaisa/JazzCash QR code..." class="w-full px-3 py-2 text-xs rounded-xl glass-input font-mono placeholder-slate-400"></textarea>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    Tip: Scan your EasyPaisa / JazzCash merchant or personal QR with any standard barcode reader app on your phone, copy the resulting string, and paste it here. A dynamic SVG QR code will be generated matching it!
                </p>
            </div>

            <!-- Upload Custom QR Image -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Or Upload Custom QR Code Image (PNG / JPG / WEBP)</label>
                <input type="file" name="qr_image" accept="image/*" class="w-full px-3 py-2 text-xs rounded-xl glass-input text-slate-400 file:mr-3 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-600 file:text-white hover:file:bg-indigo-500">
            </div>

            <!-- Instructions -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Payment Instructions to Teacher</label>
                <textarea id="edit-instructions" name="instructions" rows="3" class="w-full px-3 py-2 text-xs rounded-xl glass-input"></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                <button type="button" onclick="document.getElementById('modal-edit-method').classList.add('hidden')" class="px-4 py-2 text-xs font-semibold rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2 text-xs font-bold rounded-xl text-white bg-indigo-600 hover:bg-indigo-500 shadow-sm transition-all cursor-pointer">
                    Save Gateway Changes
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Add New Payment Method -->
<div id="modal-create-method" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
    <div class="glass-card max-w-xl w-full p-6 sm:p-8 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl relative bg-white dark:bg-slate-900 text-slate-900 dark:text-white">
        <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100 dark:border-slate-800">
            <h2 class="text-base font-bold flex items-center gap-2">
                <i data-lucide="plus-circle" class="w-5 h-5 text-indigo-500"></i>
                <span>Add New Payment Gateway</span>
            </h2>
            <button type="button" onclick="document.getElementById('modal-create-method').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 dark:hover:text-white">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form method="POST" action="/admin/payment-methods" enctype="multipart/form-data" class="space-y-4">
            <?= $csrf() ?>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Code (e.g. easypaisa, nayapay) *</label>
                    <input type="text" name="code" required placeholder="nayapay" class="w-full px-3 py-2 text-xs rounded-xl glass-input font-mono">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Display Title *</label>
                    <input type="text" name="title" required placeholder="NayaPay Digital Wallet" class="w-full px-3 py-2 text-xs rounded-xl glass-input">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Account Title *</label>
                    <input type="text" name="account_title" required placeholder="Mirza Hasnain Baig" class="w-full px-3 py-2 text-xs rounded-xl glass-input">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Mobile / Account Number *</label>
                    <input type="text" name="account_number" required placeholder="0300-1234567" class="w-full px-3 py-2 text-xs rounded-xl glass-input font-mono tabular-nums">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Scanned Text of QR Code (Payload String)</label>
                <textarea name="qr_payload" rows="2" placeholder="Paste scanned text string from the payment app QR..." class="w-full px-3 py-2 text-xs rounded-xl glass-input font-mono placeholder-slate-400"></textarea>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Payment Instructions to Teacher</label>
                <textarea name="instructions" rows="2" placeholder="Send money to mobile account and enter TRX ID below." class="w-full px-3 py-2 text-xs rounded-xl glass-input"></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                <button type="button" onclick="document.getElementById('modal-create-method').classList.add('hidden')" class="px-4 py-2 text-xs font-semibold rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2 text-xs font-bold rounded-xl text-white bg-indigo-600 hover:bg-indigo-500 shadow-sm transition-all cursor-pointer">
                    Create Gateway
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function editMethod(m) {
        document.getElementById('edit-modal-title').textContent = 'Configure ' + m.title;
        document.getElementById('edit-method-form').action = '/admin/payment-methods/' + m.id;
        document.getElementById('edit-title').value = m.title || '';
        document.getElementById('edit-account-title').value = m.account_title || '';
        document.getElementById('edit-account-number').value = m.account_number || '';
        document.getElementById('edit-conversion-rate').value = m.conversion_rate || '280.00';
        document.getElementById('edit-qr-payload').value = m.qr_payload || '';
        document.getElementById('edit-instructions').value = m.instructions || '';
        document.getElementById('modal-edit-method').classList.remove('hidden');
    }
</script>
