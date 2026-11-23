&#x20;Sistema de Control de Préstamos de Equipo Tecnológico

(Actualización 2)



&#x20;Descripción del proyecto



Este proyecto consiste en desarrollar una aplicación web para administrar los préstamos de equipos tecnológicos de una institución.



El sistema permite registrar equipos, solicitantes, préstamos y devoluciones, además de consultar la disponibilidad de los equipos y mantener un historial de los movimientos realizados.



El aplicativo utiliza una base de datos relacional en MariaDB y un backend desarrollado con PHP 8 para ejecutar las operaciones SQL.



&#x20;Tecnologías utilizadas



\- Linux (Debian)

\- Apache

\- PHP 8

\- MariaDB

\- HTML5

\- CSS3

\- JavaScript

\- Git y GitHub

\- Visual Studio Code

\- FileZilla (SFTP)



&#x20;Base de datos



La base de datos se llama `control\_prestamos`.



Contiene las siguientes tablas:



1\. \*\*categorias:\*\* almacena los tipos de equipos tecnológicos.

2\. \*\*departamentos:\*\* registra los departamentos de la institución.

3\. \*\*equipos:\*\* almacena la información y disponibilidad de los equipos.

4\. \*\*solicitantes:\*\* registra las personas que solicitan equipos.

5\. \*\*usuarios:\*\* almacena los usuarios responsables del sistema.

6\. \*\*prestamos:\*\* registra los préstamos realizados.

7\. \*\*devoluciones:\*\* almacena las devoluciones de los equipos.



&#x20;Relaciones entre tablas



\- Una categoría puede tener varios equipos.

\- Un departamento puede tener varios solicitantes.

\- Un equipo puede tener varios préstamos en distintas fechas.

\- Un solicitante puede realizar varios préstamos.

\- Un usuario puede registrar varios préstamos.

\- Un préstamo puede tener una devolución.



Estas relaciones se implementan mediante claves primarias y foráneas.



&#x20;Estructura del proyecto



&#x20;Carpeta Backend



Contiene los archivos PHP responsables de la conexión y de las operaciones con MariaDB.



\- `conexion.php`

\- `guardar\_equipo.php`

\- `listar\_equipos.php`

\- `actualizar\_equipo.php`

\- `desactivar\_equipo.php`

\- `eliminar\_categoria.php`

\- `guardar\_solicitante.php`

\- `listar\_solicitantes.php`

\- `listar\_departamentos.php`

\- `guardar\_prestamo.php`

\- `listar\_prestamos.php`

\- `datos\_prestamo.php`

\- `prestamos\_activos.php`

\- `registrar\_devolucion.php`

\- `listar\_devoluciones.php`



&#x20;Carpeta Fronted



Contiene los archivos de la interfaz web.



\- `index.html`: página principal.

\- `equipos.html`: registro y administración de equipos.

\- `solicitantes.html`: registro y consulta de solicitantes.

\- `prestamos.html`: registro e historial de préstamos.

\- `devoluciones.html`: registro e historial de devoluciones.

\- `estilos.css`: estilos de la aplicación.

\- `script.js`: funcionamiento de los formularios y comunicación con PHP.



&#x20;Primer avance: Base de datos



Se desarrolló el diseño inicial de la base de datos mediante instrucciones DDL.



Se crearon las tablas, claves primarias, claves foráneas y relaciones necesarias para el funcionamiento del sistema.



También se realizaron ajustes para mejorar la integridad de los datos y evitar inconsistencias en el registro de préstamos.



&#x20;Segundo avance: Frontend



Se desarrolló la estructura visual del aplicativo utilizando HTML, CSS y JavaScript.



Se implementaron:



\- Página principal.

\- Menú de navegación.

\- Formulario de registro de equipos.

\- Formulario de solicitantes.

\- Formulario de préstamos.

\- Formulario de devoluciones.

\- Tablas para mostrar información.

\- Estilos para diferentes tamaños de pantalla.



En esta etapa se prepararon los componentes visuales para conectarlos posteriormente con MariaDB.



&#x20;Tercer avance: Backend e integración SQL



Para el tercer avance se desarrolló el backend utilizando PHP 8 y MariaDB.



&#x20;1. Conexión a MariaDB



Archivo: `conexion.php`



Se implementó la conexión mediante PDO.



La contraseña de la base de datos se obtiene mediante la variable de entorno `DB\_PASSWORD`, evitando escribirla directamente en el código fuente.



&#x20;2. SELECT: Consultar registros



Archivos:



\- `listar\_equipos.php`

\- `listar\_solicitantes.php`

\- `listar\_prestamos.php`

\- `listar\_devoluciones.php`

\- `listar\_departamentos.php`

\- `prestamos\_activos.php`



Permiten consultar información almacenada en MariaDB y devolver los resultados en formato JSON.



&#x20;3. INSERT: Registrar información



Archivos:



\- `guardar\_equipo.php`

\- `guardar\_solicitante.php`

\- `guardar\_prestamo.php`

\- `registrar\_devolucion.php`



Permiten insertar nuevos registros mediante consultas parametrizadas.



&#x20;4. UPDATE: Modificar información



Archivos:



\- `actualizar\_equipo.php`

\- `desactivar\_equipo.php`

\- `registrar\_devolucion.php`



