<div class="space-y-6">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="/admin" class="text-xs text-slate-500 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 flex items-center gap-1 transition-colors">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    <span>Command Center</span>
                </a>
            </div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white mt-1">
                System Broadcasts & Announcements
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">
                Publish platform updates, feature releases, and maintenance notices to all teacher dashboards.
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left: Composer -->
        <div class="glass-card p-6">
            <h2 class="text-sm font-bold uppercase tracking-wider text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                <i data-lucide="megaphone" class="w-4 h-4 text-purple-500 dark:text-purple-400"></i>
                <span>Publish New Broadcast</span>
            </h2>

            <form method="POST" action="/admin/announcements" class="space-y-4">
                <?= $csrf() ?>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Title / Subject *
                    </label>
                    <input type="text" name="title" required placeholder="e.g. Platform update v2.1 released" class="w-full px-3 py-2 text-xs rounded-lg glass-input">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Message Content *
                    </label>
                    <textarea name="content" required rows="4" placeholder="Detail the update, scheduled maintenance, or new educational feature..." class="w-full px-3 py-2 text-xs rounded-lg glass-input"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            Severity / Type
                        </label>
                        <select name="type" class="w-full px-3 py-2 text-xs rounded-lg glass-input">
                            <option value="info">Information (Blue)</option>
                            <option value="success">Feature Update (Green)</option>
                            <option value="warning">Maintenance Alert (Yellow)</option>
                            <option value="urgent">Urgent Notice (Red)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            Target Audience
                        </label>
                        <select name="target_role" class="w-full px-3 py-2 text-xs rounded-lg glass-input">
                            <option value="all">All Teachers</option>
                            <option value="teacher">Teachers Only</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Auto-Expiry Date (Optional)
                    </label>
                    <input type="date" name="expires_at" class="w-full px-3 py-2 text-xs rounded-lg glass-input">
                </div>

                <button type="submit" class="w-full py-2.5 rounded-xl text-xs font-bold text-white bg-purple-600 hover:bg-purple-500 shadow-sm flex items-center justify-center gap-2 transition-all cursor-pointer">
                    <i data-lucide="send" class="w-4 h-4"></i>
                    <span>Broadcast Live</span>
                </button>
            </form>
        </div>

        <!-- Right: Published Announcements List -->
        <div class="lg:col-span-2 glass-card p-6">
            <h2 class="text-sm font-bold uppercase tracking-wider text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                <i data-lucide="radio" class="w-4 h-4 text-emerald-500 dark:text-emerald-400"></i>
                <span>Active Broadcasts (<?= count($announcements) ?>)</span>
            </h2>

            <?php if (empty($announcements)): ?>
                <p class="text-xs text-slate-500 dark:text-slate-400 italic p-6 text-center">No announcements published yet.</p>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($announcements as $a): ?>
                        <?php 
                            $typeMeta = $types[$a['type']] ?? $types['info'];
                        ?>
                        <div class="p-4 rounded-xl border <?= $typeMeta['banner'] ?> transition-all flex flex-col justify-between gap-3">
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-full <?= $typeMeta['badge'] ?>">
                                        <?= $e($typeMeta['label']) ?>
                                    </span>
                                    <div class="text-[10px] text-slate-500 dark:text-slate-400 font-mono">
                                        Published <?= date('M d, Y', strtotime($a['created_at'])) ?>
                                        <?= !empty($a['expires_at']) ? '• Expires ' . date('M d', strtotime($a['expires_at'])) : '• Permanent' ?>
                                    </div>
                                </div>
                                <h3 class="font-bold text-sm text-slate-900 dark:text-white"><?= $e($a['title']) ?></h3>
                                <p class="text-xs text-slate-700 dark:text-slate-300 mt-1.5 leading-relaxed"><?= nl2br($e($a['content'])) ?></p>
                            </div>

                            <div class="flex items-center justify-between pt-2 border-t border-slate-200 dark:border-white/10 text-[10px] text-slate-500 dark:text-slate-400">
                                <span>Target: <?= strtoupper($e($a['target_role'])) ?></span>
                                <form method="POST" action="/admin/announcements/<?= $a['id'] ?>/delete" onsubmit="return confirm('Delete this announcement?');">
                                    <?= $csrf() ?>
                                    <button type="submit" class="text-rose-500 hover:text-rose-400 font-bold flex items-center gap-1 cursor-pointer">
                                        <i data-lucide="trash-2" class="w-3 h-3"></i>
                                        <span>Delete</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>

</div>
