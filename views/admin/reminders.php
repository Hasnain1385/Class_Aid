<div class="max-w-3xl mx-auto space-y-6">

    <!-- Top Action Bar -->
    <div class="flex items-center gap-2">
        <a href="/admin" class="text-xs text-slate-500 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 flex items-center gap-1 transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Command Center</span>
        </a>
    </div>

    <div class="glass-card p-6 sm:p-8">
        <div class="flex items-center gap-3 pb-4 border-b border-slate-200 dark:border-white/10 mb-6">
            <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center flex-shrink-0">
                <i data-lucide="bell-ring" class="w-5 h-5"></i>
            </div>
            <div>
                <h1 class="text-xl font-black text-slate-900 dark:text-white">
                    Send Reminder Broadcasts to Teachers
                </h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Inject urgent task alerts, renewal deadlines, or grading reminders directly into teacher dashboards.
                </p>
            </div>
        </div>

        <form method="POST" action="/admin/reminders/broadcast" class="space-y-4">
            <?= $csrf() ?>

            <!-- Target Selection -->
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                    Target Recipient(s) *
                </label>
                <select name="target" class="w-full px-3.5 py-2.5 text-xs rounded-xl glass-input">
                    <option value="all">Broadcast to All Active Teachers (<?= count($teachers) ?> Accounts)</option>
                    <optgroup label="Direct Message Specific Teacher">
                        <?php foreach ($teachers as $t): ?>
                            <option value="<?= $t['id'] ?>"><?= $e($t['name']) ?> (<?= $e($t['school_name'] ?? 'Class Aid') ?> • <?= $e($t['email']) ?>)</option>
                        <?php endforeach; ?>
                    </optgroup>
                </select>
            </div>

            <!-- Title -->
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                    Reminder Title *
                </label>
                <input type="text" name="title" required placeholder="e.g. Urgent: Academic Year Marks Submission Due Friday" class="w-full px-3.5 py-2.5 text-xs rounded-xl glass-input">
            </div>

            <!-- Description -->
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                    Detailed Notes / Instructions
                </label>
                <textarea name="description" rows="3" placeholder="Additional context, links, or instructions..." class="w-full px-3.5 py-2.5 text-xs rounded-xl glass-input"></textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Due Date *
                    </label>
                    <input type="date" name="due_date" value="<?= date('Y-m-d', strtotime('+3 days')) ?>" required class="w-full px-3.5 py-2.5 text-xs rounded-xl glass-input">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Priority Level
                    </label>
                    <select name="priority" class="w-full px-3.5 py-2.5 text-xs rounded-xl glass-input">
                        <option value="high">High Priority (Urgent)</option>
                        <option value="medium" selected>Medium Priority</option>
                        <option value="low">Low Priority</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Category
                    </label>
                    <select name="category" class="w-full px-3.5 py-2.5 text-xs rounded-xl glass-input">
                        <option value="subscription">Subscription / Renewal</option>
                        <option value="exam">Exam Marks / Grading</option>
                        <option value="attendance">Roll Call / Truancy</option>
                        <option value="call">Parent Communication</option>
                        <option value="general" selected>General Task</option>
                    </select>
                </div>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-200 dark:border-white/10">
                <a href="/admin" class="px-4 py-2 text-xs font-semibold text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors">
                    Cancel
                </a>
                <button type="submit" class="px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-amber-600 hover:bg-amber-500 shadow-sm flex items-center gap-2 transition-all cursor-pointer">
                    <i data-lucide="send" class="w-4 h-4"></i>
                    <span>Broadcast Reminder</span>
                </button>
            </div>

        </form>
    </div>

</div>
