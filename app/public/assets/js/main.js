/**
 * Global JavaScript for Student Grade Management System
 * Updated for Bootstrap 5 integration
 */

document.addEventListener('DOMContentLoaded', () => {
    initLiveSearch();
    initFlashMessageAutoDismiss();
    initProgressBars();
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

    searchInput.addEventListener('input', debounce(async (e) => {
        const query = e.target.value.toLowerCase().trim();
        
        try {
            const response = await fetch('/api/students');
            if (!response.ok) throw new Error('API fetch failed');
            
            const students = await response.json();
            
            const filtered = students.filter(student => 
                student.name.toLowerCase().includes(query) || 
                (student.email && student.email.toLowerCase().includes(query))
            );

            renderStudentRows(filtered, resultsContainer);
        } catch (err) {
            console.error('Live Search Error:', err);
        }
    }, 300));
}

/**
 * Re-renders table rows for student list with Bootstrap classes
 */
function renderStudentRows(students, container) {
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
        <tr class="student-row">
            <td>
                <div class="form-check">
                    <input type="checkbox" name="student_ids[]" value="${student.id}" class="form-check-input student-checkbox">
                </div>
            </td>
            <td>${escapeHtml(student.name)}</td>
            <td class="text-muted small">${escapeHtml(student.email || 'N/A')}</td>
        </tr>
    `).join('');

    // Re-attach listeners for count update if they exist
    const countDisplay = document.getElementById('selectedCount');
    if (countDisplay) {
        container.querySelectorAll('.student-checkbox').forEach(cb => {
            cb.addEventListener('change', () => {
                const checkedCount = document.querySelectorAll('.student-checkbox:checked').length;
                countDisplay.textContent = checkedCount;
            });
        });
    }
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
