# Inyección SQL Lab

Laboratorio educativo para demostrar vulnerabilidades de inyección SQL en aplicaciones web. Diseñado para uso didáctico.

> **⚠️ Advertencia:** Esta aplicación es intencionalmente vulnerable. Para uso únicamente en entornos locales y aislados.

---

## Descripción

El proyecto simula un sistema de login vulnerable que permite probar distintos vectores de ataque de inyección SQL:

- **Bypass de autenticación** — Evadir la validación de usuario y/o contraseña
- **Extracción de datos (UNION)** — Filtrar contraseñas y listar usuarios mediante consultas UNION
- **Ataques destructivos (DELETE)** — Borrar registros usando stacked queries
- **Reconocimiento (ORDER BY)** — Descubrir la estructura de la base de datos

Incluye un **panel de administración** para gestionar la base de datos, crear usuarios y restaurar el entorno después de cada demostración.

---

## Requisitos

- XAMPP (Apache + MySQL/MariaDB + PHP)
- Navegador web moderno

---

## Instalación

1. **Clonar o copiar el proyecto** dentro del directorio de XAMPP:

   ```bash
   # Opción A: Copiar al htdocs
   cp -r Intercomunicacion /opt/lampp/htdocs/sqli-lab

   # Opción B: Crear un symlink
   ln -s /ruta/al/proyecto /opt/lampp/htdocs/sqli-lab
   ```

2. **Iniciar XAMPP** (Apache y MySQL):

   ```bash
   sudo /opt/lampp/lampp start
   ```

3. **Abrir la aplicación** en el navegador:

   ```
   http://localhost/sqli-lab/
   ```

4. **Inicializar la base de datos** desde el panel de administración:
   - Ir a `http://localhost/sqli-lab/admin.php`
   - Hacer clic en **"Reiniciar Todo"** para crear la base de datos y los usuarios por defecto

---

## Uso

### Página de Login (`login.php`)

La página principal contiene un formulario de login vulnerable y tarjetas de payload interactivas. Al hacer clic en una tarjeta, se auto-rellena el campo correspondiente con el payload seleccionado.

### Panel de Administración (`admin.php`)

Permite preparar y restaurar el entorno de demostración:

- **Reiniciar Base de Datos** — Recrea la tabla con los usuarios por defecto
- **Vaciar Tabla** — Borra todos los registros (simula el efecto del ataque DELETE)
- **Crear Usuario Manual** — Agrega usuarios con credenciales personalizadas
- **Crear Usuario Aleatorio** — Genera usuarios con datos aleatorios
- **Crear Múltiples Usuarios** — Genera varios usuarios de una vez
- **Ejecutar SQL Personalizado** — Ejecuta consultas SQL directamente
