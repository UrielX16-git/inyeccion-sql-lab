<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SQL Injection Lab — Admin Panel</title>
    <meta name="description" content="Panel de administración del laboratorio educativo de inyección SQL">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>⚙️</text></svg>">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <a href="login.php" class="header-brand">
            <span class="logo-icon">🛡️</span>
            <h1>SQL Injection Lab</h1>
        </a>
        <nav class="header-nav">
            <a href="login.php" class="nav-link">🔐 Login</a>
            <a href="admin.php" class="nav-link active">⚙️ Admin Panel</a>
        </nav>
    </header>

    <main class="main-container">
        <!-- Disclaimer -->
        <div class="disclaimer-banner">
            ⚠️ ENTORNO EDUCATIVO — Panel de administración para preparar y restaurar el entorno de demostración.
        </div>

        <div class="page-title">
            <h2>⚙️ Panel de Administración</h2>
            <p>Gestiona la base de datos, crea usuarios y prepara el entorno para las demostraciones de inyección SQL</p>
        </div>

        <!-- Stats Bar -->
        <div class="stats-bar">
            <div class="stat-item">
                <div class="stat-number" id="statUserCount">—</div>
                <div class="stat-label">Usuarios Totales</div>
            </div>
            <div class="stat-item">
                <div class="stat-number" id="statDbStatus">—</div>
                <div class="stat-label">Estado de la BD</div>
            </div>
            <div class="stat-item">
                <div class="stat-number" id="statTableName">—</div>
                <div class="stat-label">Tabla Activa</div>
            </div>
        </div>

        <!-- Action Cards Grid -->
        <div class="admin-grid">
            <!-- Card: Reiniciar Base de Datos -->
            <div class="action-card">
                <div class="action-icon">🔄</div>
                <h4>Reiniciar Base de Datos</h4>
                <p>Elimina y recrea la tabla de usuarios con los datos iniciales por defecto (admin, profesor, estudiante, invitado).</p>
                <button class="btn btn-primary btn-block" onclick="adminAction('reset_db')">
                    🔄 Reiniciar Todo
                </button>
            </div>

            <!-- Card: Vaciar Tabla -->
            <div class="action-card">
                <div class="action-icon">🗑️</div>
                <h4>Vaciar Tabla de Usuarios</h4>
                <p>Borra todos los registros de la tabla sin eliminar la tabla en sí. Simula el efecto del ataque DELETE.</p>
                <button class="btn btn-danger btn-block" onclick="adminAction('clear_users')">
                    🗑️ Vaciar Tabla
                </button>
            </div>

            <!-- Card: Crear Usuario Manual -->
            <div class="action-card">
                <div class="action-icon">➕</div>
                <h4>Crear Usuario Manual</h4>
                <p>Agrega un nuevo usuario con nombre y contraseña personalizados.</p>
                <div class="action-inputs">
                    <input type="text" id="manualUser" class="form-input" placeholder="Nombre de usuario">
                    <input type="text" id="manualPass" class="form-input" placeholder="Contraseña">
                </div>
                <button class="btn btn-success btn-block" onclick="createManualUser()">
                    ➕ Crear Usuario
                </button>
            </div>

            <!-- Card: Usuario Aleatorio -->
            <div class="action-card">
                <div class="action-icon">🎲</div>
                <h4>Crear Usuario Aleatorio</h4>
                <p>Genera automáticamente un usuario con nombre y contraseña aleatorios.</p>
                <button class="btn btn-info btn-block" onclick="adminAction('random_user')">
                    🎲 Generar Aleatorio
                </button>
            </div>

            <!-- Card: X Usuarios Aleatorios -->
            <div class="action-card">
                <div class="action-icon">👥</div>
                <h4>Crear Múltiples Usuarios</h4>
                <p>Genera la cantidad especificada de usuarios con datos aleatorios.</p>
                <div class="action-inputs">
                    <input type="number" id="bulkCount" class="form-input" placeholder="Cantidad (ej: 10)" min="1" max="100" value="5">
                </div>
                <button class="btn btn-info btn-block" onclick="createBulkUsers()">
                    👥 Generar Usuarios
                </button>
            </div>

            <!-- Card: Ejecutar SQL Personalizado -->
            <div class="action-card">
                <div class="action-icon">💻</div>
                <h4>Ejecutar SQL Personalizado</h4>
                <p>Ejecuta una consulta SQL directamente contra la base de datos. Solo para administración del entorno.</p>
                <div class="action-inputs">
                    <input type="text" id="customSQL" class="form-input" placeholder="SELECT * FROM usuarios" style="font-family: var(--font-mono);">
                </div>
                <button class="btn btn-warning btn-block" onclick="executeCustomSQL()">
                    💻 Ejecutar SQL
                </button>
            </div>
        </div>

        <!-- Users Table -->
        <div class="glass-card">
            <div class="card-header">
                <span class="card-icon">👁️</span>
                <h3>Usuarios en la Base de Datos</h3>
                <button class="btn btn-outline btn-sm" onclick="refreshUsers()" style="margin-left: auto;">
                    🔄 Refrescar
                </button>
            </div>
            <div class="users-table-container">
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Usuario</th>
                            <th>Contraseña</th>
                            <th>Fecha de Creación</th>
                        </tr>
                    </thead>
                    <tbody id="usersTableBody">
                        <tr class="empty-row">
                            <td colspan="4">Cargando usuarios...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Custom SQL Result -->
        <div class="glass-card" id="sqlResultCard" style="margin-top: 1.5rem; display: none;">
            <div class="card-header">
                <span class="card-icon">💻</span>
                <h3>Resultado de SQL Personalizado</h3>
            </div>
            <div class="result-query" style="margin-bottom: 1rem;">
                <div class="query-label">Consulta ejecutada:</div>
                <code id="sqlResultQuery"></code>
            </div>
            <div id="sqlResultContent"></div>
        </div>
    </main>

    <div class="toast-container" id="toastContainer"></div>

    <script src="js/admin.js"></script>
</body>
</html>
