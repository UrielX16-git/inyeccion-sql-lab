// =====================================================
// SQL Injection Lab — Admin Panel JavaScript
// =====================================================

document.addEventListener('DOMContentLoaded', () => {
    // Load initial data
    refreshUsers();
    refreshStats();
});

// --- Generic Admin Action ---
async function adminAction(action, extraData = {}) {
    try {
        const res = await fetch('admin_process.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action, ...extraData })
        });

        const data = await res.json();

        if (data.success) {
            showToast(data.message, 'success');
        } else {
            showToast(data.message || 'Error desconocido', 'error');
        }

        // Refresh table and stats after any action
        refreshUsers();
        refreshStats();

        return data;
    } catch (err) {
        showToast('Error de conexión con el servidor', 'error');
        return null;
    }
}

// --- Create Manual User ---
async function createManualUser() {
    const usuario = document.getElementById('manualUser').value.trim();
    const contrasena = document.getElementById('manualPass').value.trim();

    if (!usuario || !contrasena) {
        showToast('Debes ingresar usuario y contraseña', 'error');
        return;
    }

    const result = await adminAction('create_user', { usuario, contrasena });
    if (result && result.success) {
        document.getElementById('manualUser').value = '';
        document.getElementById('manualPass').value = '';
    }
}

// --- Create Bulk Random Users ---
async function createBulkUsers() {
    const count = parseInt(document.getElementById('bulkCount').value);

    if (isNaN(count) || count < 1 || count > 100) {
        showToast('La cantidad debe ser un número entre 1 y 100', 'error');
        return;
    }

    await adminAction('bulk_users', { count });
}

// --- Execute Custom SQL ---
async function executeCustomSQL() {
    const sql = document.getElementById('customSQL').value.trim();

    if (!sql) {
        showToast('Debes ingresar una consulta SQL', 'error');
        return;
    }

    try {
        const res = await fetch('admin_process.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'custom_sql', sql })
        });

        const data = await res.json();
        const card = document.getElementById('sqlResultCard');
        const queryDisplay = document.getElementById('sqlResultQuery');
        const content = document.getElementById('sqlResultContent');

        card.style.display = 'block';
        queryDisplay.textContent = sql;

        if (data.success) {
            showToast(data.message, 'success');

            // If results contain rows, display as table
            if (data.results && data.results.length > 0 && data.results[0].length > 0) {
                let html = '<table class="users-table"><thead><tr>';
                const cols = Object.keys(data.results[0][0]);
                cols.forEach(col => {
                    html += `<th>${escapeHtml(col)}</th>`;
                });
                html += '</tr></thead><tbody>';

                data.results[0].forEach(row => {
                    html += '<tr>';
                    cols.forEach(col => {
                        html += `<td class="mono">${escapeHtml(String(row[col] || ''))}</td>`;
                    });
                    html += '</tr>';
                });
                html += '</tbody></table>';
                content.innerHTML = html;
            } else {
                content.innerHTML = `<p style="color: var(--text-secondary); font-size: 0.85rem;">
                    ${escapeHtml(data.message)}</p>`;
            }
        } else {
            showToast(data.message, 'error');
            content.innerHTML = `<p style="color: var(--danger-light); font-size: 0.85rem;">
                ❌ ${escapeHtml(data.message)}</p>`;
        }

        // Refresh table in case data changed
        refreshUsers();
        refreshStats();

    } catch (err) {
        showToast('Error de conexión con el servidor', 'error');
    }
}

// --- Refresh Users Table ---
async function refreshUsers() {
    try {
        const res = await fetch('admin_process.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'list_users' })
        });

        const data = await res.json();
        const tbody = document.getElementById('usersTableBody');

        if (data.success && data.users.length > 0) {
            tbody.innerHTML = data.users.map(user => `
                <tr>
                    <td class="mono">${escapeHtml(String(user.id))}</td>
                    <td><strong>${escapeHtml(user.usuario)}</strong></td>
                    <td class="pass-cell">${escapeHtml(user.contrasena)}</td>
                    <td style="color: var(--text-muted); font-size: 0.8rem;">${escapeHtml(user.created_at || '')}</td>
                </tr>
            `).join('');
        } else {
            tbody.innerHTML = `
                <tr class="empty-row">
                    <td colspan="4">⚠️ No hay usuarios en la base de datos. Usa "Reiniciar Base de Datos" para crear los usuarios por defecto.</td>
                </tr>
            `;
        }
    } catch (err) {
        document.getElementById('usersTableBody').innerHTML = `
            <tr class="empty-row">
                <td colspan="4">❌ Error al conectar con la base de datos</td>
            </tr>
        `;
    }
}

// --- Refresh Stats ---
async function refreshStats() {
    try {
        const res = await fetch('admin_process.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'count_users' })
        });

        const data = await res.json();

        document.getElementById('statUserCount').textContent = data.success ? data.count : '—';
        document.getElementById('statDbStatus').textContent = data.success ? '✅' : '❌';
        document.getElementById('statTableName').textContent = 'usuarios';
    } catch (err) {
        document.getElementById('statUserCount').textContent = '❌';
        document.getElementById('statDbStatus').textContent = '❌';
        document.getElementById('statTableName').textContent = '—';
    }
}

// --- Utility: Escape HTML ---
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// --- Toast Notification ---
function showToast(message, type = 'info') {
    const container = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;

    const icons = { success: '✅', error: '❌', info: 'ℹ️' };
    toast.innerHTML = `<span>${icons[type] || 'ℹ️'}</span> ${escapeHtml(message)}`;

    container.appendChild(toast);

    setTimeout(() => {
        toast.remove();
    }, 3000);
}
