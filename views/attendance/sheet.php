<div class="space-y-6">

    <!-- Top Header & Toolbar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="/attendance" class="text-xs text-slate-400 hover:text-indigo-500 flex items-center gap-1 transition-colors">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    <span>Attendance</span>
                </a>
                <span class="text-xs text-slate-400">•</span>
                <span class="text-xs font-semibold text-indigo-500 uppercase"><?= $e($class['name']) ?> - <?= $e($class['section']) ?></span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white mt-1 flex items-center gap-2.5">
                <span>Roll Call Register</span>
                <?php if ($isSaved): ?>
                    <span class="text-xs px-2.5 py-0.5 rounded-full badge-emerald font-bold">
                        Saved
                    </span>
                <?php else: ?>
                    <span class="text-xs px-2.5 py-0.5 rounded-full badge-amber font-bold">
                        Unsaved Changes
                    </span>
                <?php endif; ?>
            </h1>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <!-- Date change form -->
            <form method="GET" action="/attendance/sheet/<?= $class['id'] ?>" class="flex items-center gap-1.5">
                <input type="hidden" name="session" value="<?= $e($sessionType) ?>">
                <input type="date"
                       name="date"
                       value="<?= $e($date) ?>"
                       onchange="this.form.submit()"
                       class="px-3 py-2 text-xs rounded-xl glass-input font-medium tabular-nums">
            </form>

            <!-- 1-Tap Mark All Present Button -->
            <button type="button"
                    id="mark-all-present-btn"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold badge-emerald hover:opacity-90 transition-all cursor-pointer">
                <i data-lucide="check-check" class="w-4 h-4"></i>
                <span>Mark All Present</span>
            </button>

            <!-- View Previous Class Reports Button -->
            <a href="/attendance/reports?class_id=<?= $class['id'] ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold bg-white dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 shadow-sm transition-all" title="View historical records and date range reports">
                <i data-lucide="bar-chart-2" class="w-4 h-4 text-indigo-500"></i>
                <span>Class Records &amp; Reports</span>
            </a>

            <!-- Print Register Button -->
            <a href="/attendance/print/sheet/<?= $class['id'] ?>?date=<?= $e($date) ?>&session=<?= $e($sessionType) ?>" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold bg-white dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 shadow-sm transition-all">
                <i data-lucide="printer" class="w-4 h-4 text-indigo-500"></i>
                <span>Print Daily Table</span>
            </a>
        </div>
    </div>

    <!-- Dual Session Selector Tabs -->
    <div class="flex items-center justify-between p-2 rounded-2xl glass-card border border-slate-200 dark:border-slate-800 gap-2 flex-wrap">
        <div class="flex items-center gap-2">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 pl-2">Session:</span>
            <!-- Morning Tab -->
            <a href="/attendance/sheet/<?= $class['id'] ?>?date=<?= $e($date) ?>&session=morning" class="px-3.5 py-1.5 rounded-xl text-xs font-bold flex items-center gap-1.5 transition-all <?= ($sessionType === 'morning') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200' ?>">
                <span>🌅 Morning Roll Call</span>
                <?php if (!empty($sessionStatus['morning_recorded'])): ?>
                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                <?php endif; ?>
            </a>

            <!-- Midday Tab (Disabled on Friday) -->
            <?php if ($isFriday): ?>
                <div class="px-3 py-1.5 rounded-xl text-xs font-medium text-slate-400 dark:text-slate-500 bg-slate-50 dark:bg-slate-800/40 border border-dashed border-slate-300 dark:border-slate-700 cursor-not-allowed flex items-center gap-1.5" title="Friday schedule has only a single morning roll call">
                    <span>☀️ Midday (Friday Off)</span>
                </div>
            <?php else: ?>
                <a href="/attendance/sheet/<?= $class['id'] ?>?date=<?= $e($date) ?>&session=midday" class="px-3.5 py-1.5 rounded-xl text-xs font-bold flex items-center gap-1.5 transition-all <?= ($sessionType === 'midday') ? 'bg-amber-600 text-white shadow-md shadow-amber-600/30' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200' ?>">
                    <span>☀️ Midday Roll Call</span>
                    <?php if (!empty($sessionStatus['midday_recorded'])): ?>
                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                    <?php endif; ?>
                </a>
            <?php endif; ?>
        </div>

        <div class="text-xs text-slate-500 dark:text-slate-400 pr-2">
            Schedule: <strong class="text-slate-800 dark:text-slate-200"><?= $isFriday ? 'Friday (Morning Only)' : 'Standard (Morning + Midday)' ?></strong>
        </div>
    </div>

    <!-- Edit Mode In-Place Notice -->
    <?php if ($isSaved): ?>
        <div class="p-3.5 rounded-2xl bg-indigo-50/70 dark:bg-indigo-950/40 border border-indigo-200 dark:border-indigo-800/80 text-xs text-indigo-900 dark:text-indigo-200 flex items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <i data-lucide="info" class="w-4 h-4 text-indigo-500 flex-shrink-0"></i>
                <span>
                    <strong>Editing <?= ucfirst($sessionType) ?>:</strong> <?= ucfirst($sessionType) ?> roll call for <strong><?= date('F d, Y', strtotime($date)) ?></strong> is already on record. Any changes you make will update existing records in-place without creating duplicate entries.
                </span>
            </div>
            <a href="/attendance/reports?class_id=<?= $class['id'] ?>" class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400 hover:underline whitespace-nowrap">
                View Past Records &rarr;
            </a>
        </div>
    <?php endif; ?>

    <!-- Attendance Form -->
    <form method="POST" action="/attendance/sheet/<?= $class['id'] ?>" class="space-y-4">
        <?= $csrf() ?>
        <input type="hidden" name="date" value="<?= $e($date) ?>">
        <input type="hidden" name="session_type" value="<?= $e($sessionType) ?>">

        <div class="space-y-1.5">
            <?php foreach ($students as $stu): ?>
                <?php
                    $currentStatus = $stu['current_status'] ?? 'P';
                    $consecutive = (int) ($stu['consecutive_absences'] ?? 0);
                ?>
                <div class="glass-card px-3 py-2 sm:px-4 sm:py-2 attendance-student-row flex flex-col md:flex-row md:items-center justify-between gap-2 <?= $consecutive >= 3 ? 'border-rose-500/50 bg-rose-50/20 dark:bg-rose-950/20' : '' ?>">

                    <!-- Hidden Status Input -->
                    <input type="hidden"
                           name="attendance[<?= $stu['id'] ?>][status]"
                           value="<?= $currentStatus ?>"
                           class="attendance-status-input">

                    <!-- Student Info Line (Clickable Card: Opens Attendance History Drawer) -->
                    <div class="flex items-center gap-2.5 min-w-0 flex-1 cursor-pointer group"
                         onclick="openStudentAttendanceHistory(<?= $stu['id'] ?>, '<?= $e($stu['first_name'] . ' ' . $stu['last_name']) ?>', '<?= $e($stu['student_admin_id']) ?>')"
                         title="Click to view previous attendance logs for <?= $e($stu['first_name']) ?>">
                        <div class="w-7 h-7 rounded-lg bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 font-bold text-xs flex items-center justify-center flex-shrink-0 group-hover:bg-indigo-500/25 transition-colors">
                            <?= strtoupper(substr($stu['first_name'], 0, 1)) ?>
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-bold text-xs sm:text-sm text-slate-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors truncate">
                                    <?= $e($stu['first_name'] . ' ' . $stu['last_name']) ?>
                                </span>
                                <span class="font-mono text-[11px] font-bold text-indigo-500 tabular-nums">
                                    <?= $e($stu['student_admin_id']) ?>
                                </span>
                                <?php if ($consecutive >= 3): ?>
                                    <span class="text-[10px] px-1.5 py-0.5 rounded-full badge-rose font-bold flex items-center gap-1 animate-pulse">
                                        <i data-lucide="alert-triangle" class="w-3 h-3"></i>
                                        <span><?= $consecutive ?> Consec. Absent</span>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <div class="flex items-center gap-2 text-[11px] text-slate-500 dark:text-slate-400 truncate">
                                <span>G: <strong class="text-slate-700 dark:text-slate-300"><?= $e($stu['primary_guardian_name']) ?></strong></span>
                                <span>•</span>
                                <span class="font-mono tabular-nums"><?= $e($stu['primary_phone']) ?></span>
                                <button type="button"
                                        onclick="event.stopPropagation(); openWhatsApp('<?= $e($stu['primary_phone']) ?>', '<?= $e($stu['first_name'] . ' ' . $stu['last_name']) ?>', '<?= $e($stu['student_admin_id']) ?>')"
                                        class="text-emerald-500 hover:text-emerald-600 inline-flex items-center gap-0.5 font-semibold text-[10px]" title="Chat on WhatsApp">
                                    <i data-lucide="message-circle" class="w-3 h-3"></i>
                                    <span>WA</span>
                                </button>
                                <?php if ($consecutive >= 3): ?>
                                    <a href="/calls/create?student_id=<?= $stu['id'] ?>&category=Absenteeism"
                                       onclick="event.stopPropagation()"
                                       class="text-rose-500 hover:underline font-semibold flex items-center gap-0.5 text-[10px]">
                                        <i data-lucide="phone-outgoing" class="w-3 h-3"></i>
                                        <span>Call</span>
                                    </a>
                                <?php endif; ?>
                                <button type="button"
                                        onclick="event.stopPropagation(); openStudentAttendanceHistory(<?= $stu['id'] ?>, '<?= $e($stu['first_name'] . ' ' . $stu['last_name']) ?>', '<?= $e($stu['student_admin_id']) ?>')"
                                        class="text-indigo-500 hover:text-indigo-600 inline-flex items-center gap-0.5 font-semibold text-[10px]" title="View Previous Records">
                                    <i data-lucide="history" class="w-3 h-3"></i>
                                    <span>History</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- 1-Tap Toggle Buttons & Remarks (Compact) -->
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <div class="grid grid-cols-4 gap-1 w-64 sm:w-72">
                            <!-- Present [P] -->
                            <button type="button"
                                    data-status="P"
                                    class="att-toggle-btn py-1 px-1.5 rounded-lg text-xs font-bold transition-all flex items-center justify-center gap-1 <?= $currentStatus === 'P' ? 'bg-emerald-600 text-white ring-2 ring-emerald-400 ring-offset-1 dark:ring-offset-slate-900 shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-emerald-700 dark:text-emerald-400 border border-slate-200 dark:border-slate-700' ?>">
                                <span class="font-black text-xs">P</span>
                                <span class="text-[11px] font-medium hidden sm:inline">Pres</span>
                            </button>

                            <!-- Absent [A] -->
                            <button type="button"
                                    data-status="A"
                                    class="att-toggle-btn py-1 px-1.5 rounded-lg text-xs font-bold transition-all flex items-center justify-center gap-1 <?= $currentStatus === 'A' ? 'bg-rose-600 text-white ring-2 ring-rose-400 ring-offset-1 dark:ring-offset-slate-900 shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-rose-700 dark:text-rose-400 border border-slate-200 dark:border-slate-700' ?>">
                                <span class="font-black text-xs">A</span>
                                <span class="text-[11px] font-medium hidden sm:inline">Abs</span>
                            </button>

                            <!-- Late [L] -->
                            <button type="button"
                                    data-status="L"
                                    class="att-toggle-btn py-1 px-1.5 rounded-lg text-xs font-bold transition-all flex items-center justify-center gap-1 <?= $currentStatus === 'L' ? 'bg-amber-600 text-white ring-2 ring-amber-400 ring-offset-1 dark:ring-offset-slate-900 shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-amber-700 dark:text-amber-400 border border-slate-200 dark:border-slate-700' ?>">
                                <span class="font-black text-xs">L</span>
                                <span class="text-[11px] font-medium hidden sm:inline">Late</span>
                            </button>

                            <!-- Excused [E] -->
                            <button type="button"
                                    data-status="E"
                                    class="att-toggle-btn py-1 px-1.5 rounded-lg text-xs font-bold transition-all flex items-center justify-center gap-1 <?= $currentStatus === 'E' ? 'bg-blue-600 text-white ring-2 ring-blue-400 ring-offset-1 dark:ring-offset-slate-900 shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-blue-700 dark:text-blue-400 border border-slate-200 dark:border-slate-700' ?>">
                                <span class="font-black text-xs">E</span>
                                <span class="text-[11px] font-medium hidden sm:inline">Exc</span>
                            </button>
                        </div>

                        <!-- Remarks Input -->
                        <input type="text"
                               name="attendance[<?= $stu['id'] ?>][remarks]"
                               value="<?= $e($stu['current_remarks'] ?? '') ?>"
                               placeholder="Note..."
                               class="w-24 sm:w-32 px-2.5 py-1 text-xs rounded-lg glass-input">
                    </div>

                </div>
            <?php endforeach; ?>
        </div>

        <!-- Sticky Save Action Bar -->
        <div class="sticky bottom-16 md:bottom-4 z-30 p-4 rounded-2xl bg-white/95 dark:bg-slate-900/95 border border-slate-200 dark:border-slate-800 shadow-xl flex items-center justify-between backdrop-blur-md">
            <div class="text-xs text-slate-500 dark:text-slate-400">
                Recording for <strong class="text-slate-800 dark:text-slate-200"><?= count($students) ?> Students</strong> on <strong class="text-slate-800 dark:text-slate-200"><?= date('M d, Y', strtotime($date)) ?></strong>
            </div>
            <button type="submit" class="px-6 py-2.5 rounded-xl text-xs font-bold text-white shadow-sm flex items-center gap-2 transition-all cursor-pointer <?= $isSaved ? 'bg-slate-800 hover:bg-slate-700 dark:bg-slate-700 dark:hover:bg-slate-600' : 'bg-indigo-600 hover:bg-indigo-500 shadow-indigo-600/25' ?>">
                <i data-lucide="<?= $isSaved ? 'check' : 'save' ?>" class="w-4 h-4"></i>
                <span><?= $isSaved ? 'Update Saved Roll Call' : 'Save Roll Call Sheet' ?></span>
            </button>
        </div>

    </form>

