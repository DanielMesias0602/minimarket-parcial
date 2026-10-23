# TECH_DEBT.md — Deuda técnica del proyecto
**Sistema:** Aplicación web interna de ventas y control de stock  
**Negocio:** Minimarket "La Esquina" — Cañete  

Este documento registra los atajos tomados durante el desarrollo inicial (Fase 1–3) y el impacto esperado si no se corrigen.

---

## TD-01 — Enrutador manual sin framework

| Campo | Detalle |
|---|---|
| **Qué** | El enrutador de `public/index.php` es un array plano (`$rutas[...]`) con comparación directa de cadenas. No hay soporte para rutas con parámetros dinámicos (p. ej. `/producto/5`), métodos REST completos ni middlewares. |
| **Por qué se tomó** | Mantener cero dependencias externas en la Fase 1; el caso de uso actual solo necesita 6 rutas fijas. |
| **Impacto** | Agregar una ruta con ID (editar/eliminar producto) obliga a parsear la URL a mano. A partir de ~10 rutas el archivo `index.php` se vuelve difícil de mantener. No hay forma limpia de añadir middleware (p. ej. validación CSRF). |
| **Cuándo pagarlo** | Antes de implementar las operaciones de edición o eliminación de productos (Fase 4). Migrar a un enrutador ligero (p. ej. FastRoute o similar) o extraer la lógica de despacho a una clase `Router`. |

---

## TD-02 — Carga de clases con `require_once` manual

| Campo | Detalle |
|---|---|
| **Qué** | `public/index.php` carga cada archivo PHP con `require_once` explícito (14 líneas de includes). No se usa autoloading PSR-4. |
| **Por qué se tomó** | El proyecto no tiene Composer configurado; se priorizó hacer funcionar la aplicación sin gestor de dependencias en la fase inicial. |
| **Impacto** | Cada clase nueva que se agregue requiere una línea `require_once` adicional en `index.php`. Es fácil olvidar incluir un archivo y el error solo aparece en tiempo de ejecución. Imposibilita usar paquetes externos de forma estándar. |
| **Cuándo pagarlo** | Fase 4, antes de agregar nuevas clases. Configurar `composer.json` con `autoload.psr-4` apuntando a `App\` → `app/` y reemplazar todos los `require_once` por `require_once __DIR__ . '/../vendor/autoload.php'`. |

---

## TD-03 — Sin suite de pruebas automatizadas

| Campo | Detalle |
|---|---|
| **Qué** | No existe ningún test unitario ni de integración. Las reglas de negocio RN01–RN05 se validan únicamente de forma manual desde el navegador. |
| **Por qué se tomó** | El alcance de la Fase 1–3 era definir la arquitectura y la base de datos; escribir tests quedó pospuesto para no bloquear el avance de la entrega. |
| **Impacto** | Cualquier cambio en `ProductoService` o `VentaService` puede romper una regla de negocio sin que el equipo lo note hasta probar manualmente. El riesgo aumenta con cada fase de implementación. |
| **Cuándo pagarlo** | Fase 4, en paralelo con la implementación de los servicios. Configurar PHPUnit, escribir al menos un test por regla de negocio (RN01–RN05) y un test de integración para la transacción atómica (RN05). |

---

## TD-04 — `CHECK` constraints dependen de la versión de MySQL/MariaDB

| Campo | Detalle |
|---|---|
| **Qué** | Las restricciones `CHECK` del `schema.sql` (precio ≥ 0, stock ≥ 0, cantidad > 0) son ignoradas silenciosamente por MySQL < 8.0 y MariaDB < 10.2. La validación real recae íntegramente en la capa Service. |
| **Por qué se tomó** | XAMPP en aulas suele tener versiones antiguas; escribir `CHECK` igualmente documenta la intención y funciona en versiones modernas. |
| **Impacto** | En entornos con MySQL < 8.0, un bug en el Service podría insertar datos inválidos directamente en la BD sin que el motor lo rechace, dejando el inventario en estado inconsistente. |
| **Cuándo pagarlo** | Al desplegar en un entorno de producción o compartido. Verificar la versión del motor (`SELECT VERSION()`) y, si es necesario, agregar triggers de validación como respaldo al nivel de base de datos. |
