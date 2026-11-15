<div class="space-y-6">

    <!-- Top Header & Exam Details -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="/results" class="text-xs text-slate-400 hover:text-indigo-500 flex items-center gap-1 transition-colors">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    <span>Exams</span>
                </a>
                <span class="text-xs text-slate-400">•</span>
                <span class="text-xs font-semibold text-indigo-500 uppercase"><?= $e($exam['class_name']) ?> - <?= $e($exam['class_section']) ?></span>
                <span class="text-xs text-slate-400">•</span>
                <span class="text-xs font-semibold text-violet-500"><?= $e($exam['subject_name']) ?></span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white mt-1">
                <?= $e($exam['title']) ?> (<?= $e($exam['term']) ?>)
            </h1>
        </div>

        <!-- Metric Badges & Actions -->
        <div class="flex flex-wrap items-center gap-2">
            <span class="px-3 py-1.5 rounded-xl bg-white dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 text-xs text-slate-700 dark:text-slate-300 shadow-sm">
                Max Marks: <strong class="text-indigo-600 dark:text-indigo-400 font-mono tabular-nums"><?= $exam['max_marks'] ?></strong>
            </span>
            <span class="px-3 py-1.5 rounded-xl bg-white dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 text-xs text-slate-700 dark:text-slate-300 shadow-sm">
                Pass Threshold: <strong class="text-emerald-600 dark:text-emerald-400 font-mono tabular-nums"><?= $exam['pass_marks'] ?></strong>
            </span>
            <span class="px-3 py-1.5 rounded-xl bg-white dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 text-xs text-slate-700 dark:text-slate-300 shadow-sm">
                Class Average: <strong class="font-mono text-slate-900 dark:text-white tabular-nums"><?= $analytics['avg_score'] ?? 0 ?></strong>
            </span>
            <a href="/results/print/exam/<?= $exam['id'] ?>" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-white dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 shadow-sm transition-all">
                <i data-lucide="printer" class="w-4 h-4 text-violet-500"></i>
                <span>Print Result Table</span>
            </a>
            <a href="/results/import?exam_id=<?= $exam['id'] ?>" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 shadow-sm transition-all">
                <i data-lucide="scan-line" class="w-4 h-4"></i>
                <span>Bulk Import / Scan</span>
            </a>
        </div>
    </div>

    <!-- Mark Entry Form -->
    <form method="POST" action="/results/enter/<?= $exam['id'] ?>" class="space-y-4">
        <?= $csrf() ?>

        <div class="glass-card overflow-hidden">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-950/80 border-b border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 uppercase font-semibold text-xs tracking-wider">
                    <tr>
                        <th class="py-3 px-4">Admin ID</th>
                        <th class="py-3 px-4">Student Name</th>
                        <th class="py-3 px-4 w-36">Marks (Max: <?= $exam['max_marks'] ?>)</th>
                        <th class="py-3 px-4">Percentage</th>
                        <th class="py-3 px-4">Letter Grade</th>
                        <th class="py-3 px-4">Teacher Remarks</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80" id="mark-entry-table-body">
                    <?php foreach ($students as $stu): ?>
                        <?php 
                            $mData = $stu['marks_data'];
                            $score = ($mData !== null) ? $mData['marks_obtained'] : '';
                            $pct = ($mData !== null) ? $mData['percentage'] . '%' : '-';
                            $grade = ($mData !== null) ? $mData['letter_grade'] : '-';
                            $badgeClass = ($mData !== null) ? \App\Utils\GradeCalculator::badgeClass($grade) : '';
                        ?>
                        <tr class="student-mark-row hover:bg-slate-50/60 dark:hover:bg-slate-800/60 transition-colors" data-student-id="<?= $stu['id'] ?>">
                            <td class="py-3 px-4 font-mono font-bold text-indigo-600 dark:text-indigo-400 tabular-nums">
                                <?= $e($stu['student_admin_id']) ?>
                            </td>
                            <td class="py-3 px-4 font-semibold text-slate-900 dark:text-white">
                                <a href="/students/<?= $stu['id'] ?>" target="_blank" class="hover:underline">
                                    <?= $e($stu['first_name'] . ' ' . $stu['last_name']) ?>
                                </a>
                            </td>
                            <td class="py-3 px-4">
                                <input type="number" 
                                       step="0.5" 
                                       min="0" 
                                       max="<?= $exam['max_marks'] ?>"
                                       name="marks[<?= $stu['id'] ?>][score]" 
                                       value="<?= $score ?>"
                                       placeholder="Score"
                                       class="mark-score-input w-28 px-3 py-1.5 text-xs rounded-lg glass-input font-mono font-bold text-slate-900 dark:text-white tabular-nums"
                                       oninput="calculateGrade(this, <?= $exam['max_marks'] ?>)">
                            </td>
                            <td class="py-3 px-4 font-bold text-indigo-600 dark:text-indigo-400 mark-pct-cell tabular-nums">
                                <?= $pct ?>
                            </td>
                            <td class="py-3 px-4">
                                <span class="mark-grade-badge font-bold px-2.5 py-0.5 rounded-md text-xs <?= $badgeClass ?>">
                                    <?= $grade ?>
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <input type="text" 
                                       name="marks[<?= $stu['id'] ?>][remarks]" 
                                       value="<?= $e($mData['remarks'] ?? '') ?>" 
                                       placeholder="Feedback"
                                       class="w-full px-3 py-1.5 text-xs rounded-lg glass-input">
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Sticky Save Action Bar -->
        <div class="sticky bottom-16 md:bottom-4 z-30 p-4 rounded-2xl bg-white/95 dark:bg-slate-900/95 border border-slate-200 dark:border-slate-800 shadow-xl flex items-center justify-between backdrop-blur-md">
            <div class="text-xs text-slate-500 dark:text-slate-400">
                Grading <strong class="text-slate-800 dark:text-slate-200"><?= count($students) ?> Students</strong> in <strong class="text-slate-800 dark:text-slate-200"><?= $e($exam['class_name'] . ' ' . $exam['class_section']) ?></strong>
            </div>
            <button type="submit" class="px-6 py-2.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 shadow-sm flex items-center gap-2 transition-all">
                <i data-lucide="save" class="w-4 h-4"></i>
                <span>Save Grades & Results</span>
            </button>
        </div>

    </form>

