# CMDB_MDT - Sistema de Gestión de Base de Datos de Configuración

## 📋 Descripción

**CMDB_MDT** es un sistema completo de gestión de inventario de activos tecnológicos (CMDB - Configuration Management Database) desarrollado para **MD Tecnología**. Este sistema permite administrar, controlar y supervisar todos los equipos de cómputo, software y dispositivos tecnológicos de una organización de manera eficiente y centralizada.

## ✨ Características Principales

### 🔐 Sistema de Autenticación Dual
- **Administradores**: Acceso completo a todas las funcionalidades
- **Colaboradores**: Acceso limitado a consultas y solicitudes de equipos
- Sistema de sesiones seguro con validación de roles

### 📦 Gestión Completa de Inventario
- **Categorización**: Hardware, Software, Equipos de Red, Equipos de Cómputo, Telefonía
- **Estados de Equipos**: Activo, Inventario, Asignado, Reparación, Descarte, Donado
- **Información Detallada**: Marca, modelo, serie, costo, depreciación, imágenes
- **Código QR**: Generación automática para identificación rápida

### 👥 Gestión de Colaboradores
- **Perfiles Completos**: Datos personales, departamento, ubicación
- **Historial de Accesos**: Registro de sesiones y actividad
- **Equipos Asignados**: Vista de todos los equipos bajo su responsabilidad
- **Solicitudes**: Sistema para pedir asignación de equipos

### 📊 Sistema de Solicitudes y Asignaciones
- **Flujo de Aprobación**: Los colaboradores solicitan, los administradores aprueban
- **Estados de Solicitud**: Pendiente, Aceptada, Rechazada
- **Asignaciones Controladas**: Registro completo de quién tiene qué equipo
- **Devoluciones**: Sistema de retiro y devolución de equipos

### 🗂️ Sistema de Descarte
- **Evaluación Técnica**: Registro detallado de motivos de descarte
- **Trazabilidad**: Historial completo del ciclo de vida del equipo
- **Observaciones**: Documentación técnica del estado del equipo

### 🎁 Sistema de Donaciones
- **Solicitudes de Donación**: Los colaboradores pueden solicitar donación de equipos asignados
- **Proceso de Aprobación**: Validación administrativa antes de proceder
- **Registro de Destinatarios**: Control de a quién se dona cada equipo

### 📈 Reportes y Estadísticas
- **Dashboards Visuales**: Gráficos de estado de equipos por categoría
- **Exportación Excel**: Reportes completos y filtrados
- **Estadísticas en Tiempo Real**: Equipos disponibles, asignados, en descarte
- **Filtros Avanzados**: Por categoría, estado, fechas, colaborador

### 🖼️ Gestión de Imágenes
- **Fotos de Equipos**: Upload y visualización de imágenes de inventario
- **Fotos de Perfil**: Gestión de avatares de usuarios y colaboradores
- **Fallbacks Automáticos**: Imágenes por defecto cuando no hay foto disponible

## 🛠️ Tecnologías Utilizadas

### Backend
- **PHP 8+**: Lenguaje principal del servidor
- **MySQL**: Base de datos relacional
- **Composer**: Gestión de dependencias

### Frontend
- **Bootstrap 5.3.2**: Framework CSS responsivo
- **JavaScript ES6+**: Interactividad del cliente
- **Font Awesome**: Iconografía
- **AJAX/Fetch API**: Comunicación asíncrona

### Librerías Externas
- **Endroid QR Code**: Generación de códigos QR
- **PHPSpreadsheet**: Exportación a Excel (vía Composer)

### Herramientas de Desarrollo
- **WAMP/XAMPP**: Entorno de desarrollo local
- **Git**: Control de versiones

## 📁 Estructura del Proyecto

