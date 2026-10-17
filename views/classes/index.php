<div class="space-y-6">

    <!-- Header & Action Ribbon -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white flex items-center gap-2">
                <i data-lucide="book-open" class="w-6 h-6 text-indigo-500"></i>
                <span>Class Rosters & Subjects</span>
            </h1>
            <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                Manage your primary Home Class and other 4 subject classes, with multi-subject curriculum assignments.
            </p>
        </div>

        <button type="button" 
                onclick="document.getElementById('modal-create-class').classList.remove('hidden')"
                class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-500 shadow-md shadow-indigo-600/30 transition-all">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Create New Class</span>
        </button>
    </div>

    <!-- Class Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <?php foreach ($classes as $c): ?>
            <div class="glass-card p-5 space-y-4 <?= $c['is_home_class'] ? 'border-2 border-indigo-600/50 bg-indigo-50/10 dark:bg-indigo-950/20' : '' ?>">
                
                <div class="flex items-start justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-lg font-bold text-gray-900 dark:text-white">
                                <?= $e($c['name']) ?> - <?= $e($c['section']) ?>
                            </h2>
                            <?php if ($c['is_home_class']): ?>
                                <span class="text-[10px] px-2 py-0.5 rounded-full bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 font-bold uppercase">
                                    Primary Home Class
                                </span>
                            <?php endif; ?>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            Academic Year: <?= $e($c['academic_year']) ?> • <?= $c['student_count'] ?> Enrolled Students
                        </p>
                    </div>

                    <div class="flex items-center gap-1">
                        <a href="/attendance/sheet/<?= $c['id'] ?>" class="p-2 rounded-lg bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-500/20" title="Roll Call">
                            <i data-lucide="calendar-check" class="w-4 h-4"></i>
                        </a>
                        <a href="/students?class_id=<?= $c['id'] ?>" class="p-2 rounded-lg bg-gray-100 dark:bg-white/10 text-gray-700 dark:text-gray-300 hover:bg-gray-200" title="Students">
                            <i data-lucide="users" class="w-4 h-4"></i>
                        </a>
                    </div>
                </div>

                <!-- Subjects taught in this class -->
                <div class="pt-3 border-t border-gray-100 dark:border-white/5">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Assigned Subjects (<?= count($c['subjects']) ?>)
                        </span>
                        <button type="button" 
                                onclick="openAddSubjectModal(<?= $c['id'] ?>, '<?= $e($c['name'] . ' ' . $c['section']) ?>')" 
                                class="text-[11px] text-indigo-600 dark:text-indigo-400 hover:underline font-semibold flex items-center gap-0.5">
                            <i data-lucide="plus" class="w-3 h-3"></i>
                            <span>Add Subject</span>
                        </button>
                    </div>

                    <div class="flex flex-wrap gap-1.5">
                        <?php if (empty($c['subjects'])): ?>
                            <span class="text-xs text-gray-400 italic">No specific subjects defined yet.</span>
                        <?php else: ?>
                            <?php foreach ($c['subjects'] as $sub): ?>
                                <span class="px-2.5 py-1 rounded-lg bg-gray-100 dark:bg-white/10 text-xs font-medium text-gray-800 dark:text-gray-200 border border-gray-200/50 dark:border-white/5 flex items-center gap-1.5">
                                    <span><?= $e($sub['name']) ?></span>
                                    <?php if (!empty($sub['code'])): ?>
                                        <span class="font-mono text-[10px] text-indigo-500 font-semibold"><?= $e($sub['code']) ?></span>
                                    <?php endif; ?>
                                </span>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        <?php endforeach; ?>
    </div>

</div>

<!-- Modal: Create New Class -->
<div id="modal-create-class" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
    <div class="glass-card w-full max-w-md p-6 space-y-4 bg-white/95 dark:bg-slate-900/95">
        <div class="flex items-center justify-between">
            <h3 class="text-base font-bold text-gray-900 dark:text-white">Create New Class Roster</h3>
            <button type="button" onclick="document.getElementById('modal-create-class').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form method="POST" action="/classes" class="space-y-4">
            <?= $csrf() ?>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Class Name *</label>
                    <input type="text" name="name" required placeholder="e.g. Grade 10" class="w-full px-3 py-2 text-xs rounded-xl glass-input">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Section *</label>
                    <input type="text" name="section" required placeholder="e.g. A" class="w-full px-3 py-2 text-xs rounded-xl glass-input">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Academic Year</label>
                <input type="text" name="academic_year" value="<?= date('Y') ?>-<?= date('Y') + 1 ?>" class="w-full px-3 py-2 text-xs rounded-xl glass-input font-mono">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Initial Subject</label>
                    <input type="text" name="initial_subject" placeholder="e.g. Mathematics" class="w-full px-3 py-2 text-xs rounded-xl glass-input">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Subject Code</label>
                    <input type="text" name="initial_code" placeholder="e.g. MTH-10" class="w-full px-3 py-2 text-xs rounded-xl glass-input font-mono">
                </div>
            </div>

            <div class="flex items-center gap-2 pt-1">
                <input type="checkbox" name="is_home_class" value="1" id="modal_is_home" class="rounded text-indigo-600">
                <label for="modal_is_home" class="text-xs text-gray-700 dark:text-gray-300 cursor-pointer">
                    Designate as Primary Home Class
                </label>
            </div>

            <div class="pt-2 flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('modal-create-class').classList.add('hidden')" class="px-4 py-2 text-xs rounded-xl text-gray-600 hover:bg-gray-100 dark:hover:bg-white/10">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 rounded-xl shadow-md">
                    Create Class
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Add Subject -->
<div id="modal-add-subject" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
    <div class="glass-card w-full max-w-sm p-6 space-y-4 bg-white/95 dark:bg-slate-900/95">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-gray-900 dark:text-white">Add Subject</h3>
                <p class="text-xs text-gray-500 mt-0.5" id="add-subject-class-name"></p>
            </div>
            <button type="button" onclick="document.getElementById('modal-add-subject').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form method="POST" action="/classes/subjects" class="space-y-4">
            <?= $csrf() ?>
            <input type="hidden" name="class_id" id="add-subject-class-id">

            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Subject Name *</label>
                <input type="text" name="name" required placeholder="e.g. Computer Science" class="w-full px-3 py-2 text-xs rounded-xl glass-input">
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Subject Code</label>
                <input type="text" name="code" placeholder="e.g. CS-101" class="w-full px-3 py-2 text-xs rounded-xl glass-input font-mono">
            </div>

            <div class="pt-2 flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('modal-add-subject').classList.add('hidden')" class="px-4 py-2 text-xs rounded-xl text-gray-600 hover:bg-gray-100 dark:hover:bg-white/10">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 rounded-xl shadow-md">
                    Save Subject
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openAddSubjectModal(classId, className) {
        document.getElementById('add-subject-class-id').value = classId;
        document.getElementById('add-subject-class-name').innerText = 'To ' + className;
        document.getElementById('modal-add-subject').classList.remove('hidden');
    }
</script>
