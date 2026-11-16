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
                SaaS Subscription Plans
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                Configure pricing tiers, billing cycles, trial windows, and feature lists for your multi-tenant platform.
            </p>
        </div>

        <a href="/admin/plans/create" class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 shadow-sm flex items-center gap-2 transition-all self-start sm:self-auto cursor-pointer">
            <i data-lucide="plus-circle" class="w-4 h-4"></i>
            <span>Create New Plan</span>
        </a>
    </div>

    <!-- Plans Cards / Grid View -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        <?php foreach ($plans as $p): ?>
            <?php 
                $isActive = !empty($p['is_active']);
                $isPopular = !empty($p['popular']);
                $features = is_array($p['features']) ? $p['features'] : [];
            ?>
            <div class="glass-card p-5 rounded-2xl flex flex-col justify-between border-2 transition-all <?= $isActive ? 'border-slate-200 dark:border-slate-800 hover:border-indigo-500/50' : 'border-rose-500/30 opacity-60 bg-rose-50/20 dark:bg-rose-950/20' ?>">
                <div>
                    <!-- Badge & Status Row -->
                    <div class="flex items-center justify-between gap-2 mb-3">
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase font-mono tracking-wider <?= $isActive ? 'badge-emerald' : 'badge-rose' ?>">
                                <?= $isActive ? 'Active' : 'Inactive' ?>
                            </span>
                            <?php if ($isPopular): ?>
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold uppercase bg-indigo-600 text-white">
                                    <i data-lucide="sparkles" class="w-3 h-3"></i>
                                    <span>Popular</span>
                                </span>
                            <?php endif; ?>
                            <?php if (!empty($p['discount'])): ?>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase badge-amber">
                                    <?= $e($p['discount']) ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <span class="text-xs font-mono text-slate-400 font-bold uppercase">
                            <?= $e($p['code']) ?>
                        </span>
                    </div>

                    <!-- Title & Pricing -->
                    <h3 class="text-base font-bold text-slate-900 dark:text-white"><?= $e($p['name']) ?></h3>
                    
                    <div class="my-3 font-mono">
                        <span class="text-2xl font-black text-slate-900 dark:text-white tabular-nums">$<?= number_format((float) $p['price'], 2) ?></span>
                        <span class="text-xs text-slate-500 dark:text-slate-400 font-sans">/ <?= $e($p['cycle']) ?></span>
                        <div class="text-xs text-slate-400 mt-0.5 tabular-nums">
                            <?= $p['days'] !== null ? "{$p['days']} days validity" : 'Lifetime validity (Perpetual)' ?> &bull; ≈ PKR <?= number_format($p['price'] * 280) ?>
                        </div>
                    </div>

                    <!-- Features -->
                    <ul class="space-y-2 text-xs text-slate-600 dark:text-slate-300 my-4 border-t border-b border-slate-100 dark:border-slate-800 py-3">
                        <?php foreach (array_slice($features, 0, 4) as $feat): ?>
                            <li class="flex items-start gap-2">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-500 flex-shrink-0 mt-0.5"></i>
                                <span class="line-clamp-1"><?= $e($feat) ?></span>
                            </li>
                        <?php endforeach; ?>
                        <?php if (count($features) > 4): ?>
                            <li class="text-xs text-indigo-600 dark:text-indigo-400 font-semibold pl-6">
                                + <?= count($features) - 4 ?> more features
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>

                <!-- Footer Action Buttons -->
                <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <form method="POST" action="/admin/plans/<?= $p['id'] ?>/toggle">
                            <?= $csrf() ?>
                            <button type="submit" class="px-2.5 py-1.5 rounded-lg text-xs font-semibold <?= $isActive ? 'text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-950/40' : 'text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-950/40' ?> transition-colors cursor-pointer" title="<?= $isActive ? 'Deactivate Plan' : 'Activate Plan' ?>">
                                <?= $isActive ? 'Disable' : 'Enable' ?>
                            </button>
                        </form>

                        <?php if (!in_array($p['code'], ['trial', 'monthly', 'annual', 'lifetime'], true)): ?>
                            <form method="POST" action="/admin/plans/<?= $p['id'] ?>/delete" onsubmit="return confirm('Delete this custom plan?');">
                                <?= $csrf() ?>
                                <button type="submit" class="p-1.5 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-lg transition-colors cursor-pointer" title="Delete Plan">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>

                    <a href="/admin/plans/<?= $p['id'] ?>/edit" class="px-3.5 py-1.5 rounded-lg text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-500 shadow-sm transition-colors flex items-center gap-1">
                        <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                        <span>Edit Plan</span>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

</div>
