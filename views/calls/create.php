<div class="max-w-3xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white flex items-center gap-2">
                <i data-lucide="phone-outgoing" class="w-6 h-6 text-emerald-500"></i>
                <span>Log Parent Communication</span>
            </h1>
            <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                Record interaction details, discussion notes, and parent commitments for institutional auditing.
            </p>
        </div>
        <a href="javascript:history.back()" class="text-xs text-gray-500 hover:text-indigo-500 flex items-center gap-1">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Cancel</span>
        </a>
    </div>

    <form method="POST" action="/calls" class="glass-card p-6 sm:p-8 space-y-6" id="call-log-form">
        <?= $csrf() ?>

        <!-- Student Selector / Target Confirmation -->
        <div>
            <h2 class="text-xs font-bold uppercase tracking-wider text-indigo-500 dark:text-indigo-400 mb-3 flex items-center gap-1.5">
                <i data-lucide="user-check" class="w-4 h-4"></i>
                <span>Student Record</span>
            </h2>

            <?php if ($student): ?>
                <!-- Pre-selected student banner -->
                <input type="hidden" name="student_id" value="<?= $student['id'] ?>">
                <div class="p-4 rounded-xl bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-bold text-gray-900 dark:text-white">
                                <?= $e($student['first_name'] . ' ' . $student['last_name']) ?>
                            </span>
                            <span class="font-mono text-xs text-indigo-500 font-bold">[<?= $e($student['student_admin_id']) ?>]</span>
                            <span class="text-xs text-gray-500">• <?= $e($student['class_name'] ?? '') ?>-<?= $e($student['class_section'] ?? '') ?></span>
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            Guardian: <?= $e($student['primary_guardian_name']) ?> (<?= $e($student['primary_guardian_relation']) ?>) • <?= $e($student['primary_phone']) ?>
                        </div>
                    </div>
                    <a href="/calls/create" class="text-xs text-indigo-500 hover:underline">Change</a>
                </div>
            <?php else: ?>
                <!-- Typeahead live student picker -->
                <div class="relative search-wrapper">
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                        Search & Select Student *
                    </label>
                    <input type="hidden" name="student_id" id="selected-student-id" required>
                    <input type="text" 
                           id="student-picker-input"
                           class="global-student-search w-full px-3.5 py-2.5 text-xs sm:text-sm rounded-xl glass-input placeholder-gray-400"
                           placeholder="Type student name or ID (e.g. ADM-2026-001)...">
                    <div class="search-results-dropdown hidden absolute left-0 right-0 mt-2 glass-card p-1 shadow-2xl max-h-60 overflow-y-auto z-50"></div>
                    <div id="student-selected-badge" class="hidden mt-2 p-3 rounded-xl bg-indigo-500/10 border border-indigo-500/30 text-xs font-semibold text-indigo-600 dark:text-indigo-400 flex items-center justify-between">
                        <span id="student-selected-text"></span>
                        <button type="button" onclick="clearSelectedStudent()" class="text-rose-500 hover:underline">Clear</button>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- 7 Highlighted Call Categories (Priority Feature) -->
        <div class="pt-4 border-t border-gray-100 dark:border-white/5">
            <label class="block text-xs font-bold uppercase tracking-wider text-indigo-500 dark:text-indigo-400 mb-3">
                Select Interaction Category *
            </label>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                <?php foreach ($categories as $catKey => $catData): ?>
                    <?php 
                        $label = is_array($catData) ? ($catData['label'] ?? $catKey) : $catData;
                        $isSelected = ($catKey === $preselectedCategory); 
                    ?>
                    <label class="category-radio-card flex items-center justify-between p-3 rounded-xl border cursor-pointer transition-all <?= $isSelected ? 'border-indigo-600 bg-indigo-50/20 dark:bg-indigo-950/20 font-bold' : 'border-gray-200/60 dark:border-white/10 hover:border-indigo-400' ?>">
                        <input type="radio" 
                               name="call_category" 
                               value="<?= $catKey ?>" 
                               <?= $isSelected ? 'checked' : '' ?>
                               class="sr-only" 
                               onchange="updateCategorySelection(this)">
                        <span class="text-xs text-gray-900 dark:text-white"><?= $e($label) ?></span>
                        <span class="w-2.5 h-2.5 rounded-full <?= \App\Models\ParentCallLog::getCategoryBadgeClass($catKey) ?>"></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Call Metadata: Contact Person, Phone, Date, Type -->
        <div class="pt-4 border-t border-gray-100 dark:border-white/5 space-y-4">
            <h2 class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 flex items-center gap-1.5">
                <i data-lucide="phone" class="w-4 h-4"></i>
                <span>Interaction Details</span>
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Person Contacted *</label>
                    <input type="text" 
                           name="contact_person_name" 
                           id="contact-person-name"
                           value="<?= $student ? $e($student['primary_guardian_name']) : '' ?>" 
                           required 
                           placeholder="e.g. Mrs. Sarah Miller" 
                           class="w-full px-3.5 py-2.5 text-xs sm:text-sm rounded-xl glass-input">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Relationship</label>
                    <input type="text" 
                           name="relationship" 
                           id="contact-relation"
                           value="<?= $student ? $e($student['primary_guardian_relation']) : 'Mother' ?>" 
                           placeholder="Mother, Father, etc." 
                           class="w-full px-3.5 py-2.5 text-xs sm:text-sm rounded-xl glass-input">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Phone Number Dialed *</label>
                    <input type="tel" 
                           name="phone_dialed" 
                           id="phone-dialed"
                           value="<?= $student ? $e($student['primary_phone']) : '' ?>" 
                           required 
                           placeholder="e.g. +1 555-0101" 
                           class="w-full px-3.5 py-2.5 text-xs sm:text-sm rounded-xl glass-input font-mono">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Channel / Medium</label>
                    <select name="call_type" class="w-full px-3.5 py-2.5 text-xs sm:text-sm rounded-xl glass-input">
                        <option value="Phone Call">Phone Call</option>
                        <option value="WhatsApp Audio/Video">WhatsApp Audio/Video</option>
                        <option value="WhatsApp Message">WhatsApp Message</option>
                        <option value="In-Person Meeting">In-Person Meeting</option>
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Date & Time of Call</label>
                    <input type="datetime-local" 
                           name="call_timestamp" 
                           value="<?= date('Y-m-d\TH:i') ?>" 
                           class="w-full px-3.5 py-2.5 text-xs sm:text-sm rounded-xl glass-input">
                </div>
            </div>
        </div>

        <!-- Notes & Parent Response -->
        <div class="pt-4 border-t border-gray-100 dark:border-white/5 space-y-4">
            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                    Teacher Discussion Summary & Concerns *
                </label>
                <textarea name="discussion_summary" 
                          rows="3" 
                          required 
                          placeholder="Summarize what was discussed (e.g. Contacted parent regarding 3 consecutive absences without notice. Discussed upcoming mid-term revisions...)" 
                          class="w-full px-3.5 py-2.5 text-xs sm:text-sm rounded-xl glass-input leading-relaxed"></textarea>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                    Parent Response & Commitment *
                </label>
                <textarea name="parent_response" 
                          rows="3" 
                          required 
                          placeholder="Record exact parent feedback, commitments or explanations (e.g. Mother confirmed student had viral fever; will send medical certificate tomorrow and ensure homework is submitted on Monday...)" 
                          class="w-full px-3.5 py-2.5 text-xs sm:text-sm rounded-xl glass-input leading-relaxed font-medium"></textarea>
            </div>
        </div>

        <!-- Follow-up Scheduling -->
        <div class="pt-4 border-t border-gray-100 dark:border-white/5">
            <div class="flex items-center gap-3 mb-3">
                <input type="checkbox" 
                       name="follow_up_required" 
                       value="1" 
                       id="follow_up_required" 
                       onchange="document.getElementById('follow-up-date-container').classList.toggle('hidden', !this.checked)"
                       class="rounded text-indigo-600 focus:ring-indigo-500">
                <label for="follow_up_required" class="text-xs font-semibold text-gray-800 dark:text-gray-200 cursor-pointer">
                    Flag for Follow-up Action
                </label>
            </div>

            <div id="follow-up-date-container" class="hidden sm:w-1/2">
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Target Follow-up Date</label>
                <input type="date" name="follow_up_date" class="w-full px-3.5 py-2 text-xs rounded-xl glass-input">
            </div>
        </div>

        <!-- Submit Button -->
        <div class="pt-4 flex items-center justify-end gap-3">
            <a href="/calls" class="px-4 py-2.5 rounded-xl text-xs font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/10 transition-colors">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-500 shadow-lg shadow-indigo-600/30 flex items-center gap-2 transition-all">
                <i data-lucide="check" class="w-4 h-4"></i>
                <span>Save Call Record</span>
            </button>
        </div>

    </form>

