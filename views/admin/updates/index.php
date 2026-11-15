<div class="space-y-6">

    <!-- Hero Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full <?= !empty($update['has_update']) ? 'bg-amber-500 animate-ping' : 'bg-emerald-500' ?>"></span>
                <span class="text-xs font-mono font-bold uppercase tracking-wider <?= !empty($update['has_update']) ? 'text-amber-500' : 'text-emerald-500' ?>">
                    <?= !empty($update['has_update']) ? 'Update Available' : 'System Up to Date' ?>
                </span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white mt-1 flex items-center gap-2.5">
                <i data-lucide="refresh-cw" class="w-7 h-7 text-indigo-500"></i>
                <span>System Updates & In-Place Engine</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1 max-w-2xl">
                Check GitHub releases, perform zero-downtime in-place updates, preserve local databases & configuration, and manage rollback snapshots.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <!-- Check for Updates Form -->
            <form method="POST" action="/admin/updates/check">
                <?= $csrf() ?>
                <button type="submit" id="btn-check-updates" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-200 bg-white/80 dark:bg-slate-900/80 border border-slate-200 dark:border-white/10 hover:border-indigo-500 hover:text-indigo-600 dark:hover:text-indigo-400 shadow-sm transition-all cursor-pointer">
                    <i data-lucide="search" class="w-4 h-4 text-indigo-500"></i>
                    <span>Check GitHub</span>
                </button>
            </form>

            <!-- Create Manual Snapshot Form -->
            <form method="POST" action="/admin/updates/backup">
                <?= $csrf() ?>
                <input type="hidden" name="note" value="Manual administrator snapshot">
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-200 bg-white/80 dark:bg-slate-900/80 border border-slate-200 dark:border-white/10 hover:border-emerald-500 hover:text-emerald-600 dark:hover:text-emerald-400 shadow-sm transition-all cursor-pointer">
                    <i data-lucide="archive" class="w-4 h-4 text-emerald-500"></i>
                    <span>Create Snapshot</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Main 2-Column Status Split -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Column 1 & 2: Version Status & In-Place Update Action -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Primary Version Card -->
            <div class="glass-card p-6 sm:p-7 space-y-6 relative overflow-hidden border <?= !empty($update['has_update']) ? 'border-amber-500/40 bg-amber-500/5' : 'border-slate-200 dark:border-white/10' ?>">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-5 border-b border-slate-200/80 dark:border-white/10">
                    <div class="space-y-1">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Current Installed Version</span>
                        <div class="flex items-center gap-3">
                            <span class="text-3xl font-extrabold text-slate-900 dark:text-white font-mono tracking-tight tabular-nums">
                                v<?= $e($current['version']) ?>
                            </span>
                            <span class="text-xs px-2.5 py-0.5 rounded-full font-bold uppercase badge-indigo">
                                <?= $e($current['channel'] ?? 'stable') ?>
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Build: <span class="font-mono"><?= $e($current['commit'] ?? 'HEAD') ?></span> • Released: <?= $e($current['release_date'] ?? date('Y-m-d')) ?>
                        </p>
                    </div>

                    <div class="text-left sm:text-right space-y-1">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Latest on GitHub</span>
                        <div class="flex items-center sm:justify-end gap-2">
                            <span class="text-2xl font-black font-mono tracking-tight tabular-nums <?= !empty($update['has_update']) ? 'text-amber-500' : 'text-emerald-500' ?>">
                                v<?= $e($update['latest_version'] ?? $current['version']) ?>
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Repo: <a href="<?= $e($update['html_url'] ?? "https://github.com/{$repo}") ?>" target="_blank" class="text-indigo-500 hover:underline font-mono"><?= $e($repo) ?></a>
                        </p>
                    </div>
                </div>

                <!-- Update Status Banner & Action -->
                <?php if (!empty($update['has_update'])): ?>
                    <div class="p-5 rounded-2xl bg-amber-500/10 border border-amber-500/30 text-amber-900 dark:text-amber-200 space-y-4">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="flex items-start gap-3">
                                <div class="p-2 rounded-xl bg-amber-500/20 text-amber-500 flex-shrink-0 mt-0.5">
                                    <i data-lucide="sparkles" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <h3 class="font-extrabold text-sm text-slate-900 dark:text-white">
                                        <?= $e($update['release_name'] ?? ('Release v' . $update['latest_version'])) ?>
                                    </h3>
                                    <p class="text-xs text-slate-600 dark:text-slate-300 mt-0.5">
                                        Published on <?= !empty($update['published_at']) ? date('M j, Y • h:i A', strtotime($update['published_at'])) : 'Recently' ?>. Ready for automatic in-place deployment.
                                    </p>
                                </div>
                            </div>

                            <button type="button" 
                                    onclick="openUpdateModal()" 
                                    class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl text-xs font-extrabold text-white bg-gradient-to-r from-amber-500 to-indigo-600 hover:opacity-95 shadow-lg shadow-indigo-500/25 transition-all cursor-pointer whitespace-nowrap">
                                <i data-lucide="arrow-up-circle" class="w-4 h-4"></i>
                                <span>Apply Update In-Place</span>
                            </button>
                        </div>

                        <!-- Protected Data Guarantee Strip -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 pt-3 border-t border-amber-500/20 text-xs">
                            <div class="flex items-center gap-1.5 text-slate-700 dark:text-slate-300 font-medium">
                                <i data-lucide="shield-check" class="w-4 h-4 text-emerald-500 flex-shrink-0"></i>
                                <span>Pre-update snapshot auto-created</span>
                            </div>
                            <div class="flex items-center gap-1.5 text-slate-700 dark:text-slate-300 font-medium">
                                <i data-lucide="lock" class="w-4 h-4 text-indigo-500 flex-shrink-0"></i>
                                <span>.env & database safely preserved</span>
                            </div>
                            <div class="flex items-center gap-1.5 text-slate-700 dark:text-slate-300 font-medium">
                                <i data-lucide="rotate-ccw" class="w-4 h-4 text-amber-500 flex-shrink-0"></i>
                                <span>1-click rollback supported</span>
                            </div>
                        </div>
                    </div>

                    <!-- Release Notes Panel -->
                    <?php if (!empty($update['release_notes'])): ?>
                        <div class="space-y-2">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                                <i data-lucide="file-text" class="w-3.5 h-3.5 text-indigo-500"></i>
                                <span>Release Notes & Changelog</span>
                            </h4>
                            <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-950/70 border border-slate-200 dark:border-white/5 text-xs text-slate-700 dark:text-slate-300 max-h-60 overflow-y-auto whitespace-pre-wrap font-sans leading-relaxed">
                                <?= $e($update['release_notes']) ?>
                            </div>
                        </div>
                    <?php endif; ?>

                <?php else: ?>
                    <div class="p-5 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-900 dark:text-emerald-200 flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center flex-shrink-0">
                                <i data-lucide="check-circle-2" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h4 class="font-extrabold text-sm text-slate-900 dark:text-white">Your installation is running the latest software</h4>
                                <p class="text-xs text-slate-600 dark:text-slate-300 mt-0.5">
                                    Last checked on <?= $e($update['checked_at'] ?? date('Y-m-d H:i:s')) ?> against branch <span class="font-mono"><?= $e($branch) ?></span>.
                                </p>
                            </div>
                        </div>
                        <a href="<?= $e($update['html_url'] ?? "https://github.com/{$repo}") ?>" target="_blank" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-white/80 dark:bg-slate-900 text-slate-700 dark:text-slate-300 hover:text-indigo-500 border border-slate-200 dark:border-white/10 transition-colors whitespace-nowrap">
                            View on GitHub &rarr;
                        </a>
                    </div>
                <?php endif; ?>

            </div>

            <!-- Manual ZIP Package Upload Card -->
            <div class="glass-card p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-sm text-slate-900 dark:text-white flex items-center gap-2">
                            <i data-lucide="upload-cloud" class="w-4 h-4 text-violet-500"></i>
                            <span>Manual Update Package Upload</span>
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            Upload a release <span class="font-mono">.zip</span> archive directly (ideal for air-gapped or offline servers).
                        </p>
                    </div>
                </div>

                <form method="POST" action="/admin/updates/upload" enctype="multipart/form-data" class="space-y-4" onsubmit="PageLoader.start()">
                    <?= $csrf() ?>
                    <div class="flex flex-col sm:flex-row items-center gap-3">
                        <input type="file" 
                               name="update_zip" 
                               accept=".zip" 
                               required 
                               class="w-full text-xs text-slate-500 dark:text-slate-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-500/10 file:text-indigo-600 dark:file:text-indigo-400 hover:file:bg-indigo-500/20 cursor-pointer">
                        <button type="submit" class="w-full sm:w-auto px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 shadow-sm transition-all whitespace-nowrap cursor-pointer">
                            Upload & Deploy
                        </button>
                    </div>
                </form>
            </div>

            <!-- Backup & Restore Points Card -->
            <div class="glass-card p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-sm text-slate-900 dark:text-white flex items-center gap-2">
                            <i data-lucide="history" class="w-4 h-4 text-emerald-500"></i>
                            <span>Snapshots & Rollback Points</span>
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            Previous safety snapshots created before in-place updates. You can download or rollback at any time.
                        </p>
                    </div>
                    <span class="text-xs font-mono font-bold px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">
                        <?= count($backups) ?> Available
                    </span>
                </div>

                <?php if (empty($backups)): ?>
                    <div class="p-6 text-center text-xs text-slate-500 dark:text-slate-400 bg-slate-50/50 dark:bg-slate-950/40 rounded-xl border border-slate-200/60 dark:border-white/5">
                        <i data-lucide="archive" class="w-8 h-8 mx-auto mb-2 opacity-40"></i>
                        No previous snapshots recorded yet. Snapshots are automatically generated before each update.
                    </div>
                <?php else: ?>
                    <div class="space-y-2.5 max-h-72 overflow-y-auto">
                        <?php foreach ($backups as $b): ?>
                            <div class="p-3.5 rounded-xl bg-slate-50/70 dark:bg-slate-950/50 border border-slate-200/80 dark:border-white/5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                                <div>
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="font-bold text-slate-900 dark:text-white font-mono"><?= $e($b['filename']) ?></span>
                                        <span class="text-[10px] px-2 py-0.2 rounded-full font-bold bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 font-mono">v<?= $e($b['version'] ?? '1.0.0') ?></span>
                                    </div>
                                    <p class="text-slate-500 dark:text-slate-400 mt-0.5">
                                        <?= $e($b['note'] ?? 'Snapshot') ?> • <?= date('M j, Y • h:i A', strtotime($b['created_at'])) ?> (<?= number_format(($b['size'] ?? 0) / 1024, 1) ?> KB)
                                    </p>
                                </div>

                                <div class="flex items-center gap-2 flex-shrink-0">
                                    <a href="/admin/updates/backup/download/<?= urlencode($b['filename']) ?>" class="p-2 rounded-lg bg-white dark:bg-slate-800 hover:text-indigo-500 border border-slate-200 dark:border-slate-700 transition-colors" title="Download Backup ZIP">
                                        <i data-lucide="download" class="w-4 h-4"></i>
                                    </a>
                                    <form method="POST" action="/admin/updates/restore" onsubmit="return confirm('Are you sure you want to rollback to this snapshot? Current files will be replaced.')">
                                        <?= $csrf() ?>
                                        <input type="hidden" name="filename" value="<?= $e($b['filename']) ?>">
                                        <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400 hover:bg-amber-500/20 border border-amber-500/30 transition-colors cursor-pointer">
                                            Restore
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

        </div>

        <!-- Column 3: GitHub Settings & Activity Log -->
        <div class="space-y-6">

            <!-- GitHub Repository Settings Card -->
            <div class="glass-card p-6 space-y-4">
                <div class="flex items-center gap-2 pb-3 border-b border-slate-200/80 dark:border-white/10">
                    <div class="w-8 h-8 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center flex-shrink-0">
                        <i data-lucide="git-branch" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-xs uppercase tracking-wider text-slate-900 dark:text-white">Repository Settings</h3>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">Target upstream GitHub release origin</p>
                    </div>
                </div>

                <form method="POST" action="/admin/updates/settings" class="space-y-3.5">
                    <?= $csrf() ?>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">GitHub Repository (owner/repo)</label>
                        <input type="text" name="github_repo" value="<?= $e($repo) ?>" required placeholder="e.g. class-aid/class-aid" class="w-full px-3 py-2 text-xs rounded-xl glass-input font-mono">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Branch</label>
                        <input type="text" name="github_branch" value="<?= $e($branch) ?>" required placeholder="main" class="w-full px-3 py-2 text-xs rounded-xl glass-input font-mono">
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">GitHub API Token (Optional)</label>
                            <?php if ($tokenConfigured): ?>
                                <span class="text-[10px] font-bold text-emerald-500 font-mono">Configured</span>
                            <?php endif; ?>
                        </div>
                        <input type="password" name="github_token" placeholder="<?= $tokenConfigured ? '••••••••••••••••' : 'ghp_...' ?>" class="w-full px-3 py-2 text-xs rounded-xl glass-input font-mono">
                        <input type="hidden" name="update_token" value="1">
                        <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-1">Recommended to prevent GitHub API rate limits (60 req/hr).</p>
                    </div>

                    <button type="submit" class="w-full py-2.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 transition-colors cursor-pointer shadow-sm">
                        Save Updater Settings
                    </button>
                </form>
            </div>

            <!-- Recent Update Activity Log Card -->
            <div class="glass-card p-6 space-y-3">
                <div class="flex items-center justify-between pb-2 border-b border-slate-200/80 dark:border-white/10">
                    <h3 class="font-bold text-xs uppercase tracking-wider text-slate-900 dark:text-white flex items-center gap-1.5">
                        <i data-lucide="terminal" class="w-3.5 h-3.5 text-indigo-500"></i>
                        <span>Recent Engine Activity</span>
                    </h3>
                    <span class="text-[10px] font-mono text-slate-400">updates.log</span>
                </div>

                <?php if (empty($logLines)): ?>
                    <p class="text-xs text-slate-500 dark:text-slate-400 italic py-3 text-center">No update operations logged yet.</p>
                <?php else: ?>
                    <div class="bg-slate-950 p-3 rounded-xl border border-slate-800 text-[11px] font-mono text-slate-300 max-h-64 overflow-y-auto space-y-1">
                        <?php foreach ($logLines as $line): ?>
                            <div class="truncate <?= str_contains($line, 'ERROR') ? 'text-rose-400 font-bold' : (str_contains($line, 'successfully') ? 'text-emerald-400' : 'text-slate-300') ?>">
                                <?= $e($line) ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

        </div>

    </div>

    <!-- In-Place Update Confirmation & Progress Modal -->
    <div id="update-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-md animate-in fade-in duration-200">
        <div class="glass-card max-w-lg w-full p-6 sm:p-8 rounded-3xl border border-indigo-500/30 shadow-2xl relative space-y-5 bg-white dark:bg-slate-900 text-slate-900 dark:text-white">
            
            <div class="flex items-start justify-between pb-3 border-b border-slate-200 dark:border-white/10">
                <div class="flex items-center gap-2.5">
                    <div class="w-10 h-10 rounded-2xl bg-indigo-600 text-white flex items-center justify-center shadow-md">
                        <i data-lucide="arrow-up-circle" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900 dark:text-white">In-Place Application Update</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Upgrade to v<?= $e($update['latest_version'] ?? 'latest') ?></p>
                    </div>
                </div>
                <button type="button" onclick="closeUpdateModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-white p-1">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Update Safety Checklist -->
            <div id="modal-prep-view" class="space-y-4">
                <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                    You are about to perform an in-place software upgrade. The engine will automatically:
                </p>
                <div class="space-y-2 text-xs">
                    <div class="flex items-center gap-2 p-2 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/60 dark:border-white/5">
                        <i data-lucide="check" class="w-4 h-4 text-emerald-500 flex-shrink-0"></i>
                        <span>Generate a safety snapshot zip & database backup</span>
                    </div>
                    <div class="flex items-center gap-2 p-2 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/60 dark:border-white/5">
                        <i data-lucide="check" class="w-4 h-4 text-emerald-500 flex-shrink-0"></i>
                        <span>Download and extract the latest code from GitHub</span>
                    </div>
                    <div class="flex items-center gap-2 p-2 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/60 dark:border-white/5">
                        <i data-lucide="check" class="w-4 h-4 text-emerald-500 flex-shrink-0"></i>
                        <span>Safely preserve <strong class="font-mono">.env</strong>, SQLite database, and user uploads</span>
                    </div>
                    <div class="flex items-center gap-2 p-2 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/60 dark:border-white/5">
                        <i data-lucide="check" class="w-4 h-4 text-emerald-500 flex-shrink-0"></i>
                        <span>Synchronize any new database schema tables</span>
                    </div>
                </div>

                <div class="pt-2 flex items-center justify-end gap-3 border-t border-slate-200 dark:border-white/10">
                    <button type="button" onclick="closeUpdateModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800">
                        Cancel
                    </button>
                    <button type="button" onclick="executeInPlaceUpdate()" class="px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 shadow-lg shadow-indigo-600/30 transition-all cursor-pointer">
                        Confirm & Start Update
                    </button>
                </div>
            </div>

            <!-- In-Progress Live Terminal View -->
            <div id="modal-progress-view" class="hidden space-y-4">
                <div class="flex items-center gap-3">
                    <div class="liquid-spinner flex-shrink-0"></div>
                    <div>
                        <h4 id="progress-status-heading" class="text-xs font-bold text-slate-900 dark:text-white">Applying in-place update...</h4>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">Please do not close this browser window.</p>
                    </div>
                </div>

                <div id="progress-terminal" class="bg-slate-950 p-3.5 rounded-xl border border-slate-800 text-[11px] font-mono text-slate-300 max-h-52 overflow-y-auto space-y-1">
                    <div>[Init] Connecting to updater subsystem...</div>
                </div>

                <div id="progress-complete-actions" class="hidden pt-3 border-t border-slate-200 dark:border-white/10 text-right">
                    <button type="button" onclick="window.location.reload()" class="px-5 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 transition-colors">
                        Refresh Application
                    </button>
                </div>
            </div>

        </div>
    </div>