Permiten actualizar registros existentes.



La desactivación de equipos es lógica, mediante una actualización del campo `activo`.



Al registrar una devolución, también se actualiza el estado del préstamo y del equipo.



&#x20;5. DELETE: Eliminar registros



Archivo: `eliminar\_categoria.php`



Se implementó una operación DELETE para eliminar categorías que no tengan equipos asociados.



El archivo:



\- Recibe una solicitud POST con el identificador de la categoría.

\- Comprueba que la categoría exista.

\- Verifica que no tenga equipos relacionados.

\- Ejecuta una consulta DELETE parametrizada.

\- Utiliza una transacción para controlar la operación.



Esta funcionalidad fue probada en el entorno de desarrollo.



La eliminación se realiza mediante el backend; no se incorporó un botón DELETE al frontend.



&#x20;6. JOIN: Consultas relacionadas



Archivos:



\- `listar\_prestamos.php`

\- `listar\_solicitantes.php`

\- `listar\_devoluciones.php`



Se utilizan consultas JOIN para relacionar información de diferentes tablas.



Por ejemplo, el historial de préstamos permite consultar el equipo prestado, el solicitante, el responsable, las fechas y el estado.



&#x20;7. Consultas parametrizadas



Las operaciones que procesan datos proporcionados por el usuario utilizan consultas preparadas con PDO.



Se emplean los métodos:



\- `prepare()`

\- `execute()`



Esto evita construir instrucciones SQL mediante la concatenación directa de los datos recibidos.



&#x20;8. Transacciones



El registro de préstamos y devoluciones utiliza transacciones.



Al registrar un préstamo:



1\. Se verifica la disponibilidad del equipo.

2\. Se registra el préstamo.

3\. Se cambia el estado del equipo a prestado.

4\. Se confirman los cambios.



Al registrar una devolución:



1\. Se verifica el préstamo activo.

2\. Se registra la devolución.

3\. Se cambia el estado del préstamo a devuelto.

4\. Se actualiza la disponibilidad del equipo.

5\. Se confirman los cambios.



Si ocurre un error, la transacción permite revertir las modificaciones.



&#x20;Funcionamiento de la aplicación



&#x20;Equipos



Permite registrar, consultar, editar y desactivar equipos tecnológicos.



&#x20;Solicitantes



Permite registrar solicitantes y consultar su departamento correspondiente.



&#x20;Préstamos



Permite registrar préstamos, consultar el historial e impedir que un equipo prestado se registre nuevamente como disponible.



&#x20;Devoluciones



Permite registrar devoluciones, consultar el historial y actualizar los estados correspondientes en la base de datos.



&#x20;Ejecución en el entorno de desarrollo



La aplicación se ha probado utilizando Debian, Apache, PHP y MariaDB.



Para ejecutarla se requiere:



1\. Tener Apache, PHP 8 y MariaDB instalados y configurados.

2\. Crear la base de datos `control\_prestamos` con su estructura de tablas y relaciones.

3\. Configurar un usuario de MariaDB con los permisos necesarios.

4\. Configurar la conexión PDO y la variable de entorno `DB\_PASSWORD`.

5\. Colocar los archivos PHP del backend en el directorio de publicación configurado en Apache.

6\. Colocar la carpeta `Fronted` dentro del directorio de la aplicación.

7\. Abrir la página principal desde un navegador.



En el entorno utilizado durante el desarrollo, la página principal se encuentra en:



`http://192.168.67.128/control\_prestamos/Fronted/index.html`



Esta dirección corresponde a una IP privada de desarrollo y puede cambiar según la configuración de red.



&#x20;Verificación de operaciones



Las operaciones SELECT pueden comprobarse mediante los endpoints PHP de consulta.



Las operaciones INSERT y UPDATE pueden verificarse desde los formularios de la aplicación y posteriormente consultando los registros en MariaDB.



El DELETE puede verificarse realizando una solicitud POST a `eliminar\_categoria.php`, enviando un JSON con el campo `id\_categoria` correspondiente a una categoría de prueba sin equipos asociados.



Ejemplo del contenido JSON:



```json

{

&#x20; "id\_categoria": 5

}

```



El identificador 5 es únicamente ilustrativo y debe sustituirse por el ID real de una categoría creada para la prueba.



Después se puede ejecutar un SELECT en MariaDB para comprobar que el registro fue eliminado.



La operación DELETE debe probarse únicamente en un entorno de desarrollo controlado.



&#x20;Seguridad



\- La contraseña de MariaDB se obtiene mediante una variable de entorno.

\- Se utilizan consultas parametrizadas para procesar los datos del usuario.

\- Se validan diferentes campos antes de realizar operaciones.

\- Se utilizan transacciones para operaciones relacionadas.

\- No se deben publicar contraseñas ni archivos privados de configuración.



La autenticación y autorización de los endpoints todavía requieren trabajo adicional antes de publicar la aplicación en un servidor accesible por Internet.



&#x20;Estado actual



El tercer avance incorpora la conexión entre PHP y MariaDB, así como operaciones SELECT, INSERT, UPDATE, DELETE y JOIN.



Se realizaron pruebas de registro, consulta, modificación y eliminación, además del funcionamiento de préstamos y devoluciones.



El proyecto continuará desarrollándose para completar los requisitos de la entrega final.

