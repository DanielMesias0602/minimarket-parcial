# Design — Sistema de Ventas y Control de Stock
**Negocio:** Minimarket "La Esquina" — Cañete
**Versión:** 1.0

---

## 1. Arquitectura en Capas

El sistema sigue una arquitectura de **5 capas** con responsabilidades claramente separadas. Cada capa solo se comunica con la capa inmediatamente inferior.

```
┌─────────────────────────┐
│         Views           │  ← Capa 5 (presentación)
├─────────────────────────┤
│       Controller        │  ← Capa 4 (entrada/salida HTTP)
├─────────────────────────┤
│        Service          │  ← Capa 3 (lógica de negocio)
├─────────────────────────┤
│       Repository        │  ← Capa 2 (acceso a datos)
├─────────────────────────┤
│         Model           │  ← Capa 1 (estructura de datos)
└─────────────────────────┘
```

---

### Capa 1 — Model (Modelo)

**Qué hace:**
- Define la estructura de los datos que maneja el sistema.
- Representa las entidades del negocio: `Producto` y `Venta`.
- Contiene los atributos con sus tipos de datos y las reglas básicas de validación de formato.

**Qué NO hace:**
- No accede a la base de datos.
- No contiene lógica de negocio ni cálculos.
- No se comunica con otras capas; solo es usada por ellas.

---

### Capa 2 — Repository (Repositorio)

**Qué hace:**
- Ejecuta las operaciones SQL contra la base de datos: INSERT, SELECT, UPDATE.
- Encapsula toda la comunicación con el motor de base de datos (MySQL/MariaDB vía XAMPP).
- Gestiona la conexión y las transacciones (BEGIN, COMMIT, ROLLBACK).

**Qué NO hace:**
- No valida reglas de negocio (eso es responsabilidad del Service).
- No formatea datos para mostrarlos al usuario.
- No recibe peticiones HTTP directamente.

---

### Capa 3 — Service (Servicio)

**Qué hace:**
- Contiene toda la lógica de negocio del sistema.
- Valida las reglas RN01 a RN05 antes de invocar al Repository.
- Coordina la transacción atómica de venta (RN05): llama al Repository para guardar la venta y descontar el stock dentro de la misma transacción.
- Calcula el total de la venta y el total acumulado del día.

**Qué NO hace:**
- No ejecuta SQL directamente.
- No maneja la solicitud o respuesta HTTP.
- No construye HTML ni formatea la salida visual.

---

### Capa 4 — Controller (Controlador)

**Qué hace:**
- Recibe las peticiones HTTP del navegador (GET, POST).
- Extrae y sanitiza los datos del formulario o la URL.
- Llama al Service correspondiente con los datos limpios.
- Redirige o envía la respuesta al View según el resultado.

**Qué NO hace:**
- No contiene lógica de negocio.
- No accede a la base de datos.
- No genera HTML directamente; delega eso al View.

---

### Capa 5 — Views (Vistas)

**Qué hace:**
- Genera el HTML que el cajero ve en el navegador.
- Muestra formularios para registrar productos y ventas.
- Resalta en rojo (o con alerta visual) los productos con stock bajo el mínimo (RF02).
- Muestra la lista de ventas del día y el total acumulado (RF04).

**Qué NO hace:**
- No procesa datos ni aplica lógica.
- No accede a la base de datos.
- No toma decisiones de flujo; solo presenta lo que el Controller le pasa.

---

## 2. Esquema de Base de Datos (SQL)