</div>

<script>
    function calculateGrade(input, maxMarks) {
        const row = input.closest('.student-mark-row');
        const pctCell = row.querySelector('.mark-pct-cell');
        const gradeBadge = row.querySelector('.mark-grade-badge');

        const val = parseFloat(input.value);
        if (isNaN(val) || val < 0) {
            pctCell.innerText = '-';
            gradeBadge.innerText = '-';
            gradeBadge.className = 'mark-grade-badge font-bold px-2.5 py-0.5 rounded-md text-xs';
            return;
        }

        const pct = Math.round((val / maxMarks) * 1000) / 10;
        pctCell.innerText = pct + '%';

        let grade = 'F';
        let badgeClass = 'badge-rose';

        if (pct >= 90) {
            grade = 'A+';
            badgeClass = 'badge-emerald';
        } else if (pct >= 80) {
            grade = 'A';
            badgeClass = 'badge-emerald';
        } else if (pct >= 70) {
            grade = 'B';
            badgeClass = 'badge-indigo';
        } else if (pct >= 60) {
            grade = 'C';
            badgeClass = 'badge-amber';
        } else if (pct >= 50) {
            grade = 'D';
            badgeClass = 'badge-amber';
        }

        gradeBadge.innerText = grade;
        gradeBadge.className = 'mark-grade-badge font-bold px-2.5 py-0.5 rounded-md text-xs ' + badgeClass;
    }
</script>
