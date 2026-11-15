<div class="max-w-3xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white flex items-center gap-2">
                <i data-lucide="phone-outgoing" class="w-6 h-6 text-emerald-500"></i>
                <span>Log Parent Communication</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                Record interaction details, discussion notes, and parent commitments for institutional auditing.
            </p>
        </div>
        <a href="javascript:history.back()" class="text-xs text-slate-500 dark:text-slate-400 hover:text-indigo-500 flex items-center gap-1 transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Cancel</span>
        </a>
    </div>

    <form method="POST" action="/calls" class="glass-card p-6 sm:p-8 space-y-6" id="call-log-form">
        <?= $csrf() ?>

        <!-- Student Selector / Target Confirmation -->
        <div>
            <h2 class="text-xs font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400 mb-3 flex items-center gap-1.5">
                <i data-lucide="user-check" class="w-4 h-4"></i>
                <span>Student Record</span>
            </h2>

            <?php if ($student): ?>
                <!-- Pre-selected student banner -->
                <input type="hidden" name="student_id" value="<?= $student['id'] ?>">
                <div class="p-4 rounded-xl bg-indigo-50/50 dark:bg-indigo-950/20 border border-indigo-200 dark:border-indigo-900/50 flex items-center justify-between">
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-sm font-bold text-slate-900 dark:text-white">
                                <?= $e($student['first_name'] . ' ' . $student['last_name']) ?>
                            </span>
                            <span class="font-mono text-xs text-indigo-500 font-bold tabular-nums">[<?= $e($student['student_admin_id']) ?>]</span>
                            <span class="text-xs text-slate-500 dark:text-slate-400">• <?= $e($student['class_name'] ?? '') ?>-<?= $e($student['class_section'] ?? '') ?></span>
                        </div>
                        <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                            Guardian: <strong class="text-slate-700 dark:text-slate-300"><?= $e($student['primary_guardian_name']) ?></strong> (<?= $e($student['primary_guardian_relation']) ?>) • <span class="font-mono tabular-nums"><?= $e($student['primary_phone']) ?></span>
                        </div>
                    </div>
                    <a href="/calls/create" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline font-semibold">Change</a>
                </div>
            <?php else: ?>
                <!-- Typeahead live student picker -->
                <div class="relative search-wrapper">
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        Search & Select Student *
                    </label>
                    <input type="hidden" name="student_id" id="selected-student-id" required>
                    <input type="text" 
                           id="student-picker-input"
                           class="global-student-search w-full px-3.5 py-2.5 text-xs sm:text-sm rounded-xl glass-input placeholder-slate-400"
                           placeholder="Type student name or ID (e.g. ADM-2026-001)...">
                    <div class="search-results-dropdown hidden absolute left-0 right-0 mt-2 glass-card p-1 shadow-2xl max-h-60 overflow-y-auto z-50"></div>
                    <div id="student-selected-badge" class="hidden mt-2 p-3 rounded-xl bg-indigo-50/50 dark:bg-indigo-950/20 border border-indigo-200 dark:border-indigo-900/50 text-xs font-semibold text-indigo-600 dark:text-indigo-400 flex items-center justify-between">
                        <span id="student-selected-text"></span>
                        <button type="button" onclick="clearSelectedStudent()" class="text-rose-500 hover:underline">Clear</button>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- 7 Highlighted Call Categories -->
        <div class="pt-4 border-t border-slate-100 dark:border-slate-800">
            <label class="block text-xs font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400 mb-3">
                Select Interaction Category *
            </label>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                <?php foreach ($categories as $catKey => $catData): ?>
                    <?php 
                        $label = is_array($catData) ? ($catData['label'] ?? $catKey) : $catData;
                        $isSelected = ($catKey === $preselectedCategory); 
                    ?>
                    <label class="category-radio-card flex items-center justify-between p-3 rounded-xl border cursor-pointer transition-all <?= $isSelected ? 'border-indigo-600 bg-indigo-50/40 dark:bg-indigo-950/30 font-bold' : 'border-slate-200 dark:border-slate-800 hover:border-indigo-400 dark:hover:border-slate-700' ?>">
                        <input type="radio" 
                               name="call_category" 
                               value="<?= $catKey ?>" 
                               <?= $isSelected ? 'checked' : '' ?>
                               class="sr-only" 
                               onchange="updateCategorySelection(this)">
                        <span class="text-xs text-slate-800 dark:text-slate-200"><?= $e($label) ?></span>
                        <span class="w-2.5 h-2.5 rounded-full <?= $catData['dot'] ?? 'bg-indigo-500' ?>"></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Call Metadata: Contact Person, Phone, Date, Type -->
        <div class="pt-4 border-t border-slate-100 dark:border-slate-800 space-y-4">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                <i data-lucide="phone" class="w-4 h-4"></i>
                <span>Interaction Details</span>
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Person Contacted *</label>
                    <input type="text" 
                           name="contact_person_name" 
                           id="contact-person-name"
                           value="<?= $student ? $e($student['primary_guardian_name']) : '' ?>" 
                           required 
                           placeholder="e.g. Mrs. Sarah Miller" 
                           class="w-full px-3.5 py-2.5 text-xs sm:text-sm rounded-xl glass-input">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Relationship</label>
                    <input type="text" 
                           name="relationship" 
                           id="contact-relation"
                           value="<?= $student ? $e($student['primary_guardian_relation']) : 'Mother' ?>" 
                           placeholder="Mother, Father, etc." 
                           class="w-full px-3.5 py-2.5 text-xs sm:text-sm rounded-xl glass-input">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Phone Number Dialed *</label>
                    <input type="tel" 
                           name="phone_dialed" 
                           id="phone-dialed"
                           value="<?= $student ? $e($student['primary_phone']) : '' ?>" 
                           required 
                           placeholder="e.g. +1 555-0101" 
                           class="w-full px-3.5 py-2.5 text-xs sm:text-sm rounded-xl glass-input font-mono tabular-nums">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Channel / Medium</label>
                    <select name="call_type" class="w-full px-3.5 py-2.5 text-xs sm:text-sm rounded-xl glass-input">
                        <option value="Phone Call">Phone Call</option>
                        <option value="WhatsApp Audio/Video">WhatsApp Audio/Video</option>
                        <option value="WhatsApp Message">WhatsApp Message</option>
                        <option value="In-Person Meeting">In-Person Meeting</option>
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Date & Time of Call</label>
                    <input type="datetime-local" 
                           name="call_timestamp" 
                           value="<?= date('Y-m-d\TH:i') ?>" 
                           class="w-full px-3.5 py-2.5 text-xs sm:text-sm rounded-xl glass-input tabular-nums">
                </div>
            </div>
        </div>

        <!-- Notes & Parent Response -->
        <div class="pt-4 border-t border-slate-100 dark:border-slate-800 space-y-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                    Teacher Discussion Summary & Concerns *
                </label>
                <textarea name="discussion_summary" 
                          rows="3" 
                          required 
                          placeholder="Summarize what was discussed (e.g. Contacted parent regarding 3 consecutive absences without notice. Discussed upcoming mid-term revisions...)" 
                          class="w-full px-3.5 py-2.5 text-xs sm:text-sm rounded-xl glass-input leading-relaxed"></textarea>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
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
        <div class="pt-4 border-t border-slate-100 dark:border-slate-800">
            <div class="flex items-center gap-3 mb-3">
                <input type="checkbox" 
                       name="follow_up_required" 
                       value="1" 
                       id="follow_up_required" 
                       onchange="document.getElementById('follow-up-date-container').classList.toggle('hidden', !this.checked)"
                       class="rounded text-indigo-600 focus:ring-indigo-500">
                <label for="follow_up_required" class="text-xs font-semibold text-slate-800 dark:text-slate-200 cursor-pointer">
                    Flag for Follow-up Action
                </label>
            </div>

            <div id="follow-up-date-container" class="hidden sm:w-1/2">
                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">Target Follow-up Date</label>
                <input type="date" name="follow_up_date" value="<?= date('Y-m-d', strtotime('+7 days')) ?>" class="w-full px-3.5 py-2 text-xs rounded-xl glass-input tabular-nums">
            </div>
        </div>

        <!-- Submit Button -->
        <div class="pt-4 flex items-center justify-end gap-3">
            <a href="/calls" class="px-4 py-2.5 rounded-xl text-xs font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-500 shadow-sm flex items-center gap-2 transition-all">
                <i data-lucide="check" class="w-4 h-4"></i>
                <span>Save Call Record</span>
            </button>
        </div>

    </form>

