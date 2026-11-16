<div class="max-w-3xl mx-auto space-y-6">

    <!-- Top Action Bar -->
    <div class="flex items-center justify-between">
        <a href="/admin/plans" class="text-xs text-slate-500 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 flex items-center gap-1 transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Back to Plans Directory</span>
        </a>
    </div>

    <!-- Create Plan Card -->
    <div class="glass-card p-6 sm:p-8 space-y-6 border border-slate-200 dark:border-white/10">
        <div>
            <h1 class="text-xl font-black text-slate-900 dark:text-white">Create New Subscription Plan</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Add a new pricing tier for teacher workspaces with custom duration and features.
            </p>
        </div>

        <form method="POST" action="/admin/plans" class="space-y-5">
            <?= $csrf() ?>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Plan Display Name <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="name" name="name" required placeholder="e.g. Biannual Pro Plan" class="w-full px-3 py-2.5 text-xs rounded-xl glass-input">
                </div>

                <div>
                    <label for="code" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Unique Plan Code <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="code" name="code" required placeholder="e.g. biannual" class="w-full px-3 py-2.5 text-xs rounded-xl glass-input font-mono">
                    <span class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5 block">Lowercase alphanumeric, e.g. 'semi_annual', 'vip_lifetime'</span>
                </div>

                <div>
                    <label for="cycle" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Billing Cycle Type <span class="text-rose-500">*</span>
                    </label>
                    <select id="cycle" name="cycle" class="w-full px-3 py-2.5 text-xs rounded-xl glass-input">
                        <option value="trial">Free Trial</option>
                        <option value="monthly">Monthly</option>
                        <option value="annual" selected>Annually</option>
                        <option value="lifetime">Lifetime (No Expiry)</option>
                    </select>
                </div>

                <div>
                    <label for="price" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Price in USD ($) <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" step="0.01" min="0" id="price" name="price" required placeholder="89.99" class="w-full px-3 py-2.5 text-xs rounded-xl glass-input font-mono">
                </div>

                <div>
                    <label for="days" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Validity Duration (Days)
                    </label>
                    <input type="number" min="1" id="days" name="days" placeholder="365 (Leave blank for lifetime)" class="w-full px-3 py-2.5 text-xs rounded-xl glass-input font-mono">
                </div>

                <div>
                    <label for="discount" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Badge / Discount Tag (Optional)
                    </label>
                    <input type="text" id="discount" name="discount" placeholder="e.g. Save 20% or Best Value" class="w-full px-3 py-2.5 text-xs rounded-xl glass-input">
                </div>

                <div>
                    <label for="sort_order" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Display Sort Order
                    </label>
                    <input type="number" id="sort_order" name="sort_order" value="10" class="w-full px-3 py-2.5 text-xs rounded-xl glass-input font-mono">
                </div>

                <div class="flex items-center gap-2 pt-6">
                    <label class="flex items-center gap-2 text-xs text-slate-700 dark:text-slate-300 cursor-pointer">
                        <input type="checkbox" name="popular" value="1" class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 border-slate-300 dark:border-slate-700">
                        <span class="font-bold">Highlight as "Most Popular"</span>
                    </label>
                </div>

                <div class="sm:col-span-2">
                    <label for="features" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Plan Features (One feature per line)
                    </label>
                    <textarea id="features" name="features" rows="4" placeholder="Unlimited Students & Attendance&#10;Parent WhatsApp Call Logs&#10;OCR Gradebook Importer&#10;Priority Tech Support" class="w-full px-3 py-2.5 text-xs rounded-xl glass-input font-sans"></textarea>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-200 dark:border-white/10 flex items-center justify-end gap-3">
                <a href="/admin/plans" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl font-bold text-xs text-white bg-indigo-600 hover:bg-indigo-500 shadow-sm flex items-center gap-2 transition-all cursor-pointer">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Publish Plan Tier</span>
                </button>
            </div>
        </form>
    </div>

</div>
