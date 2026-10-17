/**
 * Class Aid - Client Application Logic
 */

document.addEventListener('DOMContentLoaded', () => {
    initTheme();
    initSearch();
    initAttendanceToggles();
});

// Theme Management (Dark / Light with LocalStorage & OS Sync)
function initTheme() {
    const savedTheme = localStorage.getItem('class_aid_theme');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

    if (savedTheme === 'dark' || (!savedTheme && prefersDark)) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }

    const toggles = document.querySelectorAll('.theme-toggle');
    toggles.forEach(btn => {
        btn.addEventListener('click', () => {
            const isDark = document.documentElement.classList.toggle('dark');
            localStorage.setItem('class_aid_theme', isDark ? 'dark' : 'light');
            updateThemeIcons(isDark);
        });
    });

    updateThemeIcons(document.documentElement.classList.contains('dark'));
}

function updateThemeIcons(isDark) {
    document.querySelectorAll('.theme-icon-sun').forEach(el => {
        el.style.display = isDark ? 'inline-block' : 'none';
    });
    document.querySelectorAll('.theme-icon-moon').forEach(el => {
        el.style.display = isDark ? 'none' : 'inline-block';
    });
}

// Live Student Quick Search Typeahead
function initSearch() {
    const searchInputs = document.querySelectorAll('.global-student-search');
    searchInputs.forEach(input => {
        const resultsContainer = input.closest('.search-wrapper')?.querySelector('.search-results-dropdown');
        if (!resultsContainer) return;

        let debounceTimer = null;

        input.addEventListener('input', (e) => {
            clearTimeout(debounceTimer);
            const query = e.target.value.trim();

            if (query.length < 1) {
                resultsContainer.classList.add('hidden');
                resultsContainer.innerHTML = '';
                return;
            }

            debounceTimer = setTimeout(async () => {
                try {
                    const res = await fetch(`/students/search/api?q=${encodeURIComponent(query)}`);
                    const data = await res.json();

                    if (!Array.isArray(data) || data.length === 0) {
                        resultsContainer.innerHTML = `
                            <div class="p-3 text-xs text-gray-500 dark:text-gray-400 text-center">
                                No students found for "${query}"
                            </div>`;
                    } else {
                        resultsContainer.innerHTML = data.map(s => `
                            <a href="${s.url}" class="flex items-center justify-between p-2.5 hover:bg-indigo-500/10 rounded-lg transition-colors border-b border-gray-100 dark:border-gray-800/50 last:border-0">
                                <div>
                                    <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">${s.name}</div>
                                    <div class="text-xs text-indigo-600 dark:text-indigo-400 font-mono">${s.admin_id} • ${s.class}</div>
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1">
                                    <span>${s.phone}</span>
                                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </div>
                            </a>
                        `).join('');
                    }
                    resultsContainer.classList.remove('hidden');
                } catch (err) {
                    console.error('Search error:', err);
                }
            }, 200);
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!input.contains(e.target) && !resultsContainer.contains(e.target)) {
                resultsContainer.classList.add('hidden');
            }
        });
    });
}

// Attendance 1-Tap Toggle
function initAttendanceToggles() {
    const rows = document.querySelectorAll('.attendance-student-row');
    rows.forEach(row => {
        const input = row.querySelector('.attendance-status-input');
        const buttons = row.querySelectorAll('.att-toggle-btn');
        if (!input || !buttons.length) return;

        buttons.forEach(btn => {
            btn.addEventListener('click', () => {
                const targetStatus = btn.dataset.status;
                input.value = targetStatus;

                // Update visual styles
                buttons.forEach(b => {
                    b.classList.remove('ring-2', 'ring-offset-2', 'ring-indigo-500', 'font-bold', 'opacity-100');
                    b.classList.add('opacity-40');
                });

                btn.classList.remove('opacity-40');
                btn.classList.add('ring-2', 'ring-offset-2', 'ring-indigo-500', 'font-bold', 'opacity-100');
            });
        });
    });

    // "Mark All Present" button
    const markAllPresentBtn = document.getElementById('mark-all-present-btn');
    if (markAllPresentBtn) {
        markAllPresentBtn.addEventListener('click', () => {
            document.querySelectorAll('.attendance-student-row').forEach(row => {
                const presentBtn = row.querySelector('.att-toggle-btn[data-status="P"]');
                if (presentBtn) {
                    presentBtn.click();
                }
            });
        });
    }
}

// Quick WhatsApp launcher helper
function openWhatsApp(phone, studentName, adminId) {
    if (!phone) return;
    const cleanPhone = phone.replace(/[^0-9]/g, '');
    const message = encodeURIComponent(`Hello, this is regarding ${studentName} (Admin ID: ${adminId}) from Class Aid.`);
    window.open(`https://wa.me/${cleanPhone}?text=${message}`, '_blank');
}