```sql
-- ============================================================
-- Tabla: producto
-- ============================================================
CREATE TABLE producto (
    id_producto     INT             NOT NULL AUTO_INCREMENT,
    codigo          VARCHAR(20)     NOT NULL,
    nombre          VARCHAR(100)    NOT NULL,
    categoria       VARCHAR(50)     NOT NULL,
    precio_venta    DECIMAL(10,2)   NOT NULL,
    stock_actual    INT             NOT NULL DEFAULT 0,
    stock_minimo    INT             NOT NULL DEFAULT 0,
    -- Restricciones de integridad (RN03, RN04)
    CONSTRAINT pk_producto          PRIMARY KEY (id_producto),
    CONSTRAINT uq_producto_codigo   UNIQUE (codigo),
    CONSTRAINT ck_precio_no_negativo CHECK (precio_venta >= 0),
    CONSTRAINT ck_stock_actual      CHECK (stock_actual >= 0),
    CONSTRAINT ck_stock_minimo      CHECK (stock_minimo >= 0)
);

-- ============================================================
-- Tabla: venta
-- ============================================================
CREATE TABLE venta (
    id_venta        INT             NOT NULL AUTO_INCREMENT,
    id_producto     INT             NOT NULL,
    cantidad        INT             NOT NULL,
    precio_unitario DECIMAL(10,2)   NOT NULL,
    total           DECIMAL(10,2)   NOT NULL,
    fecha_hora      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    -- Restricciones de integridad (RN01, RN02)
    CONSTRAINT pk_venta             PRIMARY KEY (id_venta),
    CONSTRAINT fk_venta_producto    FOREIGN KEY (id_producto)
                                    REFERENCES producto(id_producto),
    CONSTRAINT ck_cantidad_positiva    CHECK (cantidad > 0),
    CONSTRAINT ck_precio_unitario_nn  CHECK (precio_unitario >= 0),
    CONSTRAINT ck_total_no_negativo    CHECK (total >= 0)
);
```

> **Nota:** La restricción `CHECK` es soportada por MySQL 8.0+ y MariaDB 10.2+. En versiones anteriores de XAMPP estas restricciones se omiten silenciosamente, por lo que la validación deberá reforzarse en la capa Service.

---

## 3. Clases, Métodos y Tipos de Datos

### 3.1 Capa Model

#### Clase `Producto`

| Atributo | Tipo | Descripción |
|---|---|---|
| `id_producto` | `int` | Identificador interno autoincremental |
| `codigo` | `string` | Código único del producto |
| `nombre` | `string` | Nombre descriptivo |
| `categoria` | `string` | Categoría (abarrotes, lácteos, etc.) |
| `precio_venta` | `float` | Precio unitario de venta |
| `stock_actual` | `int` | Unidades disponibles actualmente |
| `stock_minimo` | `int` | Umbral mínimo para la alerta de stock |

#### Clase `Venta`

| Atributo | Tipo | Descripción |
|---|---|---|
| `id_venta` | `int` | Identificador interno autoincremental |
| `id_producto` | `int` | FK — producto vendido |
| `cantidad` | `int` | Unidades vendidas en esta venta |
| `precio_unitario` | `float` | Precio unitario al momento de la venta |
| `total` | `float` | Precio calculado: precio_venta × cantidad |
| `fecha_hora` | `string` / `datetime` | Fecha y hora del registro |

---

### 3.2 Capa Repository

#### Clase `ProductoRepository`

| Método | Parámetros | Retorno | Descripción |
|---|---|---|---|
| `guardar(producto)` | `Producto` | `bool` | Inserta un nuevo producto en la BD |
| `existeCodigo(codigo)` | `string` | `bool` | Verifica si el código ya está registrado |
| `listarTodos()` | — | `array<Producto>` | Devuelve todos los productos |
| `obtenerPorId(id)` | `int` | `Producto` | Devuelve un producto por su ID |
| `actualizarStock(id, nuevoStock)` | `int, int` | `bool` | Actualiza el stock_actual de un producto |

#### Clase `VentaRepository`

| Método | Parámetros | Retorno | Descripción |
|---|---|---|---|
| `guardar(venta)` | `Venta` | `bool` | Inserta un nuevo registro de venta |
| `listarPorFecha(fecha)` | `string` | `array<Venta>` | Devuelve las ventas de una fecha dada |
| `totalDelDia(fecha)` | `string` | `float` | Suma los totales de todas las ventas del día |

---

### 3.3 Capa Service

#### Clase `ProductoService`

| Método | Parámetros | Retorno | Descripción |
|---|---|---|---|
| `registrarProducto(datos)` | `array` | `array` | Valida RN03 y RN04, luego llama al Repository para guardar |
| `listarProductos()` | — | `array<Producto>` | Obtiene todos los productos del Repository |

#### Clase `VentaService`

| Método | Parámetros | Retorno | Descripción |
|---|---|---|---|
| `registrarVenta(id_producto, cantidad)` | `int, int` | `array` | Valida RN01 y RN02, ejecuta la transacción atómica (RN05) |
| `listarVentasDelDia()` | — | `array` | Devuelve ventas del día y total acumulado |

