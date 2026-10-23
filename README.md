# Ventas Bodega — Minimarket "La Esquina"

Aplicación web interna de ventas y control de stock para el minimarket "La Esquina" en Cañete.  
Permite al cajero registrar productos, alertar sobre stock bajo, registrar ventas y consultar el total del día.

---

## Requisitos

| Herramienta | Versión mínima |
|---|---|
| XAMPP (Apache + MySQL/MariaDB) | 7.4 / MySQL 8.0 o MariaDB 10.2 |
| PHP | 8.1 |
| Navegador moderno | Chrome, Firefox, Edge |

> PHP 8.1+ es necesario para la sintaxis de propiedades de constructor (`public readonly`) y `str_contains()` usadas en el proyecto.

---

## Estructura del proyecto

```
ventas-bodega/
├── app/
│   ├── Config/         # Database.php, Env.php
│   ├── Controllers/    # ProductoController.php, VentaController.php
│   ├── Exceptions/     # AppException y excepciones de dominio
│   ├── Models/         # Producto.php, Venta.php
│   ├── Repositories/   # ProductoRepository.php, VentaRepository.php
│   └── Services/       # ProductoService.php, VentaService.php
├── database/
│   ├── schema.sql      # Crea la BD y las tablas
│   └── seed.sql        # Inserta los 8 productos de prueba
├── public/
│   └── index.php       # Punto de entrada de la aplicación
├── views/
│   ├── productos/      # Vistas de listado y formulario de producto
│   └── ventas/         # Vistas de listado y formulario de venta
├── .env                # Variables de entorno (no se sube a Git)
├── .env.example        # Plantilla de variables de entorno
└── .gitignore
```

---

## 1. Crear la base de datos

La base de datos se importa manualmente en **phpMyAdmin** en dos pasos.

### Paso 1 — Crear tablas (`schema.sql`)

1. Abre phpMyAdmin: `http://localhost/phpmyadmin`
2. Haz clic en la pestaña **Importar**
3. Selecciona el archivo `database/schema.sql`
4. Deja la codificación en `utf8mb4` y haz clic en **Continuar**

Esto crea la base de datos `bodega_ventas` y las tablas `producto` y `venta`.

### Paso 2 — Cargar datos de prueba (`seed.sql`)

1. En phpMyAdmin, selecciona la base de datos `bodega_ventas` en el panel izquierdo
2. Haz clic en la pestaña **Importar**
3. Selecciona el archivo `database/seed.sql`
4. Haz clic en **Continuar**

Esto inserta los 8 productos de prueba (P001–P008).  
> Los productos P006 (Atún) y P008 (Detergente) tienen stock por debajo del mínimo, por lo que aparecerán resaltados en el listado desde el primer uso.

---

## 2. Configurar el archivo `.env`

Copia la plantilla y ajusta los valores según tu entorno:

```
# En Windows (PowerShell)
Copy-Item .env.example .env
```

Abre `.env` y edita los valores:

```ini
DB_HOST=localhost
DB_PORT=3307        # Puerto MySQL de XAMPP (3306 si no tienes otro en uso)
DB_NAME=bodega_ventas
DB_USER=root
DB_PASS=            # Vacío en instalaciones por defecto de XAMPP
```

> El archivo `.env` está en `.gitignore` y nunca se sube al repositorio.  
> Usa `.env.example` como referencia para otros desarrolladores.

---

## 3. Ejecutar la aplicación

1. Asegúrate de que **Apache** y **MySQL** estén activos en el panel de XAMPP
2. Abre el navegador y accede a:

```
http://localhost/ventas-bodega/public/
```

La navegación disponible es:

| URL | Descripción |
|---|---|
| `?ruta=productos` | Listado de productos con alerta de stock |
| `?ruta=producto-crear` | Formulario para registrar un nuevo producto |
| `?ruta=venta-crear` | Formulario para registrar una venta |
| `?ruta=ventas` | Listado de ventas del día con total acumulado |

---

## 4. Pruebas

Actualmente no existe una suite de pruebas automatizadas (ver TD-03 en `TECH_DEBT.md`).  
Las reglas de negocio se verifican manualmente:

| Regla | Cómo probarla |
|---|---|
| RN01 — cantidad > 0 | Intentar registrar una venta con cantidad 0 → el sistema debe mostrar error |
| RN02 — no vender más del stock | Ingresar cantidad superior al stock disponible → el sistema debe mostrar error |
| RN03 — precio no negativo | Intentar registrar un producto con precio −1 → el sistema debe mostrar error |
| RN04 — código único | Registrar dos productos con el mismo código → el segundo debe ser rechazado |
| RN05 — atomicidad venta/stock | Simular fallo de BD durante una venta → ni la venta ni el stock deben modificarse |

---

## Notas adicionales

- La aplicación **no tiene login**; está diseñada para uso interno en la red local del negocio (ver supuesto en `D0.md`).
- No se generan boletas ni se registran datos de clientes.
- Los errores de BD se registran en el log de errores de PHP (`error_log`) y nunca se muestran al cajero.
