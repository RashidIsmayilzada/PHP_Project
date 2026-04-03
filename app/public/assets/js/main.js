/**
 * Global JavaScript for Student Grade Management System
 * Updated for Bootstrap 5 integration
 */

document.addEventListener('DOMContentLoaded', () => {
    initLiveSearch();
    initFlashMessageAutoDismiss();
    initProgressBars();
    initRegisterRoleToggle();
});

/**
 * Initialize progress bars from data-progress attributes
 */
function initProgressBars() {
    document.querySelectorAll('.progress-bar[data-progress]').forEach(el => {
        const progress = el.getAttribute('data-progress');
        if (progress) {
            el.style.width = progress + '%';
        }
    });
}

/**
 * Live Student Search Integration
 * Uses internal API to search for students during enrollment
 */
function initLiveSearch() {
    const searchInput = document.getElementById('liveSearchInput');
    const resultsContainer = document.getElementById('liveSearchResults');

    if (!searchInput || !resultsContainer) return;

    const apiUrl = searchInput.dataset.apiUrl || '/api/students';
    const availableIds = new Set(
        Array.from(resultsContainer.querySelectorAll('[data-student-id]'))
            .map(row => Number.parseInt(row.dataset.studentId, 10))
            .filter(Number.isInteger)
    );
    const selectedIds = new Set();
    let students = [];

    bindSelectionTracking(resultsContainer, selectedIds);
    updateSelectedCount(selectedIds);

    searchInput.addEventListener('input', debounce((event) => {
        const query = event.target.value.toLowerCase().trim();
        const filteredStudents = students.filter(student => {
            const matchesAvailableList = availableIds.has(Number(student.id));
            const matchesQuery = query === ''
                || student.name.toLowerCase().includes(query)
                || (student.email && student.email.toLowerCase().includes(query));

            return matchesAvailableList && matchesQuery;
        });

        renderStudentRows(filteredStudents, resultsContainer, selectedIds);
    }, 250));

    fetch(apiUrl, { headers: { Accept: 'application/json' } })
        .then(response => {
            if (!response.ok) {
                throw new Error('API fetch failed');
            }

            return response.json();
        })
        .then(data => {
            students = Array.isArray(data) ? data : [];
            const filteredStudents = students.filter(student => availableIds.has(Number(student.id)));
            renderStudentRows(filteredStudents, resultsContainer, selectedIds);
        })
        .catch(error => {
            console.error('Live Search Error:', error);
            showSearchError(resultsContainer);
        });
}

/**
 * Re-renders table rows for student list with Bootstrap classes
 */
function renderStudentRows(students, container, selectedIds) {
    if (students.length === 0) {
        container.innerHTML = `
            <tr>
                <td colspan="3" class="text-center py-4 text-muted">
                    <i class="bi bi-search display-6 d-block mb-2"></i>
                    No students found matching your search.
                </td>
            </tr>`;
        return;
    }

    container.innerHTML = students.map(student => `
        <tr class="student-row" data-student-id="${student.id}">
            <td>
                <div class="form-check">
                    <input
                        type="checkbox"
                        name="student_ids[]"
                        value="${student.id}"
                        class="form-check-input student-checkbox"
                        ${selectedIds.has(Number(student.id)) ? 'checked' : ''}
                    >
                </div>
            </td>
            <td>${escapeHtml(student.name)}</td>
            <td class="text-muted small">${escapeHtml(student.email || 'N/A')}</td>
        </tr>
    `).join('');

    bindSelectionTracking(container, selectedIds);
    updateSelectedCount(selectedIds);
}

/**
 * Auto-dismiss flash messages after 5 seconds using Bootstrap API
 */
function initFlashMessageAutoDismiss() {
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(alert => {
        setTimeout(() => {
            if (typeof bootstrap !== 'undefined' && bootstrap.Alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            } else {
                // Fallback if bootstrap JS not loaded
                alert.style.opacity = '0';
                alert.style.transition = 'opacity 0.5s ease';
                setTimeout(() => alert.remove(), 500);
            }
        }, 5000);
    });
}

function initRegisterRoleToggle() {
    const roleSelect = document.getElementById('role');
    const studentFields = document.getElementById('student_fields');

    if (!roleSelect || !studentFields) {
        return;
    }

    roleSelect.addEventListener('change', () => {
        studentFields.classList.toggle('d-none', roleSelect.value !== 'student');
    });
}

function bindSelectionTracking(container, selectedIds) {
    container.querySelectorAll('.student-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', () => {
            const studentId = Number(checkbox.value);

            if (checkbox.checked) {
                selectedIds.add(studentId);
            } else {
                selectedIds.delete(studentId);
            }

            updateSelectedCount(selectedIds);
        });
    });
}

function updateSelectedCount(selectedIds) {
    const countDisplay = document.getElementById('selectedCount');
    if (countDisplay) {
        countDisplay.textContent = String(selectedIds.size);
    }
}

function showSearchError(container) {
    container.innerHTML = `
        <tr>
            <td colspan="3" class="text-center py-4 text-danger">
                <i class="bi bi-exclamation-circle display-6 d-block mb-2"></i>
                Could not load students from the API.
            </td>
        </tr>
    `;
}

/**
 * Utility: Debounce API calls
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Utility: Escape HTML
 */
function escapeHtml(str) {
    if (!str) return '';
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}