</div>

<!-- Modal / Drawer: Student Previous Attendance Records -->
<div id="modal-student-attendance-history" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
    <div class="glass-card w-full max-w-lg p-6 space-y-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xl rounded-2xl max-h-[90vh] flex flex-col">

        <!-- Modal Header -->
        <div class="flex items-start justify-between pb-3 border-b border-slate-100 dark:border-slate-800 flex-shrink-0">
            <div>
                <div class="flex items-center gap-2">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white" id="modal-student-name">Student History</h3>
                    <span class="font-mono text-xs font-bold text-indigo-600 dark:text-indigo-400" id="modal-student-admin-id"></span>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Previous attendance record log and trend analysis</p>
            </div>
            <button type="button" onclick="closeStudentAttendanceHistory()" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Student Summary Cards -->
        <div class="grid grid-cols-4 gap-2 text-center text-xs flex-shrink-0" id="modal-stats-grid">
            <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700">
                <span class="text-[10px] text-slate-400 uppercase font-bold block">Rate</span>
                <span class="font-black text-sm text-indigo-600 dark:text-indigo-400 tabular-nums" id="modal-stat-rate">-</span>
            </div>
            <div class="p-2.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800/40">
                <span class="text-[10px] text-emerald-600 uppercase font-bold block">Present</span>
                <span class="font-black text-sm text-emerald-600 tabular-nums" id="modal-stat-p">-</span>
            </div>
            <div class="p-2.5 rounded-xl bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-800/40">
                <span class="text-[10px] text-rose-600 uppercase font-bold block">Absent</span>
                <span class="font-black text-sm text-rose-600 tabular-nums" id="modal-stat-a">-</span>
            </div>
            <div class="p-2.5 rounded-xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/40">
                <span class="text-[10px] text-amber-600 uppercase font-bold block">Late</span>
                <span class="font-black text-sm text-amber-600 tabular-nums" id="modal-stat-l">-</span>
            </div>
        </div>

        <!-- Scrollable History Log Table -->
        <div class="flex-1 overflow-y-auto min-h-0 border border-slate-200 dark:border-slate-800 rounded-xl">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/80 sticky top-0 text-[10px] uppercase font-bold text-slate-500">
                    <tr>
                        <th class="py-2 px-3">Date</th>
                        <th class="py-2 px-3 text-center">Status</th>
                        <th class="py-2 px-3">Remarks / Note</th>
                    </tr>
                </thead>
                <tbody id="modal-history-tbody" class="divide-y divide-slate-100 dark:divide-slate-800">
                    <tr>
                        <td colspan="3" class="py-6 text-center text-slate-400 italic">Loading attendance logs...</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Footer Actions -->
        <div class="pt-2 flex items-center justify-between border-t border-slate-100 dark:border-slate-800 flex-shrink-0">
            <a href="#" id="modal-print-student-link" target="_blank" class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                <span>Print Student A4 Attendance Dossier</span>
            </a>
            <button type="button" onclick="closeStudentAttendanceHistory()" class="px-4 py-1.5 text-xs font-bold rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700">
                Close
            </button>
        </div>

    </div>
