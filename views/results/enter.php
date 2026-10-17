<div class="space-y-6">

    <!-- Top Header & Exam Details -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="/results" class="text-xs text-gray-400 hover:text-indigo-500 flex items-center gap-1">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    <span>Exams</span>
                </a>
                <span class="text-xs text-gray-400">•</span>
                <span class="text-xs font-semibold text-indigo-500 uppercase"><?= $e($exam['class_name']) ?> - <?= $e($exam['class_section']) ?></span>
                <span class="text-xs text-gray-400">•</span>
                <span class="text-xs font-semibold text-violet-500"><?= $e($exam['subject_name']) ?></span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white mt-1">
                <?= $e($exam['title']) ?> (<?= $e($exam['term']) ?>)
            </h1>
        </div>

        <!-- Metric Badges -->
        <div class="flex flex-wrap items-center gap-2">
            <span class="px-3 py-1.5 rounded-xl glass-card text-xs text-gray-700 dark:text-gray-300">
                Max Marks: <strong class="text-indigo-600 dark:text-indigo-400 font-mono"><?= $exam['max_marks'] ?></strong>
            </span>
            <span class="px-3 py-1.5 rounded-xl glass-card text-xs text-gray-700 dark:text-gray-300">
                Pass Threshold: <strong class="text-emerald-600 dark:text-emerald-400 font-mono"><?= $exam['pass_marks'] ?></strong>
            </span>
            <span class="px-3 py-1.5 rounded-xl glass-card text-xs text-gray-700 dark:text-gray-300">
                Class Average: <strong class="font-mono text-gray-900 dark:text-white"><?= $analytics['avg_score'] ?? 0 ?></strong>
            </span>
        </div>
    </div>

    <!-- Mark Entry Form -->
    <form method="POST" action="/results/enter/<?= $exam['id'] ?>" class="space-y-4">
        <?= $csrf() ?>

        <div class="glass-card overflow-hidden">
            <table class="w-full text-left text-xs">
                <thead class="bg-gray-50/50 dark:bg-white/5 border-b border-gray-200/50 dark:border-white/10 text-gray-500 uppercase font-semibold">
                    <tr>
                        <th class="py-3 px-4">Admin ID</th>
                        <th class="py-3 px-4">Student Name</th>
                        <th class="py-3 px-4 w-36">Marks (Max: <?= $exam['max_marks'] ?>)</th>
                        <th class="py-3 px-4">Percentage</th>
                        <th class="py-3 px-4">Letter Grade</th>
                        <th class="py-3 px-4">Teacher Remarks</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5" id="mark-entry-table-body">
                    <?php foreach ($students as $stu): ?>
                        <?php 
                            $mData = $stu['marks_data'];
                            $score = ($mData !== null) ? $mData['marks_obtained'] : '';
                            $pct = ($mData !== null) ? $mData['percentage'] . '%' : '-';
                            $grade = ($mData !== null) ? $mData['letter_grade'] : '-';
                            $badgeClass = ($mData !== null) ? \App\Utils\GradeCalculator::badgeClass($grade) : '';
                        ?>
                        <tr class="student-mark-row hover:bg-indigo-500/5 transition-colors" data-student-id="<?= $stu['id'] ?>">
                            <td class="py-3 px-4 font-mono font-bold text-indigo-500">
                                <?= $e($stu['student_admin_id']) ?>
                            </td>
                            <td class="py-3 px-4 font-semibold text-gray-900 dark:text-white">
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
                                       class="mark-score-input w-28 px-3 py-1.5 text-xs rounded-lg glass-input font-mono font-bold text-gray-900 dark:text-white"
                                       oninput="calculateGrade(this, <?= $exam['max_marks'] ?>)">
                            </td>
                            <td class="py-3 px-4 font-bold text-indigo-600 dark:text-indigo-400 mark-pct-cell">
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
        <div class="sticky bottom-16 md:bottom-4 z-30 p-4 rounded-2xl glass-card backdrop-blur-2xl shadow-2xl flex items-center justify-between">
            <div class="text-xs text-gray-500 dark:text-gray-400">
                Grading <strong><?= count($students) ?> Students</strong> in <strong><?= $e($exam['class_name'] . ' ' . $exam['class_section']) ?></strong>
            </div>
            <button type="submit" class="px-6 py-2.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 shadow-lg shadow-indigo-600/30 flex items-center gap-2 transition-all">
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
        let badgeClass = 'bg-rose-500/15 text-rose-600 dark:text-rose-400';

        if (pct >= 90) {
            grade = 'A+';
            badgeClass = 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400';
        } else if (pct >= 80) {
            grade = 'A';
            badgeClass = 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400';
        } else if (pct >= 70) {
            grade = 'B';
            badgeClass = 'bg-blue-500/15 text-blue-600 dark:text-blue-400';
        } else if (pct >= 60) {
            grade = 'C';
            badgeClass = 'bg-amber-500/15 text-amber-600 dark:text-amber-400';
        } else if (pct >= 50) {
            grade = 'D';
            badgeClass = 'bg-orange-500/15 text-orange-600 dark:text-orange-400';
        }

        gradeBadge.innerText = grade;
        gradeBadge.className = 'mark-grade-badge font-bold px-2.5 py-0.5 rounded-md text-xs ' + badgeClass;
    }
</script>