---

### 3.4 Capa Controller

#### Clase `ProductoController`

| Método | Tipo HTTP | Descripción |
|---|---|---|
| `mostrarFormulario()` | GET | Carga la vista del formulario de registro de producto |
| `procesarRegistro()` | POST | Recibe datos del formulario, llama a `ProductoService::registrarProducto()` |
| `listar()` | GET | Llama a `ProductoService::listarProductos()` y carga la vista de listado |

#### Clase `VentaController`

| Método | Tipo HTTP | Descripción |
|---|---|---|
| `mostrarFormulario()` | GET | Carga la vista del formulario de registro de venta |
| `procesarVenta()` | POST | Recibe datos del formulario, llama a `VentaService::registrarVenta()` |
| `listarDelDia()` | GET | Llama a `VentaService::listarVentasDelDia()` y carga la vista de ventas |

---

## 4. Transacción Atómica — Venta y Descuento de Stock (RN05)

La regla de negocio RN05 exige que el registro de la venta y el descuento del stock ocurran **juntos o no ocurra ninguno**. Esto se implementa usando una **transacción de base de datos** en la capa Repository, coordinada desde la capa Service.

### Flujo de la transacción

```
VentaService::registrarVenta(id_producto, cantidad)
│
├─ 1. Valida RN01: cantidad > 0             → Si no, retorna error sin tocar BD
├─ 2. Consulta stock actual del producto   → ProductoRepository::obtenerPorId()
├─ 3. Valida RN02: cantidad ≤ stock_actual  → Si no, retorna error sin tocar BD
├─ 4. Calcula total = precio_venta × cantidad
│
└─ 5. Inicia transacción (BEGIN)
       ├─ 6a. INSERT en tabla venta         → VentaRepository::guardar()
       ├─ 6b. UPDATE stock en tabla producto → ProductoRepository::actualizarStock()
       │
       ├─ Si ambas operaciones exitosas → COMMIT  ✔ Datos persistidos
       └─ Si cualquiera falla          → ROLLBACK ✘ Nada queda guardado
```

### Pseudocódigo (PHP)

```php
// En VentaRepository (o en una clase de conexión compartida)
function registrarVentaConStock(Venta $venta, int $nuevoStock): bool {
    $pdo->beginTransaction();
    try {
        // Paso 6a: guardar la venta
        $stmt1 = $pdo->prepare("INSERT INTO venta (id_producto, cantidad, precio_unitario, total) VALUES (?,?,?,?)");
        $stmt1->execute([$venta->id_producto, $venta->cantidad, $venta->precio_unitario, $venta->total]);

        // Paso 6b: descontar el stock
        $stmt2 = $pdo->prepare("UPDATE producto SET stock_actual = ? WHERE id_producto = ?");
        $stmt2->execute([$nuevoStock, $venta->id_producto]);

        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        return false;  // Ningún cambio quedó en la BD
    }
}
```

---

## 5. Manejo de Errores

El sistema distingue tres tipos de error y define una respuesta clara para cada uno.

| Tipo de error | Origen | Capa que lo detecta | Respuesta al cajero |
|---|---|---|---|
| **Validación de regla de negocio** | Datos inválidos del formulario (RN01, RN02, RN03, RN04) | Service | Mensaje descriptivo en pantalla; no se modifica la BD |
| **Error de transacción** | Fallo durante INSERT o UPDATE en la BD (RN05) | Repository (catch) | ROLLBACK automático; se muestra: "Error al registrar la venta. Intente nuevamente." |
| **Error de conexión a BD** | BD no disponible o caída del servidor | Repository | Se muestra: "No se pudo conectar a la base de datos. Contacte al administrador." |

### Principios aplicados

1. **El Service devuelve siempre un array de resultado** con estructura `['exito' => bool, 'mensaje' => string]`. El Controller lee este array y decide qué vista mostrar.
2. **Nunca se muestra el error técnico (SQL, stack trace) al cajero.** Solo mensajes comprensibles.
3. **El ROLLBACK es automático** dentro del bloque `try/catch` del Repository, garantizando RN05 incluso ante errores imprevistos.
