<div class="space-y-6">

    <!-- Hero Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full <?= !empty($update['has_update']) ? 'bg-amber-500 animate-ping' : (!empty($update['error']) ? 'bg-rose-500' : 'bg-emerald-500') ?>"></span>
                <span class="text-xs font-mono font-bold uppercase tracking-wider <?= !empty($update['has_update']) ? 'text-amber-500' : (!empty($update['error']) ? 'text-rose-500' : 'text-emerald-500') ?>">
                    <?= !empty($update['has_update']) ? 'Update Available (' . ($update['commits_behind'] > 0 ? "{$update['commits_behind']} commits behind" : 'New Release') . ')' : (!empty($update['error']) ? 'Check Notice' : 'System Up to Date') ?>
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

    <!-- Pre-Flight System Health & Diagnostics Strip -->
    <div class="glass-card p-4 sm:p-5 border border-slate-200/80 dark:border-white/10">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100 dark:border-slate-800 text-xs">
            <div class="flex items-center gap-2">
                <i data-lucide="activity" class="w-4 h-4 text-indigo-500"></i>
                <span class="font-bold text-slate-900 dark:text-white uppercase tracking-wider text-[11px]">Pre-Flight System Health &amp; Environment</span>
            </div>
            <div class="flex items-center gap-2 text-slate-500 dark:text-slate-400 font-mono text-[11px]">
                <span>Storage: <?= $e($health['disk_space']) ?></span>
                <span>•</span>
                <span><?= $health['all_ok'] ? '<span class="text-emerald-500 font-bold">All Pre-flight Checks Passed</span>' : '<span class="text-rose-500 font-bold">Action Required</span>' ?></span>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2.5 pt-3">
            <!-- PHP Version -->
            <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-700/50 flex items-center justify-between">
                <div>
                    <div class="text-[10px] text-slate-400 uppercase font-bold">PHP Runtime</div>
                    <div class="font-mono font-bold text-xs text-slate-800 dark:text-slate-200"><?= PHP_VERSION ?></div>
                </div>
                <i data-lucide="<?= $health['php_ok'] ? 'check-circle-2' : 'alert-circle' ?>" class="w-4 h-4 <?= $health['php_ok'] ? 'text-emerald-500' : 'text-rose-500' ?>"></i>
            </div>

            <!-- ZipArchive -->
            <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-700/50 flex items-center justify-between">
                <div>
                    <div class="text-[10px] text-slate-400 uppercase font-bold">ZipArchive</div>
                    <div class="font-mono font-bold text-xs text-slate-800 dark:text-slate-200"><?= $health['zip_ok'] ? 'Available' : 'Missing' ?></div>
                </div>
                <i data-lucide="<?= $health['zip_ok'] ? 'check-circle-2' : 'alert-circle' ?>" class="w-4 h-4 <?= $health['zip_ok'] ? 'text-emerald-500' : 'text-rose-500' ?>"></i>
            </div>

            <!-- cURL & SSL -->
            <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-700/50 flex items-center justify-between">
                <div>
                    <div class="text-[10px] text-slate-400 uppercase font-bold">cURL / SSL</div>
                    <div class="font-mono font-bold text-xs text-slate-800 dark:text-slate-200"><?= ($health['curl_ok'] && $health['openssl_ok']) ? 'Operational' : 'Disabled' ?></div>
                </div>
                <i data-lucide="<?= ($health['curl_ok'] && $health['openssl_ok']) ? 'check-circle-2' : 'alert-circle' ?>" class="w-4 h-4 <?= ($health['curl_ok'] && $health['openssl_ok']) ? 'text-emerald-500' : 'text-rose-500' ?>"></i>
            </div>

            <!-- Write Permissions -->
            <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-700/50 flex items-center justify-between">
                <div>
                    <div class="text-[10px] text-slate-400 uppercase font-bold">Write Perms</div>
                    <div class="font-mono font-bold text-xs text-slate-800 dark:text-slate-200"><?= $health['all_writable'] ? 'Writable' : 'Locked' ?></div>
                </div>
                <i data-lucide="<?= $health['all_writable'] ? 'check-circle-2' : 'alert-circle' ?>" class="w-4 h-4 <?= $health['all_writable'] ? 'text-emerald-500' : 'text-rose-500' ?>"></i>
            </div>

            <!-- Local Git Status -->
            <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-700/50 flex items-center justify-between">
                <div>
                    <div class="text-[10px] text-slate-400 uppercase font-bold">Local Git</div>
                    <div class="font-mono font-bold text-xs text-slate-800 dark:text-slate-200"><?= $health['is_git'] ? 'Git Repo' : 'Standalone' ?></div>
                </div>
                <i data-lucide="git-commit" class="w-4 h-4 text-indigo-500"></i>
            </div>

            <!-- GitHub Rate Limit -->
            <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-700/50 flex items-center justify-between">
                <div>
                    <div class="text-[10px] text-slate-400 uppercase font-bold">API Limit</div>
                    <div class="font-mono font-bold text-xs text-slate-800 dark:text-slate-200">
                        <?= !empty($update['rate_limit']['remaining']) ? "{$update['rate_limit']['remaining']}/{$update['rate_limit']['limit']}" : ($tokenConfigured ? '5,000/hr' : '60/hr') ?>
                    </div>
                </div>
                <i data-lucide="gauge" class="w-4 h-4 text-indigo-500"></i>
            </div>
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
                            Commit: <span class="font-mono font-semibold text-slate-700 dark:text-slate-300"><?= $e($current['commit'] ?? 'HEAD') ?></span> • Released: <?= $e($current['release_date'] ?? date('Y-m-d')) ?>
                        </p>
                    </div>

                    <div class="text-left sm:text-right space-y-1">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Upstream on GitHub</span>
                        <div class="flex items-center sm:justify-end gap-2">
                            <span class="text-2xl font-black font-mono tracking-tight tabular-nums <?= !empty($update['has_update']) ? 'text-amber-500' : 'text-emerald-500' ?>">
                                <?= !empty($update['latest_commit']) ? ('commit ' . $e($update['latest_commit'])) : ('v' . $e($update['latest_version'] ?? $current['version'])) ?>
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Target: <a href="<?= $e($update['html_url'] ?? "https://github.com/{$repo}") ?>" target="_blank" class="text-indigo-500 hover:underline font-mono"><?= $e($repo) ?> (<?= $e($branch) ?>)</a>
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
                                        <?= $e($update['release_name'] ?? ('Update ' . $update['latest_version'])) ?>
                                        <?php if (!empty($update['commits_behind'])): ?>
                                            <span class="text-xs font-mono font-normal text-amber-600 dark:text-amber-300 ml-1.5">(<?= $update['commits_behind'] ?> new commit<?= $update['commits_behind'] > 1 ? 's' : '' ?> upstream)</span>
                                        <?php endif; ?>
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

                    <!-- Recent Incoming Commits List -->
                    <?php if (!empty($update['recent_commits'])): ?>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between text-xs">
                                <h4 class="font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                                    <i data-lucide="git-commit" class="w-3.5 h-3.5 text-indigo-500"></i>
                                    <span>Incoming Upstream Commits</span>
                                </h4>
                                <a href="https://github.com/<?= $e($repo) ?>/compare/<?= $e($current['commit']) ?>...<?= $e($update['latest_commit']) ?>" target="_blank" class="text-indigo-500 hover:underline">
                                    View Full GitHub Diff &rarr;
                                </a>
                            </div>
                            <div class="space-y-1.5">
                                <?php foreach ($update['recent_commits'] as $c): ?>
                                    <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
                                        <div class="flex items-center gap-2 min-w-0 flex-1">
                                            <span class="font-mono text-[11px] font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-500/10 px-1.5 py-0.5 rounded flex-shrink-0"><?= $e($c['sha']) ?></span>
                                            <span class="text-slate-800 dark:text-slate-200 truncate font-medium"><?= $e($c['message']) ?></span>
                                        </div>
                                        <span class="text-[11px] text-slate-400 font-mono flex-shrink-0 ml-2"><?= $e($c['author']) ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php elseif (!empty($update['release_notes'])): ?>
                        <!-- Release Notes Panel -->
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

                <?php elseif (!empty($update['error'])): ?>
                    <!-- Error / Notice Banner -->
                    <div class="p-5 rounded-2xl bg-amber-500/10 border border-amber-500/30 text-amber-900 dark:text-amber-200 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center flex-shrink-0">
                            <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h4 class="font-extrabold text-sm text-slate-900 dark:text-white">GitHub API Check Notice</h4>
                            <p class="text-xs text-slate-600 dark:text-slate-300 mt-0.5 leading-relaxed">
                                <?= $e($update['error']) ?>
                            </p>
                        </div>
                    </div>
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
                            Previous safety snapshots created before in-place updates. You can download, restore, or remove snapshots.
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
                                        <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400 hover:bg-amber-500/20 border border-amber-500/30 transition-colors cursor-pointer" title="Rollback to this snapshot">
                                            Restore
                                        </button>
                                    </form>
                                    <form method="POST" action="/admin/updates/backup/delete" onsubmit="return confirm('Permanently delete this backup archive from server?')">
                                        <?= $csrf() ?>
                                        <input type="hidden" name="filename" value="<?= $e($b['filename']) ?>">
                                        <button type="submit" class="p-2 rounded-lg text-rose-500 hover:bg-rose-500/10 transition-colors cursor-pointer" title="Delete snapshot">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
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
                        <div class="glass-input-group" onclick="this.querySelector('input')?.focus()">
                            <div class="input-icon-box">
                                <i data-lucide="github" class="w-4 h-4"></i>
                            </div>
                            <input type="text" name="github_repo" value="<?= $e($repo) ?>" required placeholder="e.g. Hasnain1385/Class_Aid" class="font-mono text-xs">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Branch</label>
                        <div class="glass-input-group" onclick="this.querySelector('input')?.focus()">
                            <div class="input-icon-box">
                                <i data-lucide="git-commit" class="w-4 h-4"></i>
                            </div>
                            <input type="text" name="github_branch" value="<?= $e($branch) ?>" required placeholder="main" class="font-mono text-xs">
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">GitHub Token (Personal Access)</label>
                            <?php if ($tokenConfigured): ?>
                                <span class="text-[10px] font-bold text-emerald-500 font-mono">Configured</span>
                            <?php endif; ?>
                        </div>
                        <div class="glass-input-group" onclick="this.querySelector('input')?.focus()">
                            <div class="input-icon-box">
                                <i data-lucide="key" class="w-4 h-4"></i>
                            </div>
                            <input type="password" name="github_token" placeholder="<?= $tokenConfigured ? '••••••••••••••••' : 'ghp_...' ?>" class="font-mono text-xs">
                        </div>
                        <input type="hidden" name="update_token" value="1">
                        <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-1">Recommended for unlimited API rate limits (5,000 req/hr vs 60 req/hr).</p>
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
                            <div class="truncate <?= str_contains($line, 'ERROR') ? 'text-rose-400 font-bold' : (str_contains($line, 'successfully') || str_contains($line, 'cleanly') ? 'text-emerald-400 font-bold' : 'text-slate-300') ?>">
                                <?= $e($line) ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

        </div>

    </div>

    <!-- In-Place Update Confirmation & Live Terminal Modal -->
    <div id="update-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-md animate-in fade-in duration-200">
        <div class="glass-card max-w-lg w-full p-6 sm:p-8 rounded-3xl border border-indigo-500/30 shadow-2xl relative space-y-5 bg-white dark:bg-slate-900 text-slate-900 dark:text-white">
            
            <div class="flex items-start justify-between pb-3 border-b border-slate-200 dark:border-white/10">
                <div class="flex items-center gap-2.5">
                    <div class="w-10 h-10 rounded-2xl bg-indigo-600 text-white flex items-center justify-center shadow-md shadow-indigo-600/25">
                        <i data-lucide="arrow-up-circle" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900 dark:text-white">In-Place Application Update</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Deploying to <?= $e($update['latest_commit'] ?? 'latest') ?></p>
                    </div>
                </div>
                <button type="button" onclick="closeUpdateModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-white p-1">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Update Safety Checklist -->
            <div id="modal-prep-view" class="space-y-4">
                <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                    You are about to perform an automated in-place software upgrade. The engine will automatically:
                </p>
                <div class="space-y-2 text-xs">
                    <div class="flex items-center gap-2 p-2 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/60 dark:border-white/5">
                        <i data-lucide="check" class="w-4 h-4 text-emerald-500 flex-shrink-0"></i>
                        <span>Create pre-update safety backup zip &amp; database snapshot</span>
                    </div>
                    <div class="flex items-center gap-2 p-2 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/60 dark:border-white/5">
                        <i data-lucide="check" class="w-4 h-4 text-emerald-500 flex-shrink-0"></i>
                        <span>Download and safely extract latest code from GitHub</span>
                    </div>
                    <div class="flex items-center gap-2 p-2 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/60 dark:border-white/5">
                        <i data-lucide="check" class="w-4 h-4 text-emerald-500 flex-shrink-0"></i>
                        <span>Preserve <strong class="font-mono">.env</strong>, SQLite database, and uploads</span>
                    </div>
                    <div class="flex items-center gap-2 p-2 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/60 dark:border-white/5">
                        <i data-lucide="check" class="w-4 h-4 text-emerald-500 flex-shrink-0"></i>
                        <span>Synchronize database schema and flush OPcache</span>
                    </div>
                </div>

                <div class="pt-2 flex items-center justify-end gap-3 border-t border-slate-200 dark:border-white/10">
                    <button type="button" onclick="closeUpdateModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800">
                        Cancel
                    </button>
                    <button type="button" onclick="executeInPlaceUpdate()" class="px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 shadow-lg shadow-indigo-600/30 transition-all cursor-pointer">
                        Confirm &amp; Start Update
                    </button>
                </div>
            </div>

            <!-- In-Progress Live Terminal View -->
            <div id="modal-progress-view" class="hidden space-y-4">
                <div class="flex items-center gap-3">
                    <div id="progress-spinner" class="liquid-spinner flex-shrink-0"></div>
                    <div>
                        <h4 id="progress-status-heading" class="text-xs font-bold text-slate-900 dark:text-white">Applying in-place update...</h4>
                        <p id="progress-status-sub" class="text-[11px] text-slate-500 dark:text-slate-400">Please do not close this browser window.</p>
                    </div>
                </div>

                <div id="progress-terminal" class="bg-slate-950 p-3.5 rounded-xl border border-slate-800 text-[11px] font-mono text-slate-300 max-h-56 overflow-y-auto space-y-1">
                    <div>[Init] Connecting to updater subsystem...</div>
                </div>

                <div id="progress-complete-actions" class="hidden pt-3 border-t border-slate-200 dark:border-white/10 text-right">
                    <button type="button" onclick="window.location.reload()" class="px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 transition-colors shadow-md shadow-emerald-600/25">
                        Reload Application &rarr;
                    </button>
                </div>
            </div>

        </div>
    </div>