</div>

<script>
    function updateCategorySelection(radio) {
        document.querySelectorAll('.category-radio-card').forEach(card => {
            card.classList.remove('border-indigo-600', 'bg-indigo-50/40', 'dark:bg-indigo-950/30', 'font-bold');
            card.classList.add('border-slate-200', 'dark:border-slate-800');
        });
        const parentCard = radio.closest('.category-radio-card');
        if (parentCard) {
            parentCard.classList.add('border-indigo-600', 'bg-indigo-50/40', 'dark:bg-indigo-950/30', 'font-bold');
            parentCard.classList.remove('border-slate-200', 'dark:border-slate-800');
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

            // Auto-fetch student bio to fill guardian & phone
            fetch('/students/search/api?q=' + encodeURIComponent(idEl.innerText.split('•')[0].trim()))
                .then(r => r.json())
                .then(data => {
                    if (Array.isArray(data) && data.length > 0) {
                        const s = data[0];
                        if (document.getElementById('contact-person-name') && s.guardian_name) {
                            document.getElementById('contact-person-name').value = s.guardian_name;
                        }
                        if (document.getElementById('contact-relation') && s.guardian_relation) {
                            document.getElementById('contact-relation').value = s.guardian_relation;
                        }
                        if (document.getElementById('phone-dialed') && s.phone) {
                            document.getElementById('phone-dialed').value = s.phone;
                        }
                    }
                }).catch(err => console.log('Autofill error', err));
        }
    });

    function clearSelectedStudent() {
        document.getElementById('selected-student-id').value = '';
        document.getElementById('student-selected-badge').classList.add('hidden');
        document.getElementById('student-picker-input').classList.remove('hidden');
        document.getElementById('student-picker-input').value = '';
    }
</script>
