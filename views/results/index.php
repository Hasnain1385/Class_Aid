<div class="space-y-6">

    <!-- Header & Action Ribbon -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white flex items-center gap-2">
                <i data-lucide="award" class="w-6 h-6 text-violet-500"></i>
                <span>Examinations & Gradebook</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                Define exams, enter student numerical scores, auto-compute letter grades (A+ to F), and print report cards.
            </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <a href="/results/import" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold bg-white dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 shadow-sm transition-all">
                <i data-lucide="scan-line" class="w-4 h-4 text-violet-500"></i>
                <span>Bulk Import Marks (CSV / OCR)</span>
            </a>
            <button type="button" 
                    onclick="document.getElementById('modal-create-exam').classList.remove('hidden')"
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-500 shadow-sm transition-all cursor-pointer">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Define New Exam</span>
            </button>
        </div>
    </div>

    <!-- Exams Grid / Table -->
    <?php if (empty($exams)): ?>
        <div class="glass-card p-12 text-center text-slate-500 dark:text-slate-400">
            <i data-lucide="award" class="w-12 h-12 mx-auto mb-3 opacity-40"></i>
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">No examinations defined yet</h3>
            <p class="text-xs mt-1">Create an assessment or quiz to begin entering marks and calculating grades.</p>
            <button type="button" 
                    onclick="document.getElementById('modal-create-exam').classList.remove('hidden')"
                    class="mt-4 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-semibold shadow-sm transition-all">
                Create First Exam
            </button>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php foreach ($exams as $ex): ?>
                <?php 
                    $passRate = ($ex['graded_count'] > 0) ? round(($ex['pass_count'] / $ex['graded_count']) * 100, 1) : 0;
                ?>
                <div class="glass-card p-5 space-y-4 hover:border-slate-300 dark:hover:border-slate-700 transition-all">
                    
                    <div class="flex items-start justify-between">
                        <div>
                            <span class="text-xs px-2.5 py-0.5 rounded-full bg-indigo-500/15 text-indigo-600 dark:text-indigo-400 font-bold uppercase border border-indigo-500/30">
                                <?= $e($ex['term']) ?>
                            </span>
                            <h2 class="text-base font-bold text-slate-900 dark:text-white mt-1.5">
                                <?= $e($ex['title']) ?>
                            </h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                <?= $e($ex['subject_name']) ?> • <?= $e($ex['class_name'] . ' ' . $ex['class_section']) ?>
                            </p>
                        </div>
                        <div class="text-right text-xs">
                            <span class="font-mono text-slate-400 block tabular-nums"><?= date('M d, Y', strtotime($ex['exam_date'])) ?></span>
                            <span class="font-semibold text-slate-700 dark:text-slate-300 tabular-nums">Max: <?= $ex['max_marks'] ?> pts</span>
                        </div>
                    </div>

                    <!-- Performance Mini-Metrics -->
                    <div class="grid grid-cols-2 gap-2 bg-slate-50 dark:bg-slate-950/60 p-3 rounded-xl text-xs border border-slate-100 dark:border-slate-800">
                        <div>
                            <span class="text-slate-400 block text-xs uppercase font-semibold">Graded Students</span>
                            <span class="font-bold text-slate-900 dark:text-white tabular-nums"><?= $ex['graded_count'] ?></span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-xs uppercase font-semibold">Pass Rate</span>
                            <span class="font-bold tabular-nums <?= $passRate >= 70 ? 'text-emerald-500' : 'text-amber-500' ?>"><?= $passRate ?>%</span>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                        <span class="text-xs text-slate-500 dark:text-slate-400 tabular-nums">Pass mark: <?= $ex['pass_marks'] ?></span>
                        <div class="flex items-center gap-1.5">
                            <a href="/results/print/exam/<?= $ex['id'] ?>" target="_blank" class="p-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors" title="Print Examination Results Table">
                                <i data-lucide="printer" class="w-4 h-4 text-violet-500"></i>
                            </a>
                            <a href="/results/enter/<?= $ex['id'] ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-500 shadow-sm transition-all">
                                <i data-lucide="edit" class="w-4 h-4"></i>
                                <span>Enter / Edit Marks</span>
                            </a>
                        </div>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<!-- Modal: Define New Exam -->
<div id="modal-create-exam" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
    <div class="glass-card w-full max-w-lg p-6 sm:p-8 space-y-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xl">
        <div class="flex items-center justify-between">
            <h3 class="text-base font-bold text-slate-900 dark:text-white">Define New Assessment / Exam</h3>
            <button type="button" onclick="document.getElementById('modal-create-exam').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form method="POST" action="/results/exams" class="space-y-4">
            <?= $csrf() ?>

            <!-- Target Subject Dropdown -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Select Subject & Class *</label>
                <select name="subject_id" required class="w-full px-3.5 py-2.5 text-xs sm:text-sm rounded-xl glass-input">
                    <option value="">-- Choose Subject --</option>
                    <?php foreach ($classes as $c): ?>
                        <optgroup label="<?= $e($c['name'] . ' ' . $c['section']) ?>">
                            <?php foreach (($classSubjects[$c['id']] ?? []) as $sub): ?>
                                <option value="<?= $sub['id'] ?>">
                                    <?= $e($sub['name']) ?> (<?= $e($c['name'] . ' ' . $c['section']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Exam Title *</label>
                    <input type="text" name="title" required placeholder="e.g. Mid-Term Assessment" class="w-full px-3.5 py-2.5 text-xs rounded-xl glass-input">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Term / Semester</label>
                    <input type="text" name="term" value="<?= 'Term ' . (date('n') >= 7 ? '1 (Fall ' . date('Y') . ')' : '2 (Spring ' . date('Y') . ')') ?>" class="w-full px-3.5 py-2.5 text-xs rounded-xl glass-input">
                </div>
            </div>

            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Max Marks *</label>
                    <input type="number" step="1" name="max_marks" value="100" required class="w-full px-3.5 py-2.5 text-xs rounded-xl glass-input font-mono tabular-nums">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Pass Marks *</label>
                    <input type="number" step="1" name="pass_marks" value="40" required class="w-full px-3.5 py-2.5 text-xs rounded-xl glass-input font-mono tabular-nums">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Exam Date</label>
                    <input type="date" name="exam_date" value="<?= date('Y-m-d') ?>" class="w-full px-3.5 py-2.5 text-xs rounded-xl glass-input tabular-nums">
                </div>
            </div>

            <div class="pt-3 flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('modal-create-exam').classList.add('hidden')" class="px-4 py-2 text-xs rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 rounded-xl shadow-sm transition-all">
                    Create Exam & Enter Marks
                </button>
            </div>
        </form>
    </div>
</div>