</div>

<script>
    async function openStudentAttendanceHistory(studentId, studentName, adminId) {
        document.getElementById('modal-student-name').innerText = studentName;
        document.getElementById('modal-student-admin-id').innerText = adminId;
        document.getElementById('modal-print-student-link').href = '/attendance/print/report?class_id=<?= $class['id'] ?>&scope=single&student_id=' + studentId;

        const tbody = document.getElementById('modal-history-tbody');
        tbody.innerHTML = '<tr><td colspan="3" class="py-6 text-center text-slate-400 italic">Fetching previous attendance records...</td></tr>';

        document.getElementById('modal-student-attendance-history').classList.remove('hidden');

        try {
            const res = await fetch('/api/students/' + studentId + '/attendance-history', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();
            if (data && data.success) {
                const s = data.student;
                document.getElementById('modal-stat-rate').innerText = s.rate + '%';
                document.getElementById('modal-stat-p').innerText = s.counts.present;
                document.getElementById('modal-stat-a').innerText = s.counts.absent;
                document.getElementById('modal-stat-l').innerText = s.counts.late;

                if (!data.history || !data.history.length) {
                    tbody.innerHTML = '<tr><td colspan="3" class="py-6 text-center text-slate-400 italic">No attendance records recorded for this student yet.</td></tr>';
                    return;
                }

                let html = '';
                data.history.forEach(row => {
                    const st = (row.status || 'P').toUpperCase();
                    let badge = '';
                    if (st === 'P') badge = '<span class="px-2 py-0.5 rounded font-bold text-[10px] bg-emerald-100 text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-300">PRESENT</span>';
                    else if (st === 'A') badge = '<span class="px-2 py-0.5 rounded font-bold text-[10px] bg-rose-100 text-rose-800 dark:bg-rose-950/50 dark:text-rose-300">ABSENT</span>';
                    else if (st === 'L') badge = '<span class="px-2 py-0.5 rounded font-bold text-[10px] bg-amber-100 text-amber-800 dark:bg-amber-950/50 dark:text-amber-300">LATE</span>';
                    else if (st === 'E') badge = '<span class="px-2 py-0.5 rounded font-bold text-[10px] bg-blue-100 text-blue-800 dark:bg-blue-950/50 dark:text-blue-300">EXCUSED</span>';

                    html += `
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                            <td class="py-2 px-3 font-mono text-[11px] text-slate-600 dark:text-slate-300">${row.attendance_date}</td>
                            <td class="py-2 px-3 text-center">${badge}</td>
                            <td class="py-2 px-3 text-slate-500 italic text-[11px]">${row.remarks ? escapeHtml(row.remarks) : '-'}</td>
                        </tr>
                    `;
                });
                tbody.innerHTML = html;
            }
        } catch (e) {
            tbody.innerHTML = '<tr><td colspan="3" class="py-6 text-center text-rose-500 italic">Failed to load records.</td></tr>';
        }
    }

    function closeStudentAttendanceHistory() {
        document.getElementById('modal-student-attendance-history').classList.add('hidden');
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
</script>