</div>

<script>
    function openUpdateModal() {
        document.getElementById('update-modal').classList.remove('hidden');
        document.getElementById('modal-prep-view').classList.remove('hidden');
        document.getElementById('modal-progress-view').classList.add('hidden');
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    function closeUpdateModal() {
        document.getElementById('update-modal').classList.add('hidden');
    }

    async function executeInPlaceUpdate() {
        const prepView = document.getElementById('modal-prep-view');
        const progressView = document.getElementById('modal-progress-view');
        const terminal = document.getElementById('progress-terminal');
        const heading = document.getElementById('progress-status-heading');
        const completeActions = document.getElementById('progress-complete-actions');

        prepView.classList.add('hidden');
        progressView.classList.remove('hidden');
        if (typeof PageLoader !== 'undefined') PageLoader.start();

        const addTerminalLine = (msg, isError = false) => {
            const line = document.createElement('div');
            line.className = isError ? 'text-rose-400 font-bold' : 'text-slate-300';
            line.textContent = `[${new Date().toLocaleTimeString()}] ${msg}`;
            terminal.appendChild(line);
            terminal.scrollTop = terminal.scrollHeight;
        };

        addTerminalLine('Submitting update request...');

        try {
            const csrfToken = document.querySelector('input[name="_csrf_token"]')?.value || '';
            const formData = new FormData();
            formData.append('_csrf_token', csrfToken);

            const res = await fetch('/admin/updates/apply', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            });

            const data = await res.json();
            if (typeof PageLoader !== 'undefined') PageLoader.done();

            if (data.steps && Array.isArray(data.steps)) {
                data.steps.forEach(s => addTerminalLine(s.message));
            }

            if (data.success) {
                heading.textContent = 'Update Finished Successfully!';
                heading.className = 'text-xs font-bold text-emerald-500';
                addTerminalLine('Application is now running v' + (data.new_version || 'latest') + '. All services operational.');
                completeActions.classList.remove('hidden');
            } else {
                heading.textContent = 'Update Failed';
                heading.className = 'text-xs font-bold text-rose-500';
                addTerminalLine('Error: ' + (data.error || 'Update failed.'), true);
                if (data.rolled_back) {
                    addTerminalLine('Notice: Safe automatic rollback succeeded. System returned to previous stable version.');
                }
                completeActions.classList.remove('hidden');
            }
        } catch (err) {
            if (typeof PageLoader !== 'undefined') PageLoader.done();
            heading.textContent = 'Update Communication Error';
            heading.className = 'text-xs font-bold text-rose-500';
            addTerminalLine('Network error during update: ' + err.message, true);
            completeActions.classList.remove('hidden');
        }
    }
</script>
