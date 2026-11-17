# Inventario

## 1. ¿Qué hace el proyecto?

- Agregar nuevos productos con nombre, cantidad, precio y estado.
- Corregir el precio de productos registrados.
- Corregir el nombre de productos registrados.
- Ver el listado de productos registrados.
- Mostrar la fecha de registro.
- Validar que los campos no queden vacíos.
- Registrar y consultar el estado del producto (Disponible o Sin existencia).
---
## 2. ¿Qué tecnología utiliza?
- PHP 8.1.10 o compatible
- MariaDB
- HTML5
- CSS3
- Apache (usualmente con Laragon en Windows)

## 3. ¿Qué se necesita instalar?
- Laragon o xampp
- PHP
- MySQL o MariaDB
- Un navegador web 

## 4.configuro y ejecuto?
-Clonar o copiar el proyecto
-Crear la base de datos
-Configurar la conexión

## 5. Agregar precio a una base existente

Si la base de datos ya fue creada antes de agregar el campo de precio, ejecuta
`migracion_precio.sql` una sola vez sobre la base `inventario`. Los productos
existentes conservarán sus datos y recibirán un precio inicial de `0.00`.
Las instalaciones nuevas ya incluyen la columna `precio` en `base_datos.sql`.

## 6. Agregar el estado a una base existente

Si la base de datos ya existía antes de agregar el estado del producto, ejecuta
`migracion_estado.sql` una sola vez sobre la base `inventario`. Los productos
existentes recibirán el estado “Disponible” cuando tengan existencia y “Sin
existencia” cuando su cantidad sea cero o menor. Las instalaciones nuevas ya
incluyen la columna `estado` en `base_datos.sql`.

## 7. Ejecutar las pruebas

Ejecuta `php tests/test_validaciones.php` para las pruebas unitarias. Las dos
pruebas de integración usan la base configurada mediante `DB_HOST`, `DB_NAME`,
`DB_USER` y `DB_PASSWORD` (por defecto, `pruebainventario2`) y requieren que
la tabla `productos` se haya creado con `base_datos.sql`. Las pruebas guardan
los registros dentro de una transacción y revierten los cambios al terminar.

## 8. Evidencia de validación

`validarEstadoProducto()` acepta únicamente los valores exactos `Disponible` y
`Sin existencia`. `guardarProducto()` llama a esta validación antes del
`INSERT`; un valor inválido devuelve `estado_invalido` y no crea un registro.

Las cinco pruebas unitarias específicas del estado en
`tests/test_validaciones.php` cubren:

| Caso | Entrada | Resultado esperado |
|---|---|---|
| Válido | `Disponible` | Aceptado |
| Válido | `Sin existencia` | Aceptado |
| Vacío | `""` | Rechazado |
| No permitido | `Reservado` | Rechazado |
| Fuera del dominio permitido | 100 caracteres `x` | Rechazado |

Las dos pruebas de integración en `tests/test_integracion_productos.php`
ejercitan la misma función `guardarProducto()` que usa el formulario. La
primera registra un producto válido y consulta por ID para comprobar nombre,
cantidad y estado. La segunda intenta guardar el estado `Reservado`, comprueba
el error `estado_invalido` y verifica que el conteo del producto sea cero. Un
bloque `finally` revierte la transacción, incluso si una aserción falla.

**Fallo y corrección del entorno:** al intentar correr la integración en la
máquina local, MySQL no estaba iniciado y PDO devolvió
`SQLSTATE[HY000] [2002]` (conexión rechazada). No era un fallo de la validación
ni de la lógica de guardado: la base de pruebas no estaba disponible. La
corrección fue ejecutar la integración en GitHub Actions, que inicia un
servicio MySQL 8 y crea la tabla de pruebas desde `base_datos.sql` antes de
correr los tests.

