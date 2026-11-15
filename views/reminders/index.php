<div class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="/dashboard" class="text-xs text-gray-400 hover:text-indigo-500 flex items-center gap-1">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    <span>Dashboard</span>
                </a>
            </div>
            <h1 class="text-2xl font-black tracking-tight text-gray-900 dark:text-white mt-1">
                Reminders & Academic Deadlines
            </h1>
            <p class="text-xs text-gray-500 dark:text-gray-400">
                Track parent follow-up calls, roll call reconciliations, grading milestones, and institutional reminders.
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left Column: Add Reminder Card -->
        <div class="glass-card p-6">
            <h2 class="text-xs font-bold uppercase tracking-wider text-indigo-500 dark:text-indigo-400 mb-4 flex items-center gap-2">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                <span>Create New Reminder</span>
            </h2>

            <form method="POST" action="/reminders" class="space-y-4">
                <?= $csrf() ?>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1">
                        Task Title *
                    </label>
                    <div class="glass-input-group" onclick="this.querySelector('input')?.focus()">
                        <div class="input-icon-box">
                            <i data-lucide="bell" class="w-4 h-4"></i>
                        </div>
                        <input type="text" name="title" required placeholder="e.g. Call Liam's guardian">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1">
                        Category
                    </label>
                    <div class="glass-input-group" onclick="this.querySelector('select')?.focus()">
                        <div class="input-icon-box">
                            <i data-lucide="folder" class="w-4 h-4"></i>
                        </div>
                        <select name="category">
                            <option value="call">Parent Communication</option>
                            <option value="attendance">Roll Call & Truancy</option>
                            <option value="exam">Marks & Exams</option>
                            <option value="subscription">Subscription / Renewal</option>
                            <option value="general" selected>General Task</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1">
                            Due Date *
                        </label>
                        <div class="glass-input-group" onclick="this.querySelector('input')?.focus()">
                            <div class="input-icon-box">
                                <i data-lucide="calendar" class="w-4 h-4"></i>
                            </div>
                            <input type="date" name="due_date" value="<?= date('Y-m-d') ?>" required class="tabular-nums">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1">
                            Time (Optional)
                        </label>
                        <div class="glass-input-group" onclick="this.querySelector('input')?.focus()">
                            <div class="input-icon-box">
                                <i data-lucide="clock" class="w-4 h-4"></i>
                            </div>
                            <input type="time" name="due_time" value="<?= date('H:i', strtotime('+1 hour')) ?>" class="tabular-nums">
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1">
                        Priority Level
                    </label>
                    <div class="glass-input-group" onclick="this.querySelector('select')?.focus()">
                        <div class="input-icon-box">
                            <i data-lucide="flag" class="w-4 h-4"></i>
                        </div>
                        <select name="priority">
                            <option value="high">High Priority</option>
                            <option value="medium" selected>Medium Priority</option>
                            <option value="low">Low Priority</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1">
                        Notes / Context
                    </label>
                    <textarea name="description" rows="3" placeholder="Context, guardian phone, or specific instructions..." class="w-full px-3 py-2 text-xs rounded-xl glass-input text-gray-900 dark:text-white"></textarea>
                </div>

                <button type="submit" class="w-full py-2.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 shadow-lg shadow-indigo-600/30 flex items-center justify-center gap-2 transition-all">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Save Reminder</span>
                </button>
            </form>
        </div>

        <!-- Right Column: Reminders List -->
        <div class="lg:col-span-2 space-y-4">
            
            <div class="glass-card p-6">
                <div class="flex items-center justify-between pb-4 border-b border-gray-200/50 dark:border-white/10 mb-4">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 flex items-center gap-2">
                        <i data-lucide="list-todo" class="w-4 h-4 text-indigo-500"></i>
                        <span>Your Academic Task List (<?= count($reminders) ?>)</span>
                    </h2>
                </div>

                <?php if (empty($reminders)): ?>
                    <div class="text-center py-10 text-gray-400 text-xs">
                        <i data-lucide="check-check" class="w-10 h-10 text-emerald-500 mx-auto mb-2 opacity-50"></i>
                        All caught up! No reminders or deadlines scheduled.
                    </div>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach ($reminders as $r): ?>
                            <?php 
                                $isCompleted = ($r['status'] === 'completed');
                                $isDue = (!$isCompleted && strtotime($r['due_date']) <= time());
                                $catInfo = $categories[$r['category']] ?? $categories['general'];
                            ?>
                            <div class="p-4 rounded-xl glass-card border transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-3 <?= $isCompleted ? 'opacity-50 line-through' : ($isDue ? 'border-amber-500/40 bg-amber-500/5' : '') ?>">
                                
                                <div class="flex items-start gap-3 flex-1">
                                    <!-- Toggle Complete Form -->
                                    <form method="POST" action="/reminders/<?= $r['id'] ?>/toggle" class="mt-0.5">
                                        <?= $csrf() ?>
                                        <button type="submit" class="w-5 h-5 rounded-md border flex items-center justify-center transition-colors <?= $isCompleted ? 'bg-emerald-500 border-emerald-500 text-white' : 'border-gray-300 dark:border-gray-600 hover:border-indigo-500' ?>">
                                            <?php if ($isCompleted): ?>
                                                <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                            <?php endif; ?>
                                        </button>
                                    </form>

                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="font-bold text-sm text-gray-900 dark:text-white <?= $isCompleted ? 'line-through text-gray-500' : '' ?>">
                                                <?= $e($r['title']) ?>
                                            </span>

                                            <span class="text-[10px] px-2 py-0.5 rounded-full font-bold uppercase <?= $catInfo['badge'] ?>">
                                                <?= $e($catInfo['label']) ?>
                                            </span>

                                            <?php if ($r['priority'] === 'high'): ?>
                                                <span class="text-[10px] px-1.5 py-0.2 rounded font-black uppercase bg-rose-500/15 text-rose-600 dark:text-rose-400">
                                                    High
                                                </span>
                                            <?php endif; ?>
                                        </div>

                                        <?php if (!empty($r['description'])): ?>
                                            <p class="text-xs text-gray-600 dark:text-gray-400 mt-1"><?= nl2br($e($r['description'])) ?></p>
                                        <?php endif; ?>

                                        <div class="flex items-center gap-3 text-[11px] text-gray-500 dark:text-gray-400 mt-2 font-mono">
                                            <span class="flex items-center gap-1">
                                                <i data-lucide="calendar" class="w-3 h-3"></i>
                                                <span><?= date('M d, Y', strtotime($r['due_date'])) ?></span>
                                            </span>
                                            <?php if (!empty($r['due_time'])): ?>
                                                <span>•</span>
                                                <span class="flex items-center gap-1">
                                                    <i data-lucide="clock" class="w-3 h-3"></i>
                                                    <span><?= $e($r['due_time']) ?></span>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Delete Action -->
                                <div class="flex items-center gap-2 self-end sm:self-center">
                                    <form method="POST" action="/reminders/<?= $r['id'] ?>/delete" onsubmit="return confirm('Delete this reminder?');">
                                        <?= $csrf() ?>
                                        <button type="submit" class="p-1.5 rounded-lg hover:bg-rose-500/10 text-gray-400 hover:text-rose-500 transition-colors" title="Delete Reminder">
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

    </div>

</div>
