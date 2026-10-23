
================================================================

Fase 1 · D0.md (min 10–20) · Commit 1
Hola soy un estudiante de la carrera de ingenieria de sistemas del VI Ciclo, necesito que me ayudes a armar el archivo D0.md para mi examen. 
El caso es de un minimarket en Cañete ("La Esquina") que vende abarrotes, lácteos, etc El problema es que hoy anotan todo en un cuaderno y revisan el stock mirando los estantes, así que no saben cuánto vendieron al día y a veces se quedan sin productos, eso puede terminar siendo un gran problema para el futuro del negocio. El dueño quiere una aplicacion web interna usado por el cajero segun el caso mencionado

Te paso las cosas exactas que tiene que llevar el D0.md:
1. El problema en una sola frase.
2. Los usuarios y el proceso actual (con su dificultad).
3. Los 4 requisitos funcionales (RF): registrar productos, listar resaltando los que están por debajo del mínimo, registrar una venta descontando stock, y listar ventas del día con el total.
4. Los requisitos no funcionales (RNF) de seguridad e integridad de datos.
5. Las 5 reglas de negocio (RN) que me pide el caso: cantidad > 0, no vender más del stock, precio no negativo, código único, y que la venta y el descuento de stock sea todo junto o nada.
6. Qué está fuera de alcance (sin login, clientes ni boletas) y agrega un SUPUESTO.

Por favor, no inventes cosas que no estén en el caso, solo redacta el D0.md con eso. ANALIZA ANTES DE ACTUAR Y HACER, NO HACEMOS VIDECODING. vamos 

================================================================
Fase 2 · requirements.md y design.md en Kiro (min 20–45)· Commit 2

kiro ahora pasemos a la fase 2. Ya sabes que tenemos D0.md (Con la siguiente corrección: Corregí el RF01 agregando la palabra "categoría" y "precio de venta". | Kiro omitió la categoría en el primer requisito funcional, y la profesora la pide explícitamente en el caso original y segun el caso de la bodega), necesito que me generes dos archivos nuevos.
El primero es requirements.md. Ten en cuenta los requisitos funcionales (RF) y no funcionales (RNF) tienen que estar redactados en formato EARS sí o sí, o sea que empiecen con 'CUANDO... EL SISTEMA DEBERÁ...'. Asegúrate de que cada una de las 5 reglas de negocio (RN) del D0.md aparezca al menos un criterio de aceptación.
El segundo es design.md. Necesito la arquitectura en 5 capas (Model, Repository, Service, Controller, Views) explicando qué hace y qué no hace cada una. Tambien debes agregar:
- El código SQL para crear las tablas `producto` y `venta` (con nombres en español y snake_case, que tengan llave primaria, foránea, que el código sea único y restricciones para que el precio y el stock no sean negativos y la cantidad sea mayor a cero).
- Las clases con sus métodos y qué tipos de datos usan (int, string, float, etc.).
- Una explicación de cómo se va a hacer para que la venta y el descuento del stock se hagan juntos o no se guarde nada si hay error.
- Cómo vamos a manejar los errores si algo falla.

No generes tasks.md ni nada de eso, solo dame el contenido de los dos archivos para yo copiarlo a VS Code. No inventes cosas fuera del caso.

Fase 2 - Correcciones
Prompt enviado a Kiro:
"Kiro, un par de correcciones rápidas: 
1. En requirements.md (RF03), el caso original dice venta de 'un producto' (singular), no de varios. Cámbialo.
2. En design.md, agrega la columna `precio_unitario` en la tabla `venta` del SQL. 
3. Porfa mueve los dos archivos .md a la raíz del proyecto (al lado de D0.md) y borra la carpeta `.kiro`. Gracias."

================================================================
Fase 3 - Base de datos

Oye Kiro, para la Fase 3 ya creé la carpeta database y los archivos vacíos schema.sql y seed.sql en VS Code. Por favor, NO ejecutes nada en la terminal ni en la base de datos, porque mi profesora exige que yo lo importe manualmente en phpMyAdmin.

Solo necesito que escribas el código dentro de los archivos:

En database/schema.sql: escribe el código SQL para crear la base de datos bodega_ventas (en utf8mb4) y las tablas producto y venta (con sus llaves primarias, la foránea, el UNIQUE para el código y los CHECK para precio >= 0, stock >= 0 y cantidad > 0).

En database/seed.sql: escribe los INSERT INTO para meter los 8 productos de prueba (del P001 al P008) exactos del caso.

P001, Arroz superior 5 kg, Abarrotes, 22.50, 20, 5
P002, Azúcar rubia 1 kg, Abarrotes, 4.20, 30, 10
P003, Aceite vegetal 1 L, Abarrotes, 9.90, 12, 4
P004, Leche evaporada 400 g, Lácteos, 4.00, 48, 12
P005, Fideo spaghetti 500 g, Abarrotes, 3.50, 25, 8
P006, Atún en trozos 170 g, Conservas, 6.50, 3, 6
P007, Gaseosa 1.5 L, Bebidas, 7.00, 18, 6
P008, Detergente 1 kg, Limpieza, 11.80, 2, 4

En la raíz del proyecto, crea o escribe dentro del archivo .env las variables: DB_HOST=localhost, DB_NAME=bodega_ventas, DB_USER=root y DB_PASS= (vacío).

En la raíz, escribe dentro del archivo .gitignore la palabra .env para que no se suban las credenciales a GitHub.

Por favor, redacta el código directamente en los archivos y avísame cuando estén listos.