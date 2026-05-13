// =====================================================
// SQL Injection Lab — Login Page JavaScript
// =====================================================

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('loginForm');
    const userInput = document.getElementById('usuario');
    const passInput = document.getElementById('contrasena');
    const loginBtn = document.getElementById('loginBtn');
    const resultPanel = document.getElementById('resultPanel');
    const resultHeader = document.getElementById('resultHeader');
    const resultMessage = document.getElementById('resultMessage');
    const resultQuery = document.getElementById('resultQuery');

    // --- Handle Login Form Submit ---
    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const usuario = userInput.value;
        const contrasena = passInput.value;

        // Disable button during request
        loginBtn.disabled = true;
        loginBtn.innerHTML = '<span class="spinner"></span> Procesando...';

        try {
            const res = await fetch('login_process.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ usuario, contrasena })
            });

            const data = await res.json();

            // Show result panel
            resultPanel.className = 'result-panel visible';

            if (data.success) {
                resultPanel.classList.add('result-success');
                resultPanel.classList.remove('result-error', 'result-warning');
                resultHeader.innerHTML = '✅ <span style="color: var(--success-light);">Acceso Concedido</span>';

                // Build message with all results if UNION was used
                let msg = data.message;
                if (data.all_results && data.all_results.length > 1) {
                    msg += '<br><br><strong>📋 Todos los resultados encontrados:</strong><br>';
                    msg += '<table class="users-table" style="margin-top: 0.5rem;">';
                    msg += '<thead><tr>';
                    // Get column names from first row
                    const cols = Object.keys(data.all_results[0]);
                    cols.forEach(col => {
                        msg += `<th>${escapeHtml(col)}</th>`;
                    });
                    msg += '</tr></thead><tbody>';
                    data.all_results.forEach(row => {
                        msg += '<tr>';
                        cols.forEach(col => {
                            msg += `<td class="mono">${escapeHtml(String(row[col] || ''))}</td>`;
                        });
                        msg += '</tr>';
                    });
                    msg += '</tbody></table>';
                }
                resultMessage.innerHTML = msg;

            } else {
                resultPanel.classList.add('result-error');
                resultPanel.classList.remove('result-success', 'result-warning');
                resultHeader.innerHTML = '❌ <span style="color: var(--danger-light);">Acceso Denegado</span>';
                resultMessage.innerHTML = escapeHtml(data.message);
            }

            // Show the SQL query that was executed
            resultQuery.textContent = data.query || '(no disponible)';

        } catch (err) {
            resultPanel.className = 'result-panel visible result-warning';
            resultHeader.innerHTML = '⚠️ <span style="color: var(--warning-light);">Error de Conexión</span>';
            resultMessage.textContent = 'No se pudo conectar con el servidor. Verifica que XAMPP esté corriendo.';
            resultQuery.textContent = '(error de red)';
        } finally {
            loginBtn.disabled = false;
            loginBtn.innerHTML = '🚀 Iniciar Sesión';
        }
    });

    // --- Payload Cards Click Handler ---
    document.querySelectorAll('.payload-card').forEach(card => {
        card.addEventListener('click', () => {
            const target = card.dataset.target;   // 'usuario' or 'contrasena'
            const payload = card.dataset.payload;

            const targetInput = target === 'usuario' ? userInput : passInput;
            targetInput.value = payload;

            // Visual feedback
            targetInput.classList.add('injected');
            targetInput.focus();

            // Remove highlight after animation
            setTimeout(() => {
                targetInput.classList.remove('injected');
            }, 1500);

            // Show toast
            showToast(`Payload insertado en el campo "${target === 'usuario' ? 'Usuario' : 'Contraseña'}"`, 'info');
        });
    });

    // Focus animation on inputs
    [userInput, passInput].forEach(input => {
        input.addEventListener('focus', () => {
            input.parentElement.style.transform = 'scale(1.01)';
            input.parentElement.style.transition = 'transform 0.2s ease';
        });
        input.addEventListener('blur', () => {
            input.parentElement.style.transform = 'scale(1)';
        });
    });
});

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

    // Remove after animation completes
    setTimeout(() => {
        toast.remove();
    }, 3000);
}