```
CMDB_MDT/
├── 📁 Usuario/                    # Módulo Administrador
│   ├── Home.php                   # Dashboard principal
│   ├── Inventario.php             # Gestión de equipos
│   ├── Categorias.php             # Gestión de categorías
│   ├── Usuarios.php               # Gestión de usuarios
│   ├── Solicitudes.php            # Aprobación de solicitudes
│   ├── Asignaciones.php           # Control de asignaciones
│   ├── Descarte.php               # Gestión de descartes
│   ├── Reportes.php               # Reportes y estadísticas
│   └── Perfil.php                 # Perfil de administrador
├── 📁 colaboradores/              # Módulo Colaboradores
│   ├── portal_colaborador.php     # Dashboard colaborador
│   ├── InventarioColab.php        # Vista de inventario disponible
│   ├── CategoriasColab.php        # Consulta de categorías
│   ├── SolicitudesColab.php       # Mis solicitudes
│   ├── PerfilColab.php            # Perfil de colaborador
│   └── solicitar_donacion.php     # Solicitar donación
├── 📁 js/                         # Scripts JavaScript
│   ├── login.js                   # Autenticación
│   ├── home.js                    # Dashboard
│   ├── reportes.js                # Reportes interactivos
│   ├── descarte.js                # Gestión de descartes
│   ├── qrinventario.js            # Generación QR
│   └── ...
├── 📁 css/                        # Estilos personalizados
├── 📁 img/                        # Imágenes del sistema
├── 📁 uploads/                    # Archivos subidos
├── 📁 ajax/                       # Endpoints AJAX
├── 📁 sql/                        # Scripts SQL
├── conexion.php                   # Clase de conexión DB
├── navbar_unificado.php           # Navegación global
├── cmdb.sql                       # Base de datos completa
└── composer.json                  # Dependencias
```

## 🚀 Instalación y Configuración

### Prerrequisitos
- **PHP 8.0+**
- **MySQL 5.7+ / MariaDB 10.3+**
- **Apache/Nginx**
- **Composer**

### Paso 1: Clonar el Repositorio
```bash
git clone https://github.com/JustinBr88/CMDB_MDT.git
cd CMDB_MDT
```

### Paso 2: Instalar Dependencias
```bash
composer install
```