**Evidencia del pipeline:** la ejecución
[#43815766135](https://github.com/MAYo199978/Inventario/actions/runs/43815766135)
terminó con `success`. El log muestra `18` pruebas unitarias correctas y `0`
fallidas; en integración muestra que el producto válido fue recuperado, que
el inválido no creó un registro y que la inserción de prueba fue revertida.
Esta ejecución es evidencia histórica de las pruebas de estado, anterior a la
incorporación de `asignado`.

## 9. Práctica PHP: explicación del campo Estado del producto

El campo `estado` indica si un producto está disponible o si ya no tiene
existencias. El formulario permite elegir una de dos opciones. PHP vuelve a
validar la opción antes de guardar y la base de datos limita los valores
posibles.

### 9.1 Reglas del campo

| Regla | Detalle |
|---|---|
| Datos permitidos | Los valores exactos `Disponible` y `Sin existencia`. |
| Datos rechazados | Campo vacío y cualquier valor distinto de las dos opciones, por ejemplo `Reservado`. |
| Límites aplicables | No se usa un límite numérico de caracteres: el campo solo admite los dos valores definidos en la lista y en el `ENUM` de la base de datos. |
| Tratamiento de espacios o formato | `guardar.php` aplica `trim()` al dato recibido. Después, `validarEstadoProducto()` compara el valor completo de forma exacta; no cambia mayúsculas ni normaliza otros formatos. |

Si no se selecciona una opción, se muestra “Debe completar todos los campos.”
Si se recibe un valor que no está permitido, se muestra “Seleccione un estado
válido para el producto.”

### 9.2 Partes modificadas

| Parte modificada | Archivo y enlace | Qué se cambió |
|---|---|---|
| Formulario | [`index.php`](./index.php) | Se agregó una lista obligatoria con las opciones `Disponible` y `Sin existencia`. |
| Tabla productos y preparación de la base de pruebas | [`base_datos.sql`](./base_datos.sql), [`migracion_estado.sql`](./migracion_estado.sql) y [`tests/conexion_pruebas.php`](./tests/conexion_pruebas.php) | La columna `estado` es un `ENUM` con los dos valores permitidos. La migración agrega la columna a bases existentes y calcula el estado inicial según la cantidad. La conexión de pruebas usa `DB_HOST`, `DB_NAME`, `DB_USER` y `DB_PASSWORD`. |
| Función de validación | [`src/validaciones.php`](./src/validaciones.php) | `validarEstadoProducto()` permite únicamente `Disponible` o `Sin existencia`, con comparación estricta. |
| Proceso de guardado | [`guardar.php`](./guardar.php) y [`src/productos.php`](./src/productos.php) | `guardar.php` recibe y recorta el dato. `guardarProducto()` lo valida antes del `INSERT`; si no es válido devuelve `estado_invalido` y no inserta. |
| Listado | [`index.php`](./index.php) | La consulta recupera el estado, lo muestra en la tabla y lo incluye en los datos que usa el buscador. |
| Mensaje de error | [`index.php`](./index.php) | Se muestra un mensaje si el estado recibido no pertenece a las opciones permitidas; un campo vacío usa el mensaje general de formulario incompleto. |

### 9.3 Cómo llega el dato desde el formulario hasta el guardado

1. En [`index.php`](./index.php), la persona elige `Disponible` o
   `Sin existencia` en el control `name="estado"`.
2. Al enviar el formulario por `POST`, el dato llega a
   [`guardar.php`](./guardar.php), que comprueba que sea una cadena y elimina
   espacios al inicio y al final.
3. Si el campo está vacío, se redirige al formulario con
   `estado=incompleto`. Si tiene contenido, se envía a `guardarProducto()`
   junto con el nombre, la cantidad y el dato de asignado.
4. En [`src/productos.php`](./src/productos.php), `guardarProducto()` llama a
   `validarEstadoProducto()` antes de preparar o ejecutar el `INSERT`. Si el
   valor no está permitido, devuelve `estado_invalido`.
5. Si pasa la validación, la consulta preparada guarda el estado en la
   columna `productos.estado`.
6. [`index.php`](./index.php) consulta el estado y lo presenta en la columna
   “Estado” del listado; también se puede usar para buscar el producto.

### 9.4 Dónde se llama a la validación y cómo se evita guardar un dato inválido

`guardarProducto()` llama a `validarEstadoProducto()` antes de ejecutar el
`INSERT`. La validación del navegador ayuda a elegir una opción, pero PHP
también revisa el valor recibido en el servidor. Si llega, por ejemplo,
`Reservado`, se devuelve `estado_invalido`, `guardar.php` redirige a
`index.php?estado=estado_invalido` y no se crea el registro.

## 10. Práctica PHP: casos y resultados

Las cinco pruebas unitarias de estado están en
[`tests/test_validaciones.php`](./tests/test_validaciones.php). Se ejecutaron
con `php tests/test_validaciones.php`; las cinco pasaron. La evidencia de las
dos pruebas de integración de estado corresponde a una ejecución histórica de
GitHub Actions. En la versión actual del archivo
[`tests/test_integracion_productos.php`](./tests/test_integracion_productos.php),
la prueba válida guarda y recupera `Disponible`, pero el caso inválido prueba
el campo `asignado` y no un estado inválido.

| ID y tipo | Caso y entrada | Esperado | Obtenido y estado |
|---|---|---|---|
| U1 Unitaria | Estado válido: `Disponible` | Aceptado | `true`; PASS |
| U2 Unitaria | Estado válido: `Sin existencia` | Aceptado | `true`; PASS |
| U3 Unitaria | Estado vacío: `""` | Rechazado | `false`; PASS |
| U4 Unitaria | Estado no permitido: `Reservado` | Rechazado | `false`; PASS |
| U5 Unitaria | Valor de 100 caracteres `x` | Rechazado | `false`; PASS |
| I1 Integración | Guardar y recuperar un producto con estado `Disponible` | El producto se guarda y el estado recuperado coincide | PASS en la ejecución histórica de GitHub Actions; la ejecución local actual quedó pendiente porque MySQL rechazó la conexión. |
| I2 Integración | Intentar guardar `Reservado` y consultar si se creó el producto | Rechazo `estado_invalido` y cero registros | PASS en la ejecución histórica de GitHub Actions; este caso no está actualmente en el archivo de integración. |

### Preparación de la base de pruebas y limpieza

Las pruebas de integración requieren que MySQL/MariaDB esté iniciado y que
exista la base `pruebainventario2` (o la indicada con `DB_NAME`). La tabla
`productos` debe tener la estructura de [`base_datos.sql`](./base_datos.sql).
Si se usa una base anterior que no tiene la columna `estado`, se debe ejecutar
[`migracion_estado.sql`](./migracion_estado.sql) una sola vez.

Las pruebas de integración abren una transacción y la revierten en el bloque
`finally`, incluso si ocurre un error. Por ello, los registros de prueba no
deben quedar guardados. Para repetirlas localmente, inicia MySQL/MariaDB y
ejecuta `php tests/test_integracion_productos.php`.

## 11. Evidencias del proceso PHP

| Evidencia | Comando o paso | Resultado observado y qué demuestra |
|---|---|---|
| Ejecución de pruebas unitarias | `php tests/test_validaciones.php` | 23 pruebas correctas y 0 fallidas en total; los cinco casos de `estado` pasaron. |
| Falla y causa identificada | `php tests/test_integracion_productos.php` | Error `SQLSTATE[HY000] [2002]`: MySQL rechazó la conexión local. Es un problema de disponibilidad del servicio de base de datos, no un fallo de la regla de estado. |
| Ejecución correcta de integración | [Ejecución histórica de GitHub Actions](https://github.com/MAYo199978/Inventario/actions/runs/43815766135) | Terminó con `success`: el producto válido se recuperó, el estado inválido fue rechazado sin crear un registro y la transacción se revirtió. Es evidencia histórica de las pruebas de estado. |

La ejecución histórica es evidencia del cambio de estado de esa versión. Para
obtener evidencia actualizada de la integración, inicia MySQL/MariaDB y vuelve
a ejecutar el comando. La captura debe mostrar el guardado y recuperación del
estado válido, además del rechazo de un estado inválido.

## 12. Aspectos que todavía no comprueban estas pruebas

Las pruebas unitarias verifican la función de validación, pero no prueban por
sí solas el formulario desde un navegador. La prueba de integración actual
comprueba el guardado y recuperación del estado `Disponible`, pero su caso
inválido se refiere a `asignado`; por ello, no verifica actualmente el rechazo
de un estado inválido en la base de pruebas. Tampoco se verifican concurrencia,
permisos de usuarios ni el despliegue en un servidor de producción.
