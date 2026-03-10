# Sistema Académico

Proyecto desarrollado con **Laravel** para la gestión básica de materias, grupos y estudiantes dentro de una institución académica.

El sistema permite administrar información académica y registrar la relación entre estudiantes y grupos.

---

# Descripción del Proyecto

Este sistema académico permite realizar operaciones básicas de gestión educativa:

* Registro de materias
* Creación de grupos académicos
* Registro de estudiantes
* Inscripción de estudiantes en grupos
* Autenticación de usuarios

El sistema está desarrollado utilizando **arquitectura MVC** proporcionada por Laravel.

---

# Tecnologías Utilizadas

El proyecto utiliza las siguientes herramientas:

* **Laravel** – Framework principal del backend
* **PHP** – Lenguaje de programación
* **MySQL** – Base de datos
* **Laravel Breeze** – Sistema de autenticación
* **Node.js** – Compilación de recursos frontend
* **Git** – Control de versiones

---

# Requisitos del Sistema

Para ejecutar el proyecto es necesario tener instalado:

* **PHP** 8.1 o superior
* **Composer**
* **Node.js**
* **MySQL**
* **Git**

Opcionalmente se recomienda usar:

* **Laragon** o **XAMPP** para facilitar el entorno de desarrollo.

---

# Instalación del Proyecto

### 1. Clonar el repositorio

```bash
git clone URL_DEL_REPOSITORIO
```

Entrar al directorio del proyecto:

```bash
cd sistema-academico
```

---

# Instalar dependencias del backend

Instalar las dependencias de **Laravel** con:

```bash
composer install
```

---

# Instalar dependencias del frontend

Instalar paquetes de **Node.js**:

```bash
npm install
```

Compilar recursos:

```bash
npm run dev
```

---

# Configurar variables de entorno

Crear el archivo `.env`:

```bash
cp .env.example .env
```

Luego generar la clave del sistema:

```bash
php artisan key:generate
```

---

# Configurar base de datos

En el archivo `.env` configurar la conexión a **MySQL**:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sistema_academico
DB_USERNAME=root
DB_PASSWORD=
```

Crear la base de datos manualmente en MySQL con el nombre:

```
sistema_academico
```

---

# Ejecutar migraciones

Crear las tablas del sistema:

```bash
php artisan migrate
```

---

# Ejecutar el servidor

Iniciar el servidor de desarrollo:

```bash
php artisan serve
```

Abrir en el navegador:

```
http://127.0.0.1:8000
```

---

# Funcionalidades del Sistema

El sistema permite realizar las siguientes operaciones:

### Gestión de Materias

* Crear materias
* Editar materias
* Listar materias

### Gestión de Grupos

* Crear grupos
* Asociar grupos a materias
* Listar grupos

### Gestión de Estudiantes

* Registrar estudiantes
* Editar estudiantes
* Listar estudiantes

### Inscripción a Grupos

* Asociar estudiantes a grupos
* Visualizar estudiantes inscritos

---

# Estructura General del Proyecto

El proyecto sigue la estructura estándar de **Laravel**:

```
app/
   Models/
   Http/
      Controllers/

database/
   migrations/

resources/
   views/

routes/
   web.php
```

---

# Flujo de Trabajo con Git

Cada integrante debe trabajar en su propia rama.

Crear una rama:

```bash
git checkout -b nombre-rama
```

Guardar cambios:

```bash
git add .
git commit -m "Descripción de cambios"
git push origin nombre-rama
```

---

# Equipo de Desarrollo

Proyecto desarrollado por estudiantes de la asignatura:

**Modelos de Desarrollo Web**

Integrantes del equipo:

* Andres Felipe Joya Buitrago
* Emily Nicoll David Villadiego
* Jessica Juarez
* 

---

# Licencia

Proyecto desarrollado con fines académicos.

Con eso tu repositorio se ve **mucho más profesional**.