</div>

<script>
    function updateCategorySelection(radio) {
        document.querySelectorAll('.category-radio-card').forEach(card => {
            card.classList.remove('border-indigo-600', 'bg-indigo-50/20', 'dark:bg-indigo-950/20', 'font-bold');
            card.classList.add('border-gray-200/60', 'dark:border-white/10');
        });
        const parentCard = radio.closest('.category-radio-card');
        if (parentCard) {
            parentCard.classList.add('border-indigo-600', 'bg-indigo-50/20', 'dark:bg-indigo-950/20', 'font-bold');
            parentCard.classList.remove('border-gray-200/60', 'dark:border-white/10');
        }
    }

    // Handle student selection if picker is used
    document.addEventListener('click', (e) => {
        const item = e.target.closest('.search-results-dropdown a');
        if (item && document.getElementById('selected-student-id')) {
            e.preventDefault();
            const href = item.getAttribute('href');
            const studentId = href.split('/').pop();
            const nameEl = item.querySelector('.font-semibold');
            const idEl = item.querySelector('.font-mono');

            document.getElementById('selected-student-id').value = studentId;
            document.getElementById('student-selected-text').innerText = nameEl.innerText + ' (' + idEl.innerText + ')';
            document.getElementById('student-selected-badge').classList.remove('hidden');
            document.getElementById('student-picker-input').classList.add('hidden');
            item.closest('.search-results-dropdown').classList.add('hidden');
        }
    });

    function clearSelectedStudent() {
        document.getElementById('selected-student-id').value = '';
        document.getElementById('student-selected-badge').classList.add('hidden');
        document.getElementById('student-picker-input').classList.remove('hidden');
        document.getElementById('student-picker-input').value = '';
    }
</script>
