# MusicArte — Sistema de Gestion del Centro Cultural

Sistema web hecho en **Laravel 11 + MySQL** para gestionar alumnos, maestros, horarios,
calendario de clases, asistencia, pagos, egresos, caja chica, planilla de maestros,
avisos (ventanas flotantes) y reportes.

Todo el CRUD funciona con **ventanas modales** (sin recargar la pagina para crear/editar),
**SweetAlert2** para todas las confirmaciones y alertas, y un **calendario interactivo**
(FullCalendar) donde se programan y visualizan las clases arrastrando y soltando.

---

## 1. Requisitos

- PHP >= 8.2 con extensiones: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `gd`
- Composer (https://getcomposer.org)
- MySQL 5.7+ / MariaDB (puedes usar XAMPP)
- Servidor local: XAMPP, Laragon, o `php artisan serve`

## 2. Instalacion paso a paso

### 2.1 Descomprimir el proyecto

Descomprime el archivo `musicarte.zip` dentro de tu carpeta de proyectos
(por ejemplo `C:\xampp\htdocs\musicarte` si usas XAMPP, o cualquier carpeta si usaras
`php artisan serve`).

### 2.2 Instalar dependencias PHP

Abre una terminal dentro de la carpeta del proyecto y ejecuta:

```bash
composer install
```

Esto descargara Laravel y las demas librerias (dompdf para los recibos en PDF, etc.)

### 2.3 Configurar el archivo .env

Copia el archivo de ejemplo:

```bash
copy .env.example .env        (Windows)
cp .env.example .env          (Mac/Linux)
```

Edita `.env` y ajusta los datos de tu base de datos MySQL:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=musicarte
DB_USERNAME=root
DB_PASSWORD=
```

> Antes de continuar, crea la base de datos vacia en phpMyAdmin (o por consola):
> `CREATE DATABASE musicarte;`

### 2.4 Generar la clave de la aplicacion

```bash
php artisan key:generate
```

### 2.5 Crear las tablas y los datos iniciales

```bash
php artisan migrate --seed
```

Esto crea todas las tablas y carga:
- 2 usuarios de acceso (ver credenciales abajo)
- Especialidades base (Piano, Guitarra, Violin, Bateria, Canto, etc.) con precios de ejemplo
- Maestros y alumnos de ejemplo
- Un aviso de bienvenida

### 2.6 Enlazar el almacenamiento publico (opcional, para imagenes futuras)

```bash
php artisan storage:link
```

### 2.7 Levantar el servidor

Si usas XAMPP: abre `http://localhost/musicarte/public` en tu navegador
(o configura un Virtual Host apuntando a la carpeta `public`).

Si prefieres el servidor de desarrollo de Laravel:

```bash
php artisan serve
```

Y entra a `http://127.0.0.1:8000`

---

## 3. Credenciales de acceso iniciales

| Rol | Correo | Contrasena |
|---|---|---|
| **Administrador** (acceso total, incluye precios) | admin@musicarte.pe | MusicArte2026 |
| **Recepcion** (todo, excepto precios/montos) | recepcion@musicarte.pe | MusicArte2026 |

**Importante:** cambia estas contrasenas apenas ingreses, o crea tus propios
usuarios directamente en la base de datos / con `php artisan tinker`.

---

## 4. Como estan repartidos los permisos

- **Administrador**: ve y edita absolutamente todo, incluyendo el modulo de **Pagos**,
  **Planilla de Maestros** y el **precio mensual** de cada Especialidad.
- **Recepcion**: puede gestionar Alumnos, Maestros, Especialidades (menos el precio),
  Horarios, Calendario de clases, Asistencia, Egresos, Caja Chica, Avisos y Recitales.
  El modulo de Pagos y Planilla se muestra en modo **solo lectura** (puede ver los
  recibos y generarlos en PDF, pero no crear/editar/eliminar montos).

Esto se controla en dos capas: rutas protegidas con middleware `role:admin` para
Pagos y Planilla, y validacion adicional dentro del controlador de Especialidades
para que el campo `precio_mensual` nunca se guarde si lo envia un usuario de Recepcion
(aunque manipule el formulario).

---

## 5. Modulos incluidos

| Modulo | Descripcion |
|---|---|
| Dashboard | KPIs del dia/mes + alertas de saldos pendientes y cumpleanos |
| Alumnos | CRUD completo con datos de tutor, contacto, diagnostico, etc. |
| Maestros | CRUD de la plana docente |
| Especialidades | Catalogo de instrumentos/disciplinas con color de calendario y precio |
| Horarios | Plantilla semanal recurrente por alumno |
| Calendario | Vista mensual/semanal/diaria (FullCalendar), arrastrar y soltar, popup de detalle |
| Asistencia | Marcar asistencia por clase + resumen % por alumno |
| Pagos | Registro de mensualidades, saldo automatico, recibo en PDF |
| Egresos | Gastos operativos |
| Caja Chica | Movimientos menores |
| Planilla | Pago mensual a maestros por horas |
| Avisos | Ventanas flotantes (popup) que se muestran al ingresar al sistema |
| Recitales/Eventos | Presentaciones especiales del centro |
| Reportes | Alumnos por especialidad, asistencia mensual, ingresos vs egresos, pagos pendientes, planilla, clases dictadas/canceladas — todos imprimibles |

---

## 6. Cargar tus datos reales (los de tus Excel)

Los datos de ejemplo (`database/seeders/AlumnoSeeder.php`, `MaestroSeeder.php`,
`EspecialidadSeeder.php`) son solo una muestra para que veas la app funcionando.
Para cargar tus alumnos, maestros y precios reales de
`ADMINISTRACIÓN_2026.xlsx` y `ASISTENCIA_Y_HORARIOS_2026.xlsx`, tienes dos caminos:

1. **Manual (recomendado para empezar):** usa los modales de Alumnos, Maestros,
   Especialidades y Pagos para ir registrando todo desde la interfaz.
2. **Masivo:** si quieres que te arme un importador especial que lea directamente
   tus archivos Excel (tal como estan, con sus columnas actuales) y llene la base
   de datos automaticamente, pidemelo en el chat y lo agrego como un modulo adicional.

---

## 7. Notas tecnicas

- Framework: Laravel 11 (PHP 8.2+)
- Frontend: Bootstrap 5 + Bootstrap Icons + SweetAlert2 + FullCalendar 6 (todo via CDN,
  no necesitas correr `npm install` ni compilar assets)
- Recibos y reportes en PDF: `barryvdh/laravel-dompdf`
- Autenticacion: sesiones nativas de Laravel (sin paquetes externos)
- Logo: `public/images/logo.png` (el que compartiste)

Cualquier ajuste (mas campos, mas reportes, importador de Excel, notificaciones por
correo, etc.) lo puedo agregar cuando quieras — solo dime que necesitas.
