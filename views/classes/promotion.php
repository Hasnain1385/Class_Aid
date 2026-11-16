<div class="space-y-6">

    <!-- Header & Action Ribbon -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="/classes" class="text-xs text-slate-400 hover:text-indigo-500 flex items-center gap-1 transition-colors">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    <span>Classes &amp; Rosters</span>
                </a>
                <span class="text-xs text-slate-400">•</span>
                <span class="text-xs font-semibold text-emerald-500 uppercase">Academic Rollover</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white mt-1 flex items-center gap-2">
                <i data-lucide="arrow-up-right" class="w-6 h-6 text-emerald-500"></i>
                <span>Student Academic Promotion &amp; Rollover</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                Promote students to their next academic grade while preserving all previous attendance rolls, exam marks, and parent call logs intact.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="/classes" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 shadow-sm transition-all">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Back to Classes</span>
            </a>
        </div>
    </div>

    <!-- Historical Data Integrity Guarantee Banner -->
    <div class="glass-card p-4 rounded-2xl border border-indigo-200 dark:border-indigo-900/60 bg-gradient-to-r from-indigo-500/10 via-purple-500/5 to-transparent flex items-start gap-3.5 shadow-sm">
        <div class="w-9 h-9 rounded-xl bg-indigo-600 text-white flex items-center justify-center flex-shrink-0 shadow-md shadow-indigo-600/20">
            <i data-lucide="shield-check" class="w-5 h-5"></i>
        </div>
        <div class="text-xs space-y-1">
            <h4 class="font-bold text-slate-900 dark:text-white">Historical Data Integrity Guarantee</h4>
            <p class="text-slate-600 dark:text-slate-300 leading-relaxed">
                When you promote students (for example, from <strong>9th Grade to 10th Grade</strong>), their active roster moves to the new class immediately. 
                <strong>All entered attendance records, session rolls, and exam grade marksheets from previous months remain 100% intact</strong> under their historical class records. 
                New roll calls and assessments will automatically be tied to the new class arrangements.
            </p>
        </div>
    </div>

    <!-- Class Transition Selection Console -->
    <div class="glass-card p-5 space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            
            <!-- Source Class (Current Roster) -->
            <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700/80 space-y-2">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                        1. Source Class (Current Roster) *
                    </label>
                    <span class="text-[11px] font-semibold text-indigo-600 dark:text-indigo-400">
                        <?= count($students) ?> Students Enrolled
                    </span>
                </div>
                <div class="glass-input-group" onclick="this.querySelector('select')?.focus()">
                    <div class="input-icon-box">
                        <i data-lucide="graduation-cap" class="w-4 h-4 text-indigo-500"></i>
                    </div>
                    <select id="source-class-select" onchange="onSourceClassChange(this.value)">
                        <?php foreach ($classes as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= ($sourceClassId === (int)$c['id']) ? 'selected' : '' ?>>
                                <?= $e($c['name']) ?> - <?= $e($c['section']) ?> (<?= $e($c['academic_year']) ?>) <?= $c['is_home_class'] ? '★ Home' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php if ($sourceClass): ?>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">
                        Academic Year: <span class="font-mono font-medium text-slate-700 dark:text-slate-300"><?= $e($sourceClass['academic_year']) ?></span> &bull; 
                        Section: <span class="font-medium text-slate-700 dark:text-slate-300"><?= $e($sourceClass['section']) ?></span>
                    </p>
                <?php endif; ?>
            </div>

            <!-- Destination Class (Promoted Class) -->
            <div class="p-4 rounded-xl bg-emerald-50/40 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-800/80 space-y-2">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-bold uppercase tracking-wider text-emerald-800 dark:text-emerald-300">
                        2. Destination Class (Next Level) *
                    </label>
                    <button type="button" 
                            onclick="document.getElementById('modal-quick-create-class').classList.remove('hidden')"
                            class="text-[11px] font-bold text-emerald-700 dark:text-emerald-400 hover:underline flex items-center gap-1">
                        <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i>
                        <span>Create Next Class</span>
                    </button>
                </div>
                <div class="glass-input-group" onclick="this.querySelector('select')?.focus()">
                    <div class="input-icon-box">
                        <i data-lucide="arrow-up-circle" class="w-4 h-4 text-emerald-500"></i>
                    </div>
                    <select id="dest-class-select" onchange="onDestClassChange(this.value)">
                        <option value="">-- Choose Next / Destination Class --</option>
                        <?php foreach ($classes as $c): ?>
                            <?php if ((int)$c['id'] !== $sourceClassId): ?>
                                <option value="<?= $c['id'] ?>" <?= ($destClassId === (int)$c['id']) ? 'selected' : '' ?>>
                                    <?= $e($c['name']) ?> - <?= $e($c['section']) ?> (<?= $e($c['academic_year']) ?>) <?= $c['is_home_class'] ? '★ Home' : '' ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php if ($destClass): ?>
                    <p class="text-[11px] text-emerald-700 dark:text-emerald-400">
                        Target Year: <span class="font-mono font-medium"><?= $e($destClass['academic_year']) ?></span> &bull; 
                        Section: <span class="font-medium"><?= $e($destClass['section']) ?></span>
                    </p>
                <?php else: ?>
                    <p class="text-[11px] text-slate-400 dark:text-slate-500">
                        Please select the next class where selected students will be transferred.
                    </p>
                <?php endif; ?>
            </div>

        </div>
    </div>

    <!-- Student Roster Promotion Checklist Form -->
    <form method="POST" action="/classes/promotion" id="promotion-form" onsubmit="return confirmPromotionSubmit(event)">
        <?= $csrf() ?>
        <input type="hidden" name="source_class_id" value="<?= $sourceClassId ?>">
        <input type="hidden" name="dest_class_id" id="hidden-dest-class-id" value="<?= $destClassId ?>">

        <div class="glass-card overflow-hidden">
            
            <!-- Table Action Toolbar -->
            <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/50 dark:bg-slate-800/40">
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="select-all-students" onchange="toggleSelectAll(this.checked)" class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 cursor-pointer" checked>
                        <label for="select-all-students" class="text-xs font-bold text-slate-800 dark:text-slate-200 cursor-pointer">
                            Select All Students
                        </label>
                    </div>
                    <span class="text-xs text-slate-400">•</span>
                    <span id="selected-counter" class="text-xs font-bold text-indigo-600 dark:text-indigo-400 font-mono">
                        <?= count($students) ?> of <?= count($students) ?> selected
                    </span>
                </div>

                <!-- Instant Filter Input -->
                <div class="flex items-center gap-2">
                    <div class="glass-input-group !h-9 text-xs sm:w-64" onclick="this.querySelector('input')?.focus()">
                        <div class="input-icon-box !w-8">
                            <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400"></i>
                        </div>
                        <input type="text" id="student-quick-search" oninput="filterStudentsTable(this.value)" placeholder="Filter student list..." class="!text-xs">
                    </div>

                    <button type="button" onclick="selectAll(true)" class="text-xs text-indigo-600 dark:text-indigo-400 font-semibold hover:underline hidden sm:inline">Check All</button>
                    <span class="text-slate-300 hidden sm:inline">|</span>
                    <button type="button" onclick="selectAll(false)" class="text-xs text-slate-500 font-semibold hover:underline hidden sm:inline">Uncheck All</button>
                </div>
            </div>

            <!-- Student Roster Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50/70 dark:bg-slate-800/50 text-slate-600 dark:text-slate-400 font-bold uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="py-3 px-3 w-10 text-center">#</th>
                            <th class="py-3 px-3 w-12 text-center">Select</th>
                            <th class="py-3 px-3 w-28">Admin ID</th>
                            <th class="py-3 px-4">Student Full Name</th>
                            <th class="py-3 px-3 text-center">Attendance Standing</th>
                            <th class="py-3 px-3 text-center">Consecutive Absences</th>
                            <th class="py-3 px-4">Current Class</th>
                            <th class="py-3 px-4">Projected Rollover</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800" id="students-table-body">
                        <?php if (empty($students)): ?>
                            <tr>
                                <td colspan="8" class="py-12 text-center text-slate-500 italic">
                                    No students are enrolled in the selected source class.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $idx = 1; foreach ($students as $stu): ?>
                                <?php
                                    $sId = (int) $stu['id'];
                                    $stats = $batchStats[$sId] ?? null;
                                    $rate = $stats ? $stats['rate'] : 100.0;
                                    $consec = $stats ? $stats['consecutive_absences'] : 0;
                                    $totalDays = $stats ? $stats['total_days'] : 0;
                                    $rateColor = ($rate >= 90) ? 'text-emerald-600 dark:text-emerald-400' : (($rate >= 80) ? 'text-amber-600 dark:text-amber-400' : 'text-rose-600 dark:text-rose-400');
                                ?>
                                <tr class="student-row hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors" data-name="<?= strtolower($stu['first_name'] . ' ' . $stu['last_name']) ?>" data-admin="<?= strtolower($stu['student_admin_id']) ?>">
                                    <td class="py-2.5 px-3 text-center font-mono text-[11px] text-slate-400">
                                        <?= $idx++ ?>
                                    </td>
                                    <td class="py-2.5 px-3 text-center">
                                        <input type="checkbox" name="students[]" value="<?= $sId ?>" class="student-item-cb w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 cursor-pointer" onchange="updateSelectedCount()" checked>
                                    </td>
                                    <td class="py-2.5 px-3 font-mono font-bold text-indigo-600 dark:text-indigo-400 tabular-nums">
                                        <?= $e($stu['student_admin_id']) ?>
                                    </td>
                                    <td class="py-2.5 px-4 font-semibold text-slate-900 dark:text-white">
                                        <div class="flex items-center gap-2">
                                            <div class="w-6 h-6 rounded-lg bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 text-xs font-bold flex items-center justify-center flex-shrink-0">
                                                <?= strtoupper(substr($stu['first_name'], 0, 1)) ?>
                                            </div>
                                            <span><?= $e($stu['first_name'] . ' ' . $stu['last_name']) ?></span>
                                            <span class="text-[10px] text-slate-400 font-normal">(<?= $e($stu['gender'] ?? 'N/A') ?>)</span>
                                        </div>
                                    </td>
                                    <td class="py-2.5 px-3 text-center">
                                        <span class="font-mono font-bold text-xs tabular-nums <?= $rateColor ?>">
                                            <?= $rate ?>%
                                        </span>
                                        <span class="text-[10px] text-slate-400 block font-normal">
                                            (<?= $totalDays ?> sessions)
                                        </span>
                                    </td>
                                    <td class="py-2.5 px-3 text-center">
                                        <?php if ($consec >= 3): ?>
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold badge-rose">
                                                <?= $consec ?> Consecutive
                                            </span>
                                        <?php elseif ($consec > 0): ?>
                                            <span class="text-xs text-amber-600 font-medium font-mono">
                                                <?= $consec ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-xs text-emerald-600 font-medium">0</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-2.5 px-4 font-medium text-slate-700 dark:text-slate-300">
                                        <span class="px-2 py-0.5 rounded-lg bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-[11px]">
                                            <?= $e($sourceClass['name'] . ' - ' . $sourceClass['section']) ?>
                                        </span>
                                    </td>
                                    <td class="py-2.5 px-4 font-semibold">
                                        <?php if ($destClass): ?>
                                            <span class="px-2 py-0.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-300 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-[11px] flex items-center gap-1 w-fit">
                                                <i data-lucide="arrow-right" class="w-3 h-3 text-emerald-500"></i>
                                                <span><?= $e($destClass['name'] . ' - ' . $destClass['section']) ?></span>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-slate-400 italic text-[11px]">Select destination class above</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Promotion Execution Footer Ribbon -->
            <div class="p-4 border-t border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 bg-slate-50/60 dark:bg-slate-900/40">
                <div class="text-xs text-slate-500 dark:text-slate-400">
                    <p class="font-medium text-slate-800 dark:text-slate-200">Ready to execute rollover?</p>
                    <p class="text-[11px]">Unchecked students will remain in the current class as repeating or unpromoted.</p>
                </div>

                <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
                    <a href="/classes" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-800 transition-colors">
                        Cancel
                    </a>

                    <button type="submit" 
                            id="submit-promotion-btn"
                            class="px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 shadow-md shadow-emerald-600/25 transition-all flex items-center gap-2 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                        <i data-lucide="check-check" class="w-4 h-4"></i>
                        <span id="submit-promotion-btn-text">Execute Academic Promotion</span>
                    </button>
                </div>
            </div>

        </div>
    </form>

    <!-- Quick Create Class Modal -->
    <div id="modal-quick-create-class" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" onclick="if(event.target === this) this.classList.add('hidden')">
        <div class="glass-card bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xl rounded-2xl w-full max-w-md p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                    </div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Create Next Level Class</h3>
                </div>
                <button type="button" onclick="document.getElementById('modal-quick-create-class').classList.add('hidden')" class="p-1 rounded-lg text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form method="POST" action="/classes" class="space-y-3.5">
                <?= $csrf() ?>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                        Class Name *
                    </label>
                    <input type="text" name="name" placeholder="e.g. 10th Grade, Grade 10, or Secondary II" required class="glass-input text-xs w-full">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                            Section *
                        </label>
                        <input type="text" name="section" placeholder="e.g. Section A" value="<?= $e($sourceClass['section'] ?? 'A') ?>" required class="glass-input text-xs w-full">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                            Academic Year
                        </label>
                        <input type="text" name="academic_year" value="<?= date('Y') . '-' . (date('Y') + 1) ?>" class="glass-input text-xs w-full tabular-nums">
                    </div>
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <input type="checkbox" name="is_home_class" id="quick-is-home" value="1" class="w-4 h-4 rounded text-indigo-600" <?= ($sourceClass && $sourceClass['is_home_class']) ? 'checked' : '' ?>>
                    <label for="quick-is-home" class="text-xs text-slate-700 dark:text-slate-300 cursor-pointer">
                        Set as primary Home Class
                    </label>
                </div>

                <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2">
                    <button type="button" onclick="document.getElementById('modal-quick-create-class').classList.add('hidden')" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 shadow-sm">
                        Create Class
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
    function onSourceClassChange(classId) {
        const destSelect = document.getElementById('dest-class-select');
        const destId = destSelect ? destSelect.value : '';
        window.location.href = '/classes/promotion?source_class_id=' + classId + (destId ? '&dest_class_id=' + destId : '');
    }

    function onDestClassChange(destId) {
        const sourceSelect = document.getElementById('source-class-select');
        const sourceId = sourceSelect ? sourceSelect.value : '';
        document.getElementById('hidden-dest-class-id').value = destId;
        window.location.href = '/classes/promotion?source_class_id=' + sourceId + '&dest_class_id=' + destId;
    }

    function toggleSelectAll(checked) {
        selectAll(checked);
    }

    function selectAll(checked) {
        document.querySelectorAll('.student-item-cb').forEach(cb => {
            const row = cb.closest('tr');
            if (!row || row.style.display !== 'none') {
                cb.checked = checked;
            }
        });
        document.getElementById('select-all-students').checked = checked;
        updateSelectedCount();
    }

    function updateSelectedCount() {
        const checkboxes = document.querySelectorAll('.student-item-cb');
        let selectedCount = 0;
        let totalCount = checkboxes.length;

        checkboxes.forEach(cb => {
            if (cb.checked) selectedCount++;
        });

        const counterEl = document.getElementById('selected-counter');
        if (counterEl) {
            counterEl.textContent = `${selectedCount} of ${totalCount} selected`;
        }

        const btnText = document.getElementById('submit-promotion-btn-text');
        if (btnText) {
            btnText.textContent = selectedCount > 0 ? `Execute Academic Promotion (${selectedCount} Students)` : 'Execute Academic Promotion';
        }

        const btn = document.getElementById('submit-promotion-btn');
        if (btn) {
            btn.disabled = (selectedCount === 0);
        }
    }

    function filterStudentsTable(query) {
        const q = query.toLowerCase().trim();
        const rows = document.querySelectorAll('.student-row');
        rows.forEach(row => {
            const name = row.getAttribute('data-name') || '';
            const admin = row.getAttribute('data-admin') || '';
            if (name.includes(q) || admin.includes(q)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
        updateSelectedCount();
    }

    function confirmPromotionSubmit(event) {
        const destId = document.getElementById('hidden-dest-class-id').value;
        if (!destId) {
            event.preventDefault();
            alert('Please select a Destination Class before proceeding with promotion.');
            document.getElementById('dest-class-select')?.focus();
            return false;
        }

        const selectedCount = document.querySelectorAll('.student-item-cb:checked').length;
        if (selectedCount === 0) {
            event.preventDefault();
            alert('Please select at least one student to promote.');
            return false;
        }

        const confirmMsg = `Are you sure you want to promote ${selectedCount} student(s)?\n\nAll historical records (previous months' attendance, exam marks, and parent calls) will remain permanently intact under the old class record.`;
        return confirm(confirmMsg);
    }

    document.addEventListener('DOMContentLoaded', () => {
        updateSelectedCount();
    });
</script>
