<div class="space-y-6">

    <!-- Top Breadcrumb & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="/students<?= $selectedClassId ? '?class_id=' . $selectedClassId : '' ?>" class="text-xs text-gray-400 hover:text-indigo-500 flex items-center gap-1 transition-colors">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    <span>Student Directory</span>
                </a>
                <span class="text-xs text-gray-400">•</span>
                <span class="text-xs font-semibold text-indigo-500 uppercase">Bulk Roster Importer</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white mt-1">
                Bulk Import Students
            </h1>
            <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                Import complete class rosters from CSV spreadsheets, digital tables, or hardcopy paper photos using OCR.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <?php if ($ocrAvailable): ?>
                <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-[11px] font-semibold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                    <i data-lucide="scan" class="w-3.5 h-3.5"></i>
                    <span>OCR Vision Engine Ready</span>
                </span>
            <?php endif; ?>
            <a href="/students/import/template" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold glass-card hover:bg-gray-100 dark:hover:bg-white/10 text-gray-700 dark:text-gray-200 transition-all">
                <i data-lucide="download" class="w-4 h-4 text-indigo-500"></i>
                <span>Download Sample CSV</span>
            </a>
        </div>
    </div>

    <!-- Step 1: Configuration & Upload Box -->
    <div class="glass-card p-6">
        <form id="scan-form" class="space-y-5" onsubmit="event.preventDefault(); processStudentScan();">
            <?= $csrf() ?>
            <input type="hidden" id="image_base64" name="image_base64" value="">

            <!-- Class Selection -->
            <div class="max-w-md">
                <label for="class_id" class="block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">
                    Target Class For Registration <span class="text-rose-500">*</span>
                </label>
                <select id="class_id" name="class_id" class="w-full px-3 py-2.5 text-xs rounded-xl glass-input font-medium" required>
                    <?php foreach ($classes as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= ($selectedClassId == $c['id']) ? 'selected' : '' ?>>
                            <?= $e($c['name']) ?> - <?= $e($c['section']) ?> (<?= $e($c['academic_year']) ?>) <?= $c['is_home_class'] ? '(Home Class)' : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Import Source Tabs -->
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">
                    Select Data Source
                </label>
                
                <div class="flex border-b border-gray-200/50 dark:border-white/10 gap-4 mb-4">
                    <button type="button" onclick="switchTab('file')" id="tab-btn-file" class="pb-2 text-xs font-semibold border-b-2 border-indigo-600 text-indigo-600 dark:text-indigo-400 flex items-center gap-1.5">
                        <i data-lucide="file-up" class="w-4 h-4"></i>
                        <span>File / Photo Upload</span>
                    </button>
                    <button type="button" onclick="switchTab('paste')" id="tab-btn-paste" class="pb-2 text-xs font-semibold border-b-2 border-transparent text-gray-500 hover:text-gray-900 dark:hover:text-white flex items-center gap-1.5">
                        <i data-lucide="clipboard" class="w-4 h-4"></i>
                        <span>Paste Screenshot (Ctrl+V)</span>
                    </button>
                    <button type="button" onclick="switchTab('text')" id="tab-btn-text" class="pb-2 text-xs font-semibold border-b-2 border-transparent text-gray-500 hover:text-gray-900 dark:hover:text-white flex items-center gap-1.5">
                        <i data-lucide="file-text" class="w-4 h-4"></i>
                        <span>Raw CSV / Tabular Text</span>
                    </button>
                </div>

                <!-- Tab 1: File / Photo Upload -->
                <div id="tab-content-file" class="tab-pane">
                    <div id="dropzone" class="border-2 border-dashed border-slate-300 dark:border-slate-700 rounded-2xl p-8 text-center hover:border-indigo-500 dark:hover:border-indigo-400 transition-colors cursor-pointer bg-slate-50 dark:bg-slate-950/60" onclick="document.getElementById('import_file').click()">
                        <input type="file" id="import_file" name="import_file" class="hidden" accept=".csv,.txt,.tsv,.png,.jpg,.jpeg,.webp,.bmp,.tiff,.pdf" onchange="handleFileSelected(this)">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center mx-auto mb-3 shadow-inner">
                            <i data-lucide="upload-cloud" class="w-6 h-6"></i>
                        </div>
                        <h4 class="text-sm font-bold text-slate-900 dark:text-white">
                            Choose a CSV or photo of paper roster, or drag & drop here
                        </h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                            Supports CSV, TSV, TXT, or scanned images (PNG, JPG, WEBP, PDF)
                        </p>
                        <div id="selected-file-name" class="mt-3 text-xs font-semibold text-indigo-600 dark:text-indigo-400 hidden"></div>
                    </div>
                </div>

                <!-- Tab 2: Clipboard Paste -->
                <div id="tab-content-paste" class="tab-pane hidden">
                    <div id="paste-target" tabindex="0" class="border-2 border-dashed border-slate-300 dark:border-slate-700 rounded-2xl p-8 text-center focus:border-indigo-500 focus:outline-none bg-slate-50 dark:bg-slate-950/60 cursor-pointer">
                        <div class="w-12 h-12 rounded-2xl bg-violet-50 dark:bg-indigo-950/40 text-violet-600 dark:text-violet-400 flex items-center justify-center mx-auto mb-3">
                            <i data-lucide="camera" class="w-6 h-6"></i>
                        </div>
                        <h4 class="text-sm font-bold text-slate-900 dark:text-white">
                            Click here and press <kbd class="px-2 py-0.5 rounded bg-slate-200 dark:bg-slate-800 text-xs font-mono font-bold">Ctrl + V</kbd> to paste screenshot
                        </h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                            Capture a snippet of your spreadsheet or screen and paste directly!
                        </p>
                        <div id="paste-preview-container" class="mt-4 hidden flex flex-col items-center">
                            <img id="paste-preview" class="max-h-56 rounded-xl border border-slate-200 dark:border-slate-800 shadow-lg object-contain">
                            <button type="button" onclick="clearPastedImage()" class="mt-2 text-xs text-rose-500 hover:underline cursor-pointer">Remove image</button>
                        </div>
                    </div>
                </div>

                <!-- Tab 3: Raw Text / CSV -->
                <div id="tab-content-text" class="tab-pane hidden">
                    <textarea id="raw_text" name="raw_text" rows="6" class="w-full p-3 text-xs font-mono rounded-xl glass-input placeholder-gray-400" placeholder="student_admin_id,first_name,last_name,gender,primary_phone,primary_guardian_name&#10;ADM-101,Sarah,Connor,Female,555-0199,John Connor&#10;ADM-102,Luke,Skywalker,Male,555-0188,Anakin Skywalker"></textarea>
                    <p class="text-[11px] text-gray-400 mt-1">
                        Paste comma-separated, tab-separated, or pipe-separated tabular rows.
                    </p>
                </div>
            </div>

            <!-- Action Ribbon -->
            <div class="flex items-center justify-between pt-2">
                <div id="scan-status" class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-2"></div>
                <button type="submit" id="btn-process" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 shadow-md shadow-indigo-600/30 transition-all">
                    <i data-lucide="sparkles" class="w-4 h-4"></i>
                    <span>Scan & Stage Students</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Step 2: Interactive Staging & Review Table -->
    <div id="staging-section" class="hidden space-y-4">
        <form method="POST" action="/students/import/commit" id="commit-form">
            <?= $csrf() ?>
            <input type="hidden" name="class_id" id="commit_class_id" value="<?= $selectedClassId ?>">

            <div class="glass-card overflow-hidden">
                <div class="p-4 bg-slate-50 dark:bg-slate-950/60 border-b border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <span>Extracted Students Preview & Review</span>
                            <span id="badge-total-count" class="px-2.5 py-0.5 rounded-full text-xs font-bold badge-indigo">0 rows</span>
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            Review and edit detected fields. Fix any duplicates or typos before finalizing registration.
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" onclick="addNewStudentRow()" class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700 flex items-center gap-1 transition-all cursor-pointer">
                            <i data-lucide="plus" class="w-3.5 h-3.5 text-indigo-500"></i>
                            <span>Add Row</span>
                        </button>
                        <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 shadow-sm transition-all cursor-pointer">
                            <i data-lucide="check-circle" class="w-4 h-4"></i>
                            <span>Confirm & Register Students</span>
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 dark:bg-slate-950/80 border-b border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 uppercase font-semibold text-xs tracking-wider">
                            <tr>
                                <th class="py-3 px-3 w-10 text-center">#</th>
                                <th class="py-3 px-3 w-36">Admin ID *</th>
                                <th class="py-3 px-3">First Name *</th>
                                <th class="py-3 px-3">Last Name</th>
                                <th class="py-3 px-3 w-28">Gender</th>
                                <th class="py-3 px-3 w-36">Parent Phone *</th>
                                <th class="py-3 px-3">Guardian Name *</th>
                                <th class="py-3 px-3">Status</th>
                                <th class="py-3 px-3 w-12 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody id="staging-tbody" class="divide-y divide-slate-100 dark:divide-slate-800/80">
                            <!-- Injected dynamically via JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </form>
    </div>

</div>

<script>
    let activeTab = 'file';
    let currentRows = [];

    function switchTab(tab) {
        activeTab = tab;
        ['file', 'paste', 'text'].forEach(t => {
            document.getElementById('tab-content-' + t).classList.toggle('hidden', t !== tab);
            const btn = document.getElementById('tab-btn-' + t);
            if (t === tab) {
                btn.className = 'pb-2 text-xs font-semibold border-b-2 border-indigo-600 text-indigo-600 dark:text-indigo-400 flex items-center gap-1.5';
            } else {
                btn.className = 'pb-2 text-xs font-semibold border-b-2 border-transparent text-gray-500 hover:text-gray-900 dark:hover:text-white flex items-center gap-1.5';
            }
        });
    }

    // Drag and drop dropzone handlers
    const dropzone = document.getElementById('dropzone');
    ['dragenter', 'dragover'].forEach(eventName => {
        dropzone.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropzone.classList.add('border-indigo-600', 'bg-indigo-50/20');
        }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropzone.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropzone.classList.remove('border-indigo-600', 'bg-indigo-50/20');
        }, false);
    });

    dropzone.addEventListener('drop', (e) => {
        const dt = e.dataTransfer;
        const files = dt.files;
        if (files.length > 0) {
            document.getElementById('import_file').files = files;
            handleFileSelected(document.getElementById('import_file'));
        }
    });

    function handleFileSelected(input) {
        const file = input.files[0];
        const display = document.getElementById('selected-file-name');
        if (file) {
            display.textContent = 'Selected: ' + file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
            display.classList.remove('hidden');
        } else {
            display.classList.add('hidden');
        }
    }

    // Clipboard Paste Listener
    document.addEventListener('paste', (e) => {
        const items = (e.clipboardData || e.originalEvent.clipboardData).items;
        for (let item of items) {
            if (item.type.indexOf('image') === 0) {
                const blob = item.getAsFile();
                const reader = new FileReader();
                reader.onload = function(event) {
                    const base64 = event.target.result;
                    document.getElementById('image_base64').value = base64;
                    document.getElementById('paste-preview').src = base64;
                    document.getElementById('paste-preview-container').classList.remove('hidden');
                    switchTab('paste');
                };
                reader.readAsDataURL(blob);
                break;
            }
        }
    });

    function clearPastedImage() {
        document.getElementById('image_base64').value = '';
        document.getElementById('paste-preview').src = '';
        document.getElementById('paste-preview-container').classList.add('hidden');
    }

    // Process scan via AJAX
    async function processStudentScan() {
        const statusEl = document.getElementById('scan-status');
        const btnProcess = document.getElementById('btn-process');
        const classId = document.getElementById('class_id').value;
        document.getElementById('commit_class_id').value = classId;

        const formData = new FormData();
        formData.append('class_id', classId);
        formData.append('_csrf_token', document.querySelector('input[name="_csrf_token"]').value);

        if (activeTab === 'file') {
            const fileInput = document.getElementById('import_file');
            if (!fileInput.files || fileInput.files.length === 0) {
                alert('Please choose a file or image first.');
                return;
            }
            formData.append('import_file', fileInput.files[0]);
        } else if (activeTab === 'paste') {
            const b64 = document.getElementById('image_base64').value;
            if (!b64) {
                alert('Please paste a screenshot using Ctrl+V first.');
                return;
            }
            formData.append('image_base64', b64);
        } else {
            const rawText = document.getElementById('raw_text').value;
            if (!rawText.trim()) {
                alert('Please enter or paste CSV text first.');
                return;
            }
            formData.append('raw_text', rawText);
        }

        btnProcess.disabled = true;
        btnProcess.classList.add('opacity-50', 'cursor-wait');
        statusEl.innerHTML = '<i data-lucide="loader" class="w-4 h-4 animate-spin text-indigo-500"></i> Parsing data & running OCR analysis...';
        if (typeof lucide !== 'undefined') lucide.createIcons();

        // Show staging section with animated skeleton ladder
        document.getElementById('staging-section').classList.remove('hidden');
        document.getElementById('badge-total-count').textContent = 'Analyzing...';
        document.getElementById('staging-tbody').innerHTML = `
            <tr>
                <td colspan="7" class="p-6">
                    <div class="skeleton-ladder space-y-3">
                        <div class="skeleton-ladder-row flex items-center justify-between p-3.5"><div class="skeleton w-1/4 h-4"></div><div class="skeleton w-1/5 h-4"></div><div class="skeleton w-1/6 h-4"></div><div class="skeleton w-1/4 h-4"></div></div>
                        <div class="skeleton-ladder-row flex items-center justify-between p-3.5"><div class="skeleton w-1/3 h-4"></div><div class="skeleton w-1/5 h-4"></div><div class="skeleton w-1/6 h-4"></div><div class="skeleton w-1/5 h-4"></div></div>
                        <div class="skeleton-ladder-row flex items-center justify-between p-3.5"><div class="skeleton w-1/4 h-4"></div><div class="skeleton w-1/5 h-4"></div><div class="skeleton w-1/6 h-4"></div><div class="skeleton w-1/4 h-4"></div></div>
                        <div class="skeleton-ladder-row flex items-center justify-between p-3.5"><div class="skeleton w-2/5 h-4"></div><div class="skeleton w-1/5 h-4"></div><div class="skeleton w-1/6 h-4"></div><div class="skeleton w-1/6 h-4"></div></div>
                    </div>
                </td>
            </tr>
        `;
        document.getElementById('staging-section').scrollIntoView({ behavior: 'smooth' });
        if (typeof PageLoader !== 'undefined') PageLoader.start();

        try {
            const res = await fetch('/students/import/scan', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });

            if (typeof PageLoader !== 'undefined') PageLoader.done();

            const contentType = res.headers.get('content-type') || '';
            let data;
            if (contentType.includes('application/json')) {
                data = await res.json();
            } else {
                const rawText = await res.text();
                const cleanError = rawText.replace(/<[^>]*>?/gm, ' ').replace(/\s+/g, ' ').trim();
                throw new Error(cleanError.substring(0, 250) || 'Server returned an invalid response (HTTP ' + res.status + ')');
            }

            if (!data.success) {
                alert(data.message || 'Error processing data.');
                statusEl.innerHTML = '<span class="text-rose-500 font-semibold">' + (data.message || 'Failed') + '</span>';
                document.getElementById('staging-section').classList.add('hidden');
                return;
            }

            currentRows = data.students || [];
            renderStagingTable(currentRows);
            statusEl.innerHTML = '<span class="text-emerald-500 font-semibold"><i data-lucide="check" class="w-3.5 h-3.5 inline"></i> Extracted ' + currentRows.length + ' students!</span>';
            if (typeof lucide !== 'undefined') lucide.createIcons();
        } catch (err) {
            if (typeof PageLoader !== 'undefined') PageLoader.done();
            console.error(err);
            alert('An unexpected error occurred during processing: ' + err.message);
            statusEl.innerHTML = '<span class="text-rose-500 font-semibold">Error scanning data</span>';
            document.getElementById('staging-section').classList.add('hidden');
        } finally {
            btnProcess.disabled = false;
            btnProcess.classList.remove('opacity-50', 'cursor-wait');
        }
    }

    function renderStagingTable(rows) {
        const tbody = document.getElementById('staging-tbody');
        tbody.innerHTML = '';
        document.getElementById('badge-total-count').textContent = rows.length + ' rows';

        rows.forEach((r, idx) => {
            const tr = document.createElement('tr');
            tr.className = 'hover:bg-indigo-500/5 transition-colors';
            tr.id = 'staging-row-' + idx;

            const isDuplicate = (r.status === 'duplicate');
            const statusBadge = isDuplicate 
                ? '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/10 text-amber-500 border border-amber-500/20" title="' + (r.message || '') + '">Duplicate ID</span>'
                : '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/10 text-emerald-500 border border-emerald-500/20">Ready</span>';

            tr.innerHTML = `
                <td class="py-2.5 px-3 text-center text-gray-400 font-mono">${idx + 1}</td>
                <td class="py-2.5 px-3">
                    <input type="text" name="students[${idx}][student_admin_id]" value="${escapeHtml(r.student_admin_id || '')}" required class="w-full px-2.5 py-1.5 text-xs font-mono rounded-lg glass-input ${isDuplicate ? 'border-amber-500 text-amber-400 font-bold' : ''}">
                </td>
                <td class="py-2.5 px-3">
                    <input type="text" name="students[${idx}][first_name]" value="${escapeHtml(r.first_name || '')}" required class="w-full px-2.5 py-1.5 text-xs rounded-lg glass-input">
                </td>
                <td class="py-2.5 px-3">
                    <input type="text" name="students[${idx}][last_name]" value="${escapeHtml(r.last_name || '')}" class="w-full px-2.5 py-1.5 text-xs rounded-lg glass-input">
                </td>
                <td class="py-2.5 px-3">
                    <select name="students[${idx}][gender]" class="w-full px-2 py-1.5 text-xs rounded-lg glass-input">
                        <option value="Male" ${r.gender === 'Male' ? 'selected' : ''}>Male</option>
                        <option value="Female" ${r.gender === 'Female' ? 'selected' : ''}>Female</option>
                        <option value="Other" ${r.gender === 'Other' ? 'selected' : ''}>Other</option>
                    </select>
                </td>
                <td class="py-2.5 px-3">
                    <input type="text" name="students[${idx}][primary_phone]" value="${escapeHtml(r.primary_phone || '')}" required class="w-full px-2.5 py-1.5 text-xs rounded-lg glass-input font-mono">
                </td>
                <td class="py-2.5 px-3">
                    <input type="text" name="students[${idx}][primary_guardian_name]" value="${escapeHtml(r.primary_guardian_name || 'Parent/Guardian')}" required class="w-full px-2.5 py-1.5 text-xs rounded-lg glass-input">
                </td>
                <td class="py-2.5 px-3">
                    ${statusBadge}
                </td>
                <td class="py-2.5 px-3 text-center">
                    <button type="button" onclick="deleteStagingRow(${idx})" class="p-1 rounded-lg text-gray-400 hover:text-rose-500 hover:bg-rose-500/10 transition-colors" title="Remove Row">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                </td>
            `;

            tbody.appendChild(tr);
        });

        lucide.createIcons();
    }

    function deleteStagingRow(idx) {
        const rowEl = document.getElementById('staging-row-' + idx);
        if (rowEl) {
            rowEl.remove();
        }
    }

    function addNewStudentRow() {
        const idx = document.getElementById('staging-tbody').children.length;
        const newStu = {
            student_admin_id: 'STU-' + (new Date().getFullYear()) + '-' + String(idx + 1).padStart(3, '0'),
            first_name: '',
            last_name: '',
            gender: 'Male',
            primary_phone: '',
            primary_guardian_name: 'Parent/Guardian',
            status: 'ready'
        };

        const tbody = document.getElementById('staging-tbody');
        const tr = document.createElement('tr');
        tr.className = 'hover:bg-indigo-500/5 transition-colors bg-indigo-500/5';
        tr.id = 'staging-row-' + idx;

        tr.innerHTML = `
            <td class="py-2.5 px-3 text-center text-gray-400 font-mono">${idx + 1}</td>
            <td class="py-2.5 px-3">
                <input type="text" name="students[${idx}][student_admin_id]" value="${newStu.student_admin_id}" required class="w-full px-2.5 py-1.5 text-xs font-mono rounded-lg glass-input">
            </td>
            <td class="py-2.5 px-3">
                <input type="text" name="students[${idx}][first_name]" placeholder="First Name" required class="w-full px-2.5 py-1.5 text-xs rounded-lg glass-input">
            </td>
            <td class="py-2.5 px-3">
                <input type="text" name="students[${idx}][last_name]" placeholder="Last Name" class="w-full px-2.5 py-1.5 text-xs rounded-lg glass-input">
            </td>
            <td class="py-2.5 px-3">
                <select name="students[${idx}][gender]" class="w-full px-2 py-1.5 text-xs rounded-lg glass-input">
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                </select>
            </td>
            <td class="py-2.5 px-3">
                <input type="text" name="students[${idx}][primary_phone]" placeholder="Phone" required class="w-full px-2.5 py-1.5 text-xs rounded-lg glass-input font-mono">
            </td>
            <td class="py-2.5 px-3">
                <input type="text" name="students[${idx}][primary_guardian_name]" value="Parent/Guardian" required class="w-full px-2.5 py-1.5 text-xs rounded-lg glass-input">
            </td>
            <td class="py-2.5 px-3">
                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/10 text-emerald-500 border border-emerald-500/20">New</span>
            </td>
            <td class="py-2.5 px-3 text-center">
                <button type="button" onclick="deleteStagingRow(${idx})" class="p-1 rounded-lg text-gray-400 hover:text-rose-500 hover:bg-rose-500/10 transition-colors">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                </button>
            </td>
        `;

        tbody.appendChild(tr);
        lucide.createIcons();
    }

    function escapeHtml(text) {
        if (!text) return '';
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
</script>