### Paso 3: Configurar Base de Datos
1. Crear la base de datos:
```sql
CREATE DATABASE cmdb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. Importar el esquema:
```bash
mysql -u tu_usuario -p cmdb < cmdb.sql
```

### Paso 4: Configurar Conexión
Editar `conexion.php` con tus credenciales de base de datos:
```php
private $host = "localhost";
private $username = "tu_usuario";
private $password = "tu_contraseña";
private $database = "cmdb";
```

### Paso 5: Configurar Permisos
```bash
chmod 755 uploads/
chmod 755 img/usuarios/
```

## 👤 Usuarios por Defecto

### Administrador
- **Usuario**: admin@midominio.com
- **Contraseña**: admin123

### Colaborador
- **Usuario**: colaborador@midominio.com
- **Contraseña**: colab123

> ⚠️ **Importante**: Cambiar estas contraseñas en producción

## 📊 Base de Datos

### Tablas Principales
- **usuarios**: Cuentas de acceso al sistema
- **colaboradores**: Información de empleados
- **categorias**: Tipos de equipos
- **inventario**: Equipos y software
- **solicitudes**: Peticiones de equipos
- **asignaciones**: Control de equipos asignados
- **donaciones**: Gestión de donaciones
- **historial_accesos_colaborador**: Auditoría de accesos

### Características de la BD
- **Integridad Referencial**: Foreign keys para consistencia
- **Triggers**: Automatización de estados de descarte
- **Índices**: Optimización de consultas frecuentes
- **UTF-8**: Soporte completo de caracteres especiales

## 🔒 Seguridad

### Autenticación
- **Hashing de Contraseñas**: bcrypt con salt
- **Validación de Sesiones**: Control de acceso por roles
- **Prepared Statements**: Prevención de SQL Injection

### Validación de Datos
- **Sanitización**: Escape de caracteres especiales
- **Validación Backend**: Verificación en servidor
- **Filtros de Upload**: Control de tipos de archivo

### Auditoría
- **Historial de Accesos**: Registro de IP y navegador
- **Logs de Actividad**: Trazabilidad de acciones

## 📱 Funcionalidades por Rol

### 👨‍💼 Administradores
- ✅ CRUD completo de inventario
- ✅ Gestión de usuarios y colaboradores
- ✅ Aprobación/rechazo de solicitudes
- ✅ Control de asignaciones
- ✅ Gestión de descartes
- ✅ Reportes y estadísticas
- ✅ Validación de donaciones
- ✅ Configuración del sistema

### 👥 Colaboradores
- ✅ Vista de inventario disponible
- ✅ Solicitud de equipos
- ✅ Consulta de mis solicitudes
- ✅ Vista de equipos asignados
- ✅ Solicitud de donaciones
- ✅ Gestión de perfil personal
- ✅ Generación de códigos QR

## 🎨 Características de la Interfaz

### Diseño Responsivo
- **Bootstrap 5**: Adaptable a dispositivos móviles
- **Navegación Intuitiva**: Menús contextuales por rol
- **Iconografía Consistente**: Font Awesome

### Experiencia de Usuario
- **Modales Interactivos**: Formularios sin recarga de página
- **Tablas Dinámicas**: Ordenamiento y filtros
- **Feedback Visual**: Alertas y confirmaciones
- **Carga Asíncrona**: AJAX para mejor rendimiento

### Accesibilidad
- **Etiquetas Semánticas**: HTML5 estructurado
- **Contraste Adecuado**: Colores accesibles
- **Navegación por Teclado**: Soporte para usuarios con discapacidades

## 📈 Casos de Uso

### Gestión Diaria
1. **Nuevo Equipo**: Administrador registra equipo → Genera QR → Disponible para asignación
2. **Solicitud de Equipo**: Colaborador solicita → Administrador aprueba → Equipo asignado
3. **Devolución**: Colaborador devuelve → Administrador valida → Equipo disponible
4. **Descarte**: Técnico evalúa → Administrador marca descarte → Fuera de inventario

### Reportes de Gestión
- **Inventario Total**: Estado general de todos los equipos
- **Asignaciones Activas**: Quién tiene qué equipo
- **Equipos Disponibles**: Inventario listo para asignar
- **Estadísticas por Departamento**: Distribución de recursos

## 🔧 Mantenimiento

### Respaldos
- **Base de Datos**: Backup diario recomendado
- **Archivos Subidos**: Respaldar carpeta `uploads/`
- **Configuración**: Respaldar `conexion.php`

### Actualizaciones
- **Dependencias**: `composer update` periódicamente
- **Seguridad**: Monitorear vulnerabilidades PHP
- **Base de Datos**: Scripts de migración cuando sea necesario

### Logs
- **Errores PHP**: Revisar logs del servidor web
- **Accesos**: Tabla `historial_accesos_colaborador`
- **Actividad**: Logs personalizados en desarrollo futuro

## 🤝 Contribución

### Proceso de Desarrollo
1. Fork del repositorio
2. Crear rama feature: `git checkout -b feature/nueva-funcionalidad`
3. Commit con mensaje descriptivo
4. Push a la rama: `git push origin feature/nueva-funcionalidad`
5. Crear Pull Request

### Estándares de Código
- **PSR-12**: Estándar de codificación PHP
- **Comentarios**: Documentar funciones complejas
- **Validación**: Backend + Frontend siempre
- **Seguridad**: Sanitizar todas las entradas

## 📞 Soporte

### Información de Contacto
- **Desarrollador**: Justin Br88
- **GitHub**: [@JustinBr88](https://github.com/JustinBr88)
- **Proyecto**: [CMDB_MDT](https://github.com/JustinBr88/CMDB_MDT)

### Reportar Problemas
- **Issues**: GitHub Issues para bugs y solicitudes
- **Documentación**: Wiki del proyecto
- **Discusiones**: GitHub Discussions para preguntas

## 📄 Licencia

Este proyecto está bajo la Licencia MIT. Ver el archivo [LICENSE](LICENSE) para más detalles.

## 🏆 Reconocimientos

### Tecnologías Open Source
- **PHP**: Lenguaje principal
- **Bootstrap**: Framework CSS
- **Font Awesome**: Iconografía
- **Endroid QR Code**: Generación de códigos QR

### Inspiración
- **ITIL v4**: Buenas prácticas de gestión de servicios TI
- **ISO 20000**: Estándares de gestión de servicios
- **Principios CMDB**: Configuration Management Database

---

<div align="center">

**Desarrollado con ❤️ para MD Tecnología**

`Version 1.0.0` | `PHP 8+` | `MySQL` | `Bootstrap 5`

</div>
