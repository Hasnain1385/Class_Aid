<div class="space-y-6">

    <!-- Header & Action Ribbon -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="/attendance" class="text-xs text-slate-400 hover:text-indigo-500 flex items-center gap-1 transition-colors">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    <span>Attendance Home</span>
                </a>
                <span class="text-xs text-slate-400">•</span>
                <span class="text-xs font-semibold text-indigo-500 uppercase">Audit &amp; Intelligence</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white mt-1 flex items-center gap-2">
                <i data-lucide="bar-chart-2" class="w-6 h-6 text-indigo-500"></i>
                <span>Attendance Records &amp; Analytics Dashboard</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                Multi-filter attendance records for whole class rosters, individual students, or custom student cohorts.
            </p>
        </div>

        <?php if ($selectedClass && $report): ?>
            <?php 
                $printUrl = '/attendance/print/report?class_id=' . $selectedClass['id'] 
                    . '&date_from=' . urlencode($dateFrom) 
                    . '&date_to=' . urlencode($dateTo) 
                    . '&scope=' . urlencode($scope);
                if ($scope === 'single' && $singleStudentId > 0) {
                    $printUrl .= '&student_id=' . $singleStudentId;
                } elseif ($scope === 'selected' && !empty($selectedIds)) {
                    $printUrl .= '&students=' . urlencode(implode(',', $selectedIds));
                }
            ?>
            <div class="flex items-center gap-2">
                <a href="<?= $printUrl ?>" target="_blank" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 shadow-md shadow-indigo-600/25 transition-all">
                    <i data-lucide="printer" class="w-4 h-4"></i>
                    <span>Print A4 Institutional Report</span>
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Filter Console Card -->
    <div class="glass-card p-5 space-y-4">
        <form method="GET" action="/attendance/reports" id="filter-form" class="space-y-4">
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <!-- Class Selector -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Class Section *
                    </label>
                    <select name="class_id" onchange="document.getElementById('filter-form').submit()" class="w-full px-3 py-2 text-xs rounded-xl glass-input font-medium">
                        <?php foreach ($classes as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= ($selectedClass && (int)$selectedClass['id'] === (int)$c['id']) ? 'selected' : '' ?>>
                                <?= $e($c['name']) ?> - <?= $e($c['section']) ?> <?= $c['is_home_class'] ? '(Home)' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Date From -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Date From
                    </label>
                    <input type="date" name="date_from" value="<?= $e($dateFrom) ?>" class="w-full px-3 py-2 text-xs rounded-xl glass-input font-mono tabular-nums">
                </div>

                <!-- Date To -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Date To
                    </label>
                    <input type="date" name="date_to" value="<?= $e($dateTo) ?>" class="w-full px-3 py-2 text-xs rounded-xl glass-input font-mono tabular-nums">
                </div>

                <!-- Scope Selection -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Filter Scope
                    </label>
                    <select name="scope" id="scope-select" onchange="updateScopeVisibility()" class="w-full px-3 py-2 text-xs rounded-xl glass-input font-medium">
                        <option value="all" <?= $scope === 'all' ? 'selected' : '' ?>>Whole Class (All Students)</option>
                        <option value="single" <?= $scope === 'single' ? 'selected' : '' ?>>Single Specific Student</option>
                        <option value="selected" <?= $scope === 'selected' ? 'selected' : '' ?>>Select Multiple Students</option>
                    </select>
                </div>
            </div>

            <!-- Single Student Dropdown (Conditional) -->
            <div id="single-student-wrap" class="<?= $scope === 'single' ? '' : 'hidden' ?> pt-1 border-t border-slate-100 dark:border-slate-800">
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                    Select Target Student
                </label>
                <select name="student_id" class="w-full sm:w-80 px-3 py-2 text-xs rounded-xl glass-input">
                    <option value="">-- Choose Student --</option>
                    <?php foreach ($allStudents as $stu): ?>
                        <option value="<?= $stu['id'] ?>" <?= $singleStudentId === (int)$stu['id'] ? 'selected' : '' ?>>
                            <?= $e($stu['first_name'] . ' ' . $stu['last_name']) ?> (<?= $e($stu['student_admin_id']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Multiple Students Checkbox Panel (Conditional) -->
            <div id="multi-student-wrap" class="<?= $scope === 'selected' ? '' : 'hidden' ?> pt-2 border-t border-slate-100 dark:border-slate-800 space-y-2">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">
                        Check Students to Include in Report:
                    </label>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="selectAllStudents(true)" class="text-[11px] text-indigo-600 dark:text-indigo-400 hover:underline">Select All</button>
                        <span class="text-slate-300">|</span>
                        <button type="button" onclick="selectAllStudents(false)" class="text-[11px] text-slate-500 hover:underline">Deselect All</button>
                    </div>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2 max-h-48 overflow-y-auto p-2 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 text-xs">
                    <?php foreach ($allStudents as $stu): ?>
                        <?php $checked = in_array((int)$stu['id'], $selectedIds, true); ?>
                        <label class="flex items-center gap-2 p-1 rounded hover:bg-slate-200/50 dark:hover:bg-slate-700/50 cursor-pointer">
                            <input type="checkbox" name="students[]" value="<?= $stu['id'] ?>" class="student-checkbox rounded text-indigo-600" <?= $checked ? 'checked' : '' ?>>
                            <span class="truncate text-slate-800 dark:text-slate-200"><?= $e($stu['first_name'] . ' ' . $stu['last_name']) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Action buttons -->
            <div class="flex items-center justify-between pt-2">
                <div class="flex items-center gap-2">
                    <!-- Quick Presets -->
                    <span class="text-[11px] font-semibold text-slate-400 uppercase">Presets:</span>
                    <button type="button" onclick="setPreset('this_month')" class="px-2.5 py-1 text-[11px] rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-indigo-500/10 hover:text-indigo-600 transition-colors">
                        This Month
                    </button>
                    <button type="button" onclick="setPreset('last_30')" class="px-2.5 py-1 text-[11px] rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-indigo-500/10 hover:text-indigo-600 transition-colors">
                        Last 30 Days
                    </button>
                    <button type="button" onclick="setPreset('this_week')" class="px-2.5 py-1 text-[11px] rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-indigo-500/10 hover:text-indigo-600 transition-colors">
                        This Week
                    </button>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" onclick="printFilteredAttendanceReport()" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 shadow-sm transition-all flex items-center gap-1.5 cursor-pointer">
                        <i data-lucide="printer" class="w-4 h-4 text-indigo-500"></i>
                        <span>Print Filtered A4 Report</span>
                    </button>

                    <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 shadow-sm transition-all flex items-center gap-1.5 cursor-pointer">
                        <i data-lucide="filter" class="w-4 h-4"></i>
                        <span>Apply Filter</span>
                    </button>
                </div>
            </div>

        </form>
    </div>

    <?php if (!$selectedClass): ?>
        <div class="glass-card p-12 text-center text-slate-500 dark:text-slate-400">
            <i data-lucide="book-open" class="w-12 h-12 mx-auto mb-3 opacity-40"></i>
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">No Class Selected</h3>
            <p class="text-xs mt-1">Please select a class from the filter ribbon above to view attendance reports.</p>
        </div>
    <?php elseif ($report): ?>

        <!-- KPI Metrics Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            <div class="glass-card p-4">
                <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Attendance Rate</span>
                <span class="text-2xl font-black text-indigo-600 dark:text-indigo-400 mt-1 block tabular-nums">
                    <?= $report['summary']['overall_rate'] ?>%
                </span>
                <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 block">Cohort Average</span>
            </div>

            <div class="glass-card p-4">
                <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Sessions Taken</span>
                <span class="text-2xl font-black text-slate-900 dark:text-white mt-1 block tabular-nums">
                    <?= $report['summary']['total_sessions_taken'] ?>
                </span>
                <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 block">School Days</span>
            </div>

            <div class="glass-card p-4">
                <span class="text-[10px] uppercase font-bold text-emerald-600 dark:text-emerald-400 block tracking-wider">Present (P)</span>
                <span class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1 block tabular-nums">
                    <?= $report['summary']['total_p'] ?>
                </span>
                <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 block">Total Marks</span>
            </div>

            <div class="glass-card p-4">
                <span class="text-[10px] uppercase font-bold text-rose-600 dark:text-rose-400 block tracking-wider">Absent (A)</span>
                <span class="text-2xl font-black text-rose-600 dark:text-rose-400 mt-1 block tabular-nums">
                    <?= $report['summary']['total_a'] ?>
                </span>
                <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 block">Total Truancies</span>
            </div>

            <div class="glass-card p-4">
                <span class="text-[10px] uppercase font-bold text-amber-600 dark:text-amber-400 block tracking-wider">Late / Excused</span>
                <span class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-1 block tabular-nums">
                    <?= $report['summary']['total_l'] + $report['summary']['total_e'] ?>
                </span>
                <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 block"><?= $report['summary']['total_l'] ?>L • <?= $report['summary']['total_e'] ?>E</span>
            </div>

            <div class="glass-card p-4 <?= $report['summary']['chronic_count'] > 0 ? 'border border-rose-500/40 bg-rose-50/20 dark:bg-rose-950/20' : '' ?>">
                <span class="text-[10px] uppercase font-bold text-rose-500 block tracking-wider">Chronic Alerts</span>
                <span class="text-2xl font-black text-rose-600 dark:text-rose-400 mt-1 block tabular-nums">
                    <?= $report['summary']['chronic_count'] ?>
                </span>
                <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 block">&lt;85% or &ge;3 Absences</span>
            </div>
        </div>

        <!-- Student Attendance Table -->
        <div class="glass-card overflow-hidden">
            <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                        Student Breakdown (<?= count($report['students']) ?> Students)
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Period: <?= date('M d, Y', strtotime($dateFrom)) ?> &mdash; <?= date('M d, Y', strtotime($dateTo)) ?>
                    </p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50/70 dark:bg-slate-800/50 text-slate-600 dark:text-slate-400 font-bold uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="py-3 px-3 w-10 text-center">#</th>
                            <th class="py-3 px-3 w-32">Admin ID</th>
                            <th class="py-3 px-4">Student Full Name</th>
                            <th class="py-3 px-3 text-center">Sessions</th>
                            <th class="py-3 px-3 text-center text-emerald-600 dark:text-emerald-400 font-bold">P</th>
                            <th class="py-3 px-3 text-center text-rose-600 dark:text-rose-400 font-bold">A</th>
                            <th class="py-3 px-3 text-center text-amber-600 dark:text-amber-400 font-bold">L</th>
                            <th class="py-3 px-3 text-center text-blue-600 dark:text-blue-400 font-bold">E</th>
                            <th class="py-3 px-4 w-40">Rate %</th>
                            <th class="py-3 px-3 text-center">Status</th>
                            <th class="py-3 px-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <?php if (empty($report['students'])): ?>
                            <tr>
                                <td colspan="11" class="py-8 text-center text-slate-500 italic">
                                    No students match the selected filter criteria.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $idx = 1; foreach ($report['students'] as $stu): ?>
                                <?php 
                                    $rate = $stu['attendance_rate'];
                                    $rateColor = ($rate >= 90) ? 'bg-emerald-500' : (($rate >= 80) ? 'bg-amber-500' : 'bg-rose-500');
                                    $badgeClass = ($rate >= 90) ? 'badge-emerald' : (($rate >= 80) ? 'badge-amber' : 'badge-rose');
                                ?>
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors <?= $stu['is_chronic'] ? 'bg-rose-50/20 dark:bg-rose-950/10' : '' ?>">
                                    <td class="py-2.5 px-3 text-center font-mono text-[11px] text-slate-400">
                                        <?= $idx++ ?>
                                    </td>
                                    <td class="py-2.5 px-3 font-mono font-bold text-indigo-600 dark:text-indigo-400 tabular-nums">
                                        <?= $e($stu['student_admin_id']) ?>
                                    </td>
                                    <td class="py-2.5 px-4 font-semibold text-slate-900 dark:text-white">
                                        <div class="flex items-center gap-2">
                                            <span><?= $e($stu['first_name'] . ' ' . $stu['last_name']) ?></span>
                                            <?php if ($stu['consecutive_absences'] >= 3): ?>
                                                <span class="text-[10px] px-1.5 py-0.5 rounded badge-rose font-bold" title="<?= $stu['consecutive_absences'] ?> Consecutive Absences">
                                                    <?= $stu['consecutive_absences'] ?> Consec.
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="py-2.5 px-3 text-center font-mono tabular-nums text-slate-700 dark:text-slate-300">
                                        <?= $stu['total_sessions'] ?>
                                    </td>
                                    <td class="py-2.5 px-3 text-center font-mono font-bold text-emerald-600 dark:text-emerald-400 tabular-nums">
                                        <?= $stu['present_count'] ?>
                                    </td>
                                    <td class="py-2.5 px-3 text-center font-mono font-bold text-rose-600 dark:text-rose-400 tabular-nums">
                                        <?= $stu['absent_count'] ?>
                                    </td>
                                    <td class="py-2.5 px-3 text-center font-mono font-bold text-amber-600 dark:text-amber-400 tabular-nums">
                                        <?= $stu['late_count'] ?>
                                    </td>
                                    <td class="py-2.5 px-3 text-center font-mono font-bold text-blue-600 dark:text-blue-400 tabular-nums">
                                        <?= $stu['excused_count'] ?>
                                    </td>
                                    <td class="py-2.5 px-4">
                                        <div class="flex items-center gap-2">
                                            <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-1.5 overflow-hidden">
                                                <div class="<?= $rateColor ?> h-1.5 rounded-full" style="width: <?= min(100, $rate) ?>%"></div>
                                            </div>
                                            <span class="font-mono text-xs font-bold tabular-nums w-12 text-right <?= ($rate < 80) ? 'text-rose-600' : 'text-slate-700 dark:text-slate-300' ?>">
                                                <?= $rate ?>%
                                            </span>
                                        </div>
                                    </td>
                                    <td class="py-2.5 px-3 text-center">
                                        <?php if ($stu['is_chronic']): ?>
                                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-full badge-rose uppercase">
                                                Chronic
                                            </span>
                                        <?php else: ?>
                                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-full badge-emerald uppercase">
                                                Normal
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-2.5 px-3 text-right">
                                        <div class="flex items-center justify-end gap-1">
                                            <!-- Print Single Student A4 Report -->
                                            <a href="/attendance/print/report?class_id=<?= $selectedClass['id'] ?>&scope=single&student_id=<?= $stu['id'] ?>&date_from=<?= urlencode($dateFrom) ?>&date_to=<?= urlencode($dateTo) ?>" target="_blank" class="p-1.5 rounded-lg bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-500/20 transition-colors" title="Print Student A4 Attendance Sheet">
                                                <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                                            </a>
                                            <?php if ($stu['consecutive_absences'] >= 2 || $stu['absent_count'] >= 2): ?>
                                                <a href="/calls/create?student_id=<?= $stu['id'] ?>&category=Absenteeism" class="p-1.5 rounded-lg bg-rose-500/10 text-rose-600 dark:text-rose-400 hover:bg-rose-500/20 transition-colors" title="Log Follow-up Call">
                                                    <i data-lucide="phone-outgoing" class="w-3.5 h-3.5"></i>
                                                </a>
                                            <?php endif; ?>
                                            <a href="/students/<?= $stu['id'] ?>" class="p-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors" title="View Dossier">
                                                <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    <?php endif; ?>

</div>

<script>
    function updateScopeVisibility() {
        const scope = document.getElementById('scope-select').value;
        const singleWrap = document.getElementById('single-student-wrap');
        const multiWrap = document.getElementById('multi-student-wrap');

        if (scope === 'single') {
            singleWrap.classList.remove('hidden');
            multiWrap.classList.add('hidden');
        } else if (scope === 'selected') {
            singleWrap.classList.add('hidden');
            multiWrap.classList.remove('hidden');
        } else {
            singleWrap.classList.add('hidden');
            multiWrap.classList.add('hidden');
        }
    }

    function selectAllStudents(checked) {
        document.querySelectorAll('.student-checkbox').forEach(cb => {
            cb.checked = checked;
        });
    }

    function setPreset(type) {
        const today = new Date();
        const yyyy = today.getFullYear();
        const mm = String(today.getMonth() + 1).padStart(2, '0');
        const dd = String(today.getDate()).padStart(2, '0');
        const todayStr = `${yyyy}-${mm}-${dd}`;

        const toInput = document.querySelector('input[name="date_to"]');
        const fromInput = document.querySelector('input[name="date_from"]');
        toInput.value = todayStr;

        if (type === 'this_month') {
            fromInput.value = `${yyyy}-${mm}-01`;
        } else if (type === 'last_30') {
            const past = new Date();
            past.setDate(past.getDate() - 30);
            fromInput.value = `${past.getFullYear()}-${String(past.getMonth() + 1).padStart(2, '0')}-${String(past.getDate()).padStart(2, '0')}`;
        } else if (type === 'this_week') {
            const dayOfWeek = today.getDay(); // 0 is Sunday
            const past = new Date();
            past.setDate(past.getDate() - (dayOfWeek === 0 ? 6 : dayOfWeek - 1)); // Monday
            fromInput.value = `${past.getFullYear()}-${String(past.getMonth() + 1).padStart(2, '0')}-${String(past.getDate()).padStart(2, '0')}`;
        }
        document.getElementById('filter-form').submit();
    }

    function printFilteredAttendanceReport() {
        const form = document.getElementById('filter-form');
        const formData = new FormData(form);
        const params = new URLSearchParams();

        for (const [key, value] of formData.entries()) {
            params.append(key, value);
        }

        window.open('/attendance/print/report?' + params.toString(), '_blank');
    }
</script>
