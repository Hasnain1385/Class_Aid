<div class="space-y-6">

    <!-- Active in Trial Mode (Awaiting Admin Payment Verification) -->
    <?php if (!empty($userSubscription['pending_plan'])): ?>
        <?php 
            $pPlan = \App\Models\Subscription::getPlan($userSubscription['pending_plan']);
        ?>
        <div class="p-4 sm:p-5 rounded-2xl border border-indigo-500/30 bg-indigo-50/80 dark:bg-slate-900 text-slate-900 dark:text-white flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-lg">
            <div class="flex items-start gap-3.5 min-w-0">
                <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center flex-shrink-0 shadow-md shadow-indigo-600/30">
                    <i data-lucide="sparkles" class="w-5 h-5"></i>
                </div>
                <div class="min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <h3 class="font-bold text-sm text-slate-900 dark:text-white">
                            Workspace Unlocked — 14-Day Full Trial Active
                        </h3>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase bg-amber-500/20 text-amber-700 dark:text-amber-300 border border-amber-500/40">
                            Verification Pending
                        </span>
                    </div>
                    <p class="text-xs text-slate-600 dark:text-slate-300 mt-1 leading-relaxed">
                        You have full, unrestricted access to all Class Aid features while your payment for 
                        <strong class="text-indigo-600 dark:text-indigo-400"><?= $e($pPlan['name'] ?? 'Selected Plan') ?></strong> 
                        (Reference: <span class="font-mono font-bold text-slate-900 dark:text-white"><?= $e($userSubscription['payment_reference'] ?? 'TRX') ?></span>) 
                        is verified by our administration. Once approved, your account will automatically switch!
                    </p>
                </div>
            </div>
            <a href="/subscription" class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 shadow-sm flex-shrink-0 transition-colors whitespace-nowrap">
                Billing & Receipt &rarr;
            </a>
        </div>
    <?php endif; ?>

    <!-- Welcome Hero & Quick Action Cockpit -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-2 border-b border-slate-200 dark:border-slate-800">
        <div class="min-w-0">
            <div class="flex items-center gap-2.5 flex-wrap">
                <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                    Teacher Cockpit
                </h1>
                <?php if ($homeClass): ?>
                    <span class="text-xs px-2.5 py-1 rounded-full bg-indigo-500/15 text-indigo-700 dark:text-indigo-300 border border-indigo-500/30 font-semibold">
                        Home Class: <?= $e($homeClass['name']) ?> - <?= $e($homeClass['section']) ?>
                    </span>
                <?php endif; ?>
            </div>
            <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 mt-1">
                Overview of your classes, attendance alerts, and parental communications.
            </p>
        </div>

        <!-- Quick Action Header Bar -->
        <div class="flex items-center gap-2 flex-wrap flex-shrink-0">
            <?php if ($homeClass): ?>
                <a href="/attendance/sheet/<?= $homeClass['id'] ?>" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-500 shadow-sm transition-all whitespace-nowrap">
                    <i data-lucide="check-square" class="w-4 h-4"></i>
                    <span>Take Today's Roll Call</span>
                </a>
            <?php endif; ?>
            <a href="/calls/create" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-800 dark:text-slate-200 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:border-emerald-500/50 shadow-sm transition-all whitespace-nowrap">
                <i data-lucide="phone-outgoing" class="w-4 h-4 text-emerald-500"></i>
                <span>Log Parent Call</span>
            </a>
            <a href="/students/create" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-800 dark:text-slate-200 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:border-violet-500/50 shadow-sm transition-all whitespace-nowrap">
                <i data-lucide="user-plus" class="w-4 h-4 text-violet-500"></i>
                <span>Add Student</span>
            </a>
        </div>
    </div>

    <!-- Chronic Absenteeism Priority Alert -->
    <?php if (!empty($flaggedStudents)): ?>
        <div class="p-4 sm:p-5 rounded-2xl bg-rose-500/10 border border-rose-500/30">
            <div class="flex items-start sm:items-center justify-between gap-3 mb-3 flex-wrap">
                <div class="flex items-center gap-2.5 text-rose-700 dark:text-rose-400 font-bold text-sm">
                    <div class="p-1.5 rounded-lg bg-rose-500/20 text-rose-600 dark:text-rose-300">
                        <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                    </div>
                    <span>Chronic Absenteeism Alert (<?= count($flaggedStudents) ?> Student<?= count($flaggedStudents) > 1 ? 's' : '' ?> with 3+ Consecutive Absences)</span>
                </div>
                <span class="text-xs px-2.5 py-0.5 rounded-full bg-rose-500/20 text-rose-700 dark:text-rose-300 font-bold uppercase tracking-wider">Immediate Follow-up</span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                <?php foreach ($flaggedStudents as $stu): ?>
                    <div class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-rose-500/20 flex items-center justify-between shadow-sm gap-2">
                        <div class="min-w-0 flex-1">
                            <div class="text-xs font-bold text-slate-900 dark:text-white truncate">
                                <?= $e($stu['first_name'] . ' ' . $stu['last_name']) ?>
                            </div>
                            <div class="text-xs text-rose-600 dark:text-rose-400 font-semibold flex items-center gap-1 mt-0.5">
                                <span class="font-mono"><?= $e($stu['student_admin_id']) ?></span>
                                <span>•</span>
                                <span><?= $stu['consecutive_absences'] ?> Days Absent</span>
                            </div>
                            <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 truncate">
                                Parent: <?= $e($stu['primary_guardian_name']) ?> (<?= $e($stu['primary_phone']) ?>)
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5 flex-shrink-0">
                            <button type="button" 
                                    onclick="openWhatsApp('<?= $e($stu['primary_phone']) ?>', '<?= $e($stu['first_name'] . ' ' . $stu['last_name']) ?>', '<?= $e($stu['student_admin_id']) ?>')"
                                    class="p-2 rounded-lg bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 transition-colors" 
                                    title="WhatsApp Parent">
                                <i data-lucide="message-circle" class="w-4 h-4"></i>
                            </button>
                            <a href="/calls/create?student_id=<?= $stu['id'] ?>&category=Absenteeism" 
                               class="p-2 rounded-lg bg-rose-500/10 hover:bg-rose-500/20 text-rose-600 dark:text-rose-400 transition-colors" 
                               title="Log Call Now">
                                <i data-lucide="phone-call" class="w-4 h-4"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- 4 Key High-Contrast Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Total Students -->
        <div class="glass-card p-4 sm:p-5 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Enrolled</p>
                <h3 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tabular-nums tracking-tight mt-1"><?= $totalStudents ?></h3>
                <p class="text-xs text-indigo-600 dark:text-indigo-400 mt-1 font-semibold">Across <?= count($classes) ?> Classes</p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center flex-shrink-0">
                <i data-lucide="users" class="w-5 h-5"></i>
            </div>
        </div>

        <!-- Today's Attendance -->
        <div class="glass-card p-4 sm:p-5 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Today's Attendance</p>
                <h3 class="text-2xl sm:text-3xl font-extrabold text-emerald-600 dark:text-emerald-400 tabular-nums tracking-tight mt-1">
                    <?= $todayAttendance['rate'] ?>%
                </h3>
                <p class="text-xs text-slate-600 dark:text-slate-400 mt-1 font-medium">
                    <?= $todayAttendance['present'] ?> Present • <?= $todayAttendance['absent'] ?> Absent
                </p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center flex-shrink-0">
                <i data-lucide="calendar-check" class="w-5 h-5"></i>
            </div>
        </div>

        <!-- Pending Call Follow-ups -->
        <div class="glass-card p-4 sm:p-5 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Call Follow-ups</p>
                <h3 class="text-2xl sm:text-3xl font-extrabold text-amber-600 dark:text-amber-400 tabular-nums tracking-tight mt-1"><?= count($pendingFollowups) ?></h3>
                <p class="text-xs text-slate-600 dark:text-slate-400 mt-1 font-medium">Pending parent actions</p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center flex-shrink-0">
                <i data-lucide="phone-missed" class="w-5 h-5"></i>
            </div>
        </div>

        <!-- Managed Classes -->
        <div class="glass-card p-4 sm:p-5 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Managed Rosters</p>
                <h3 class="text-2xl sm:text-3xl font-extrabold text-violet-600 dark:text-violet-400 tabular-nums tracking-tight mt-1"><?= count($classes) ?></h3>
                <p class="text-xs text-slate-600 dark:text-slate-400 mt-1 font-medium">1 Home + 4 Subject</p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-violet-500/10 text-violet-600 dark:text-violet-400 flex items-center justify-center flex-shrink-0">
                <i data-lucide="school" class="w-5 h-5"></i>
            </div>
        </div>

    </div>

    <!-- Main 2-Column Split: Classes Overview & Recent Parent Interactions -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Column 1: Classes & Quick Roll Call (1 col on desktop) -->
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center gap-2">
                    <i data-lucide="book-open" class="w-4 h-4 text-indigo-500"></i>
                    <span>Your Class Rosters</span>
                </h2>
                <a href="/classes" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">Manage &rarr;</a>
            </div>

            <div class="space-y-3">
                <?php foreach ($classes as $c): ?>
                    <div class="glass-card p-4 flex items-center justify-between gap-3 <?= $c['is_home_class'] ? 'border-l-4 border-indigo-600 dark:border-indigo-500' : '' ?>">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-bold text-sm text-slate-900 dark:text-white truncate"><?= $e($c['name']) ?> - <?= $e($c['section']) ?></span>
                                <?php if ($c['is_home_class']): ?>
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-indigo-500/20 text-indigo-700 dark:text-indigo-300 font-bold uppercase">Home</span>
                                <?php endif; ?>
                            </div>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                <?= $c['student_count'] ?> Students enrolled • AY <?= $e($c['academic_year']) ?>
                            </p>
                        </div>
                        <div class="flex items-center gap-1.5 flex-shrink-0">
                            <a href="/attendance/sheet/<?= $c['id'] ?>" class="p-2 rounded-lg bg-slate-100 hover:bg-indigo-500/10 text-slate-700 hover:text-indigo-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:text-indigo-300 transition-colors" title="Attendance">
                                <i data-lucide="calendar" class="w-4 h-4"></i>
                            </a>
                            <a href="/students?class_id=<?= $c['id'] ?>" class="p-2 rounded-lg bg-slate-100 hover:bg-indigo-500/10 text-slate-700 hover:text-indigo-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:text-indigo-300 transition-colors" title="Students Roster">
                                <i data-lucide="users" class="w-4 h-4"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Quick Reminders & Tasks Widget -->
            <div class="glass-card p-4 space-y-3">
                <div class="flex items-center justify-between pb-2 border-b border-slate-200 dark:border-slate-800">
                    <h3 class="font-bold text-xs uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                        <i data-lucide="calendar-clock" class="w-3.5 h-3.5 text-indigo-500"></i>
                        <span>Reminders & Tasks</span>
                    </h3>
                    <a href="/reminders" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">Manage &rarr;</a>
                </div>

                <?php if (empty($reminders)): ?>
                    <p class="text-xs text-slate-500 dark:text-slate-400 italic py-2">No pending tasks or deadlines.</p>
                <?php else: ?>
                    <div class="space-y-2">
                        <?php foreach ($reminders as $rem): ?>
                            <div class="p-2.5 rounded-lg bg-slate-50 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700/60 flex items-center justify-between gap-2 text-xs">
                                <div class="min-w-0 flex-1 truncate">
                                    <div class="font-semibold text-slate-900 dark:text-white truncate"><?= $e($rem['title']) ?></div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400 font-mono mt-0.5"><?= date('M d', strtotime($rem['due_date'])) ?></div>
                                </div>
                                <form method="POST" action="/reminders/<?= $rem['id'] ?>/toggle" class="flex-shrink-0">
                                    <?= $csrf() ?>
                                    <button type="submit" class="p-1 rounded hover:bg-emerald-500/10 text-slate-400 hover:text-emerald-500 transition-colors" title="Mark Done">
                                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                    </button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Column 2 & 3: Recent Parent Call Interactions (2 cols on desktop) -->
        <div class="lg:col-span-2 space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center gap-2">
                    <i data-lucide="phone-call" class="w-4 h-4 text-emerald-500"></i>
                    <span>Recent Parent Communication Log</span>
                </h2>
                <a href="/calls" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">View All Log &rarr;</a>
            </div>

            <?php if (empty($recentCalls)): ?>
                <div class="glass-card p-8 text-center text-slate-500 dark:text-slate-400 text-xs">
                    <i data-lucide="phone-off" class="w-8 h-8 mx-auto mb-2 text-slate-400 opacity-50"></i>
                    No parent communications recorded yet. Click "Log Parent Call" to document your first call.
                </div>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($recentCalls as $call): ?>
                        <div class="glass-card p-4 transition-all hover:border-indigo-500/40">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2.5 mb-2.5 border-b border-slate-200 dark:border-slate-800">
                                <div>
                                    <span class="font-bold text-sm text-slate-900 dark:text-white">
                                        <?= $e($call['first_name'] . ' ' . $call['last_name']) ?>
                                    </span>
                                    <span class="text-xs text-indigo-600 dark:text-indigo-400 font-mono font-semibold ml-1.5">(<?= $e($call['student_admin_id']) ?>)</span>
                                    <span class="text-xs text-slate-500 dark:text-slate-400 ml-1">• <?= $e($call['class_name']) ?>-<?= $e($call['class_section']) ?></span>
                                </div>
                                <div class="flex items-center gap-2 flex-shrink-0">
                                    <?= \App\Models\ParentCallLog::renderPill($call['call_category']) ?>
                                    <span class="text-xs text-slate-500 dark:text-slate-400 font-mono">
                                        <?= date('M d, H:i', strtotime($call['call_timestamp'])) ?>
                                    </span>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                                <div>
                                    <span class="text-slate-500 dark:text-slate-400 block text-xs uppercase font-bold tracking-wider mb-0.5">Teacher Discussion Notes:</span>
                                    <p class="text-slate-800 dark:text-slate-200 leading-relaxed"><?= $e($call['discussion_summary']) ?></p>
                                </div>
                                <div>
                                    <span class="text-slate-500 dark:text-slate-400 block text-xs uppercase font-bold tracking-wider mb-0.5">Parent Commitment / Response:</span>
                                    <p class="text-slate-800 dark:text-slate-200 leading-relaxed italic font-medium">"<?= $e($call['parent_response']) ?>"</p>
                                </div>
                            </div>

                            <div class="mt-3 pt-2.5 flex items-center justify-between text-xs border-t border-slate-200 dark:border-slate-800 flex-wrap gap-2">
                                <div class="flex items-center gap-2 text-slate-600 dark:text-slate-400">
                                    <span>Contact: <strong class="text-slate-900 dark:text-slate-200 font-semibold"><?= $e($call['contact_person_name']) ?></strong> (<?= $e($call['relationship']) ?>)</span>
                                    <span>•</span>
                                    <span class="font-mono"><?= $e($call['phone_dialed']) ?></span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <button type="button" 
                                            onclick="openWhatsApp('<?= $e($call['phone_dialed']) ?>', '<?= $e($call['first_name'] . ' ' . $call['last_name']) ?>', '<?= $e($call['student_admin_id']) ?>')"
                                            class="inline-flex items-center gap-1 text-emerald-600 dark:text-emerald-400 hover:underline font-semibold">
                                        <i data-lucide="message-circle" class="w-3.5 h-3.5"></i>
                                        <span>WhatsApp</span>
                                    </button>
                                    <a href="/students/<?= $call['student_id'] ?>" class="text-indigo-600 dark:text-indigo-400 hover:underline font-semibold">
                                        View Dossier &rarr;
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>

    <!-- Sliding Popup Announcements (Only on Front Page / Dashboard - Sliding from Top-Right Corner) -->
    <?php if (!empty($activeAnnouncements)): ?>
        <div id="sliding-toasts-container" class="fixed top-20 right-4 sm:right-6 z-50 max-w-sm w-full space-y-3 pointer-events-none transition-all duration-300">
            <!-- Header bar with "Mark all as read" -->
            <div id="toast-header-bar" class="hidden pointer-events-auto p-3 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white shadow-2xl flex items-center justify-between text-xs animate-in fade-in slide-in-from-top-4 duration-300">
                <div class="flex items-center gap-2 font-bold">
                    <span class="w-2 h-2 rounded-full bg-indigo-500 animate-ping"></span>
                    <span>New Broadcasts</span>
                </div>
                <button type="button" onclick="markAllAnnouncementsRead()" class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 hover:underline flex items-center gap-1 cursor-pointer">
                    <i data-lucide="check-check" class="w-3.5 h-3.5"></i>
                    <span>Mark all read</span>
                </button>
            </div>

            <!-- Toast Cards List -->
            <div id="toast-cards-list" class="space-y-2.5 pointer-events-auto">
                <?php foreach ($activeAnnouncements as $ann): ?>
                    <?php 
                        $annMeta = \App\Models\Announcement::TYPES[$ann['type']] ?? \App\Models\Announcement::TYPES['info'];
                    ?>
                    <div id="ann-toast-<?= $ann['id'] ?>" 
                         data-id="<?= $ann['id'] ?>"
                         class="ann-toast hidden transform translate-x-full opacity-0 transition-all duration-500 ease-out p-4 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white/95 dark:bg-slate-900/95 backdrop-blur-md shadow-2xl flex items-start justify-between gap-3 cursor-pointer group hover:border-indigo-500/60">
                        <div class="flex items-start gap-2.5 flex-1 min-w-0" onclick="openGlobalAnnouncementModal(<?= htmlspecialchars(json_encode($ann), ENT_QUOTES, 'UTF-8') ?>)">
                            <span class="text-xs px-2 py-0.5 rounded-full font-bold uppercase mt-0.5 flex-shrink-0 <?= $annMeta['badge'] ?>">
                                <?= $e($annMeta['label']) ?>
                            </span>
                            <div class="flex-1 min-w-0">
                                <h4 class="font-bold text-xs text-slate-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors truncate"><?= $e($ann['title']) ?></h4>
                                <p class="text-xs text-slate-600 dark:text-slate-300 mt-0.5 line-clamp-2 leading-relaxed"><?= $e($ann['content']) ?></p>
                                <span class="text-xs text-indigo-600 dark:text-indigo-400 font-semibold mt-1 inline-block">Click to view full notice &rarr;</span>
                            </div>
                        </div>
                        <button type="button" onclick="dismissSingleToast(<?= $ann['id'] ?>, event)" class="opacity-60 hover:opacity-100 flex-shrink-0 p-1 text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors cursor-pointer" title="Dismiss">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                initSlidingAnnouncements();
            });

            function initSlidingAnnouncements() {
                const readKey = 'classaid_read_announcements';
                let readIds = [];
                try {
                    readIds = JSON.parse(localStorage.getItem(readKey) || '[]');
                } catch(e) {
                    readIds = [];
                }

                const toasts = document.querySelectorAll('.ann-toast');
                let unreadCount = 0;

                toasts.forEach((t, idx) => {
                    const id = parseInt(t.dataset.id, 10);
                    if (!readIds.includes(id)) {
                        unreadCount++;
                        setTimeout(() => {
                            t.classList.remove('hidden');
                            requestAnimationFrame(() => {
                                t.classList.remove('translate-x-full', 'opacity-0');
                                t.classList.add('translate-x-0', 'opacity-100');
                            });
                        }, (idx + 1) * 200);
                    }
                });

                if (unreadCount > 0) {
                    const header = document.getElementById('toast-header-bar');
                    if (header) header.classList.remove('hidden');
                }
            }

            function dismissSingleToast(id, event) {
                if (event) event.stopPropagation();
                const t = document.getElementById('ann-toast-' + id);
                if (t) {
                    t.classList.remove('translate-x-0', 'opacity-100');
                    t.classList.add('translate-x-full', 'opacity-0');
                    setTimeout(() => t.remove(), 400);
                }
                if (typeof saveReadAnnouncementId === 'function') {
                    saveReadAnnouncementId(parseInt(id, 10));
                }
                checkRemainingToasts();
            }

            function markAllAnnouncementsRead() {
                const toasts = document.querySelectorAll('.ann-toast');
                toasts.forEach(t => {
                    const id = parseInt(t.dataset.id, 10);
                    if (typeof saveReadAnnouncementId === 'function') {
                        saveReadAnnouncementId(id);
                    }
                    t.classList.remove('translate-x-0', 'opacity-100');
                    t.classList.add('translate-x-full', 'opacity-0');
                    setTimeout(() => t.remove(), 400);
                });
                const header = document.getElementById('toast-header-bar');
                if (header) header.classList.add('hidden');
                if (typeof refreshNotificationBadges === 'function') {
                    refreshNotificationBadges();
                }
            }

            function checkRemainingToasts() {
                setTimeout(() => {
                    const remaining = document.querySelectorAll('.ann-toast:not(.hidden)');
                    if (remaining.length === 0) {
                        const header = document.getElementById('toast-header-bar');
                        if (header) header.classList.add('hidden');
                    }
                }, 450);
            }
        </script>
    <?php endif; ?>

</div>
