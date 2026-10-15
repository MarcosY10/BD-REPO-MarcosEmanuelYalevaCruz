# Sistema de asistencias para preceptoras

## Instalación en XAMPP
1. Copiar esta carpeta a `C:\xampp\htdocs\asistencias\`.
2. En el panel de XAMPP iniciar **Apache** y **MySQL**.
3. En Workbench conectarse a `127.0.0.1:3306` (usuario `root`) y ejecutar, en este orden:
   - `sql/schema.sql`
   - `sql/datos_ejemplo.sql` (opcional, datos de prueba)
4. Abrir http://localhost/asistencias/public/verificar.php: todo debe aparecer en OK.
5. Ingresar en http://localhost/asistencias/ con alguna de las cuentas de prueba.

## Configuración
- `config/config.php`: nombre, año lectivo, carpeta (`BASE_URL`), tiempos de sesión, peso de las faltas.
- `config/database.php`: datos de conexión (usuario `asistencias_app`, creado por `schema.sql`).

## Cuentas de prueba
| Rol | Usuario | Contraseña |
|---|---|---|
| Admin | admin@escuela.local | Admin2026! (la primera vez pide cambiarla) |
| Preceptora | laura.gomez@escuela.local | Preceptora2026! |
| Alumnos | alumno01@escuela.local ... alumno26@escuela.local | alumno123 |

## Registro de preceptoras
En `public/registro.php`, con la clave institucional (por defecto `ESCUELA2026`, se guarda en la tabla `configuracion`).
Las preceptoras entran directo, pero ven sus cursos recién cuando el admin se los asigne.
Los alumnos no se registran: los crea el admin.

## Recuperar contraseña
Desde el login: "¿Olvidaste tu contraseña?". El link vence a la hora y sirve una sola vez.
En modo desarrollo no se envían mails: el link aparece en pantalla y queda en `logs/correos.log`.
Al publicar: poné `MODO_DESARROLLO` en `false`, cargá el dominio real en `SITIO_URL` (config/config.php) y
configurá el envío de mail del servidor (o cambiá `enviar_link_recuperacion()` por PHPMailer).

## Antes de publicar
- `MODO_DESARROLLO` en `false` (config/config.php).
- Borrar `public/verificar.php`.
- Cambiar `SITIO_URL` y `MAIL_REMITENTE` por los datos reales.
- Cambiar la clave de `asistencias_app` y la clave de registro de preceptoras.
