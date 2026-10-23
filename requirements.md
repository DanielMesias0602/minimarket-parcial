# Requirements — Sistema de Ventas y Control de Stock
**Negocio:** Minimarket "La Esquina" — Cañete
**Versión:** 1.0

---

## 1. Requisitos Funcionales

---

### RF01 — Registrar Producto

**Formato EARS:**
> CUANDO el cajero ingrese los datos de un nuevo producto (código, nombre, categoría, precio de venta, stock actual y stock mínimo) y confirme el registro, EL SISTEMA DEBERÁ guardar el producto en la base de datos y mostrarlo en el listado de productos.

#### Criterios de Aceptación

| ID | Criterio |
|---|---|
| RF01-CA01 | El sistema acepta y persiste los campos: código, nombre, categoría, precio de venta, stock actual y stock mínimo. |
| RF01-CA02 | **(RN03)** Si el precio de venta ingresado es negativo, el sistema rechaza el registro y muestra el mensaje: "El precio no puede ser negativo." |
| RF01-CA03 | **(RN04)** Si el código ingresado ya existe en el sistema, el registro es rechazado y se muestra el mensaje: "El código de producto ya existe." |
| RF01-CA04 | Si todos los datos son válidos, el producto queda disponible inmediatamente en el listado general. |

---

### RF02 — Listar Productos con Alerta de Stock

**Formato EARS:**
> CUANDO el cajero acceda a la sección de productos, EL SISTEMA DEBERÁ mostrar la lista completa de productos registrados y resaltar visualmente aquellos cuyo stock actual sea menor al stock mínimo definido.

#### Criterios de Aceptación

| ID | Criterio |
|---|---|
| RF02-CA01 | La lista muestra todos los productos con sus campos: código, nombre, categoría, precio de venta, stock actual y stock mínimo. |
| RF02-CA02 | Los productos cuyo stock actual < stock mínimo aparecen resaltados de forma diferenciada (por ejemplo, en color rojo o con un ícono de alerta). |
| RF02-CA03 | Los productos con stock actual ≥ stock mínimo se muestran sin resaltado especial. |
| RF02-CA04 | Si no existen productos registrados, el sistema muestra el mensaje: "No hay productos registrados." |

---

### RF03 — Registrar Venta con Descuento de Stock

**Formato EARS:**
> CUANDO el cajero seleccione un producto con su cantidad y confirme la venta, EL SISTEMA DEBERÁ registrar la venta, calcular el total, descontar el stock del producto vendido y mostrar la confirmación de la operación.

#### Criterios de Aceptación

| ID | Criterio |
|---|---|
| RF03-CA01 | **(RN01)** Si la cantidad ingresada para algún producto es cero o negativa, el sistema rechaza la venta y muestra el mensaje: "La cantidad debe ser mayor a cero." |
| RF03-CA02 | **(RN02)** Si la cantidad solicitada de algún producto supera su stock disponible, el sistema rechaza la venta y muestra el mensaje: "Stock insuficiente para el producto [nombre]." |
| RF03-CA03 | **(RN05)** El registro de la venta y el descuento del stock se realizan de forma atómica: si ocurre un error en cualquier parte de la operación, ningún cambio (ni en la tabla de ventas ni en el stock) es persistido. |
| RF03-CA04 | Si la venta es exitosa, el stock del producto vendido queda actualizado inmediatamente. |
| RF03-CA05 | El sistema registra la fecha y hora de la venta en el momento de la confirmación. |
| RF03-CA06 | El sistema calcula y almacena el total de la venta como precio_venta × cantidad del producto vendido. |

---

### RF04 — Listar Ventas del Día con Total

**Formato EARS:**
> CUANDO el cajero acceda a la sección de ventas del día, EL SISTEMA DEBERÁ mostrar todas las ventas registradas en la fecha actual y el monto total acumulado de dichas ventas.

#### Criterios de Aceptación

| ID | Criterio |
|---|---|
| RF04-CA01 | La lista muestra únicamente las ventas cuya fecha coincide con la fecha actual del sistema. |
| RF04-CA02 | Cada fila de la lista incluye: identificador de venta, fecha/hora y total de esa venta. |
| RF04-CA03 | Al pie de la lista se muestra el total acumulado del día, calculado como la suma de los totales de todas las ventas listadas. |
| RF04-CA04 | Si no se realizaron ventas en la fecha actual, el sistema muestra el mensaje: "No hay ventas registradas hoy." y el total acumulado es S/ 0.00. |

---

## 2. Requisitos No Funcionales

---

### RNF01 — Integridad de Datos

**Formato EARS:**
> SIEMPRE QUE el sistema ejecute una operación que modifique datos de stock o ventas, EL SISTEMA DEBERÁ garantizar que los datos queden en un estado consistente, sin registros parciales ni valores fuera de los límites permitidos.

#### Criterios de Aceptación

| ID | Criterio |
|---|---|
| RNF01-CA01 | El stock de un producto nunca puede quedar con valor negativo como resultado de una venta. |
| RNF01-CA02 | No puede existir un registro de venta sin que el stock del producto involucrado haya sido descontado correctamente, y viceversa. |
| RNF01-CA03 | La base de datos aplica restricciones (constraints) a nivel de motor para reforzar precio ≥ 0, stock ≥ 0 y cantidad > 0. |

---

### RNF02 — Seguridad de Datos

**Formato EARS:**
> SIEMPRE QUE se registre o modifique información en el sistema, EL SISTEMA DEBERÁ almacenar los datos de forma persistente, previniendo pérdida o corrupción ante fallos durante la operación.

#### Criterios de Aceptación

| ID | Criterio |
|---|---|
| RNF02-CA01 | Las transacciones de base de datos siguen el mecanismo de commit/rollback para que ante un fallo no queden datos a medias. |
| RNF02-CA02 | Los datos de ventas y productos no se pierden ante un cierre inesperado del navegador o del servidor, siempre que la transacción haya sido confirmada (commit). |