</div>

<script>
    let logPollTimer = null;
    let seenLogLines = new Set();

    function openUpdateModal() {
        document.getElementById('update-modal').classList.remove('hidden');
        document.getElementById('modal-prep-view').classList.remove('hidden');
        document.getElementById('modal-progress-view').classList.add('hidden');
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    function closeUpdateModal() {
        if (logPollTimer) clearInterval(logPollTimer);
        document.getElementById('update-modal').classList.add('hidden');
    }

    function startLiveLogPolling(terminal) {
        if (logPollTimer) clearInterval(logPollTimer);
        seenLogLines.clear();

        logPollTimer = setInterval(async () => {
            try {
                const res = await fetch('/admin/updates/logs', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await res.json();
                if (data && data.lines && Array.isArray(data.lines)) {
                    data.lines.forEach(line => {
                        if (!seenLogLines.has(line)) {
                            seenLogLines.add(line);
                            const el = document.createElement('div');
                            el.className = line.includes('ERROR') ? 'text-rose-400 font-bold' : (line.includes('successfully') || line.includes('cleanly') ? 'text-emerald-400 font-bold' : 'text-slate-300');
                            el.textContent = line;
                            terminal.appendChild(el);
                            terminal.scrollTop = terminal.scrollHeight;
                        }
                    });
                }
            } catch (err) {}
        }, 800);
    }

    async function executeInPlaceUpdate() {
        const prepView = document.getElementById('modal-prep-view');
        const progressView = document.getElementById('modal-progress-view');
        const terminal = document.getElementById('progress-terminal');
        const heading = document.getElementById('progress-status-heading');
        const sub = document.getElementById('progress-status-sub');
        const spinner = document.getElementById('progress-spinner');
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

        addTerminalLine('Submitting in-place update execution request...');
        startLiveLogPolling(terminal);

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

            if (logPollTimer) clearInterval(logPollTimer);
            if (typeof PageLoader !== 'undefined') PageLoader.done();

            const data = await res.json();

            if (data.steps && Array.isArray(data.steps)) {
                data.steps.forEach(s => {
                    if (!seenLogLines.has(`[${s.time}] ${s.message}`)) {
                        addTerminalLine(s.message);
                    }
                });
            }

            if (data.success) {
                heading.textContent = 'Update Finished Successfully!';
                heading.className = 'text-xs font-bold text-emerald-500';
                sub.textContent = 'System is ready and operational.';
                spinner.className = 'hidden';
                addTerminalLine('Application is now running v' + (data.new_version || 'latest') + '. All services operational.');
                completeActions.classList.remove('hidden');
            } else {
                heading.textContent = 'Update Execution Failed';
                heading.className = 'text-xs font-bold text-rose-500';
                sub.textContent = 'See log output below for details.';
                spinner.className = 'hidden';
                addTerminalLine('Error: ' + (data.error || 'Update failed.'), true);
                if (data.rolled_back) {
                    addTerminalLine('Notice: Safe automatic rollback succeeded. System returned to previous stable version.');
                }
                completeActions.classList.remove('hidden');
            }
        } catch (err) {
            if (logPollTimer) clearInterval(logPollTimer);
            if (typeof PageLoader !== 'undefined') PageLoader.done();
            heading.textContent = 'Update Communication Error';
            heading.className = 'text-xs font-bold text-rose-500';
            spinner.className = 'hidden';
            addTerminalLine('Network error during update: ' + err.message, true);
            completeActions.classList.remove('hidden');
        }
    }
</script>
