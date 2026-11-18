SISTEMA DE CONTROL DE PRESTAMOS DE EQUIPO TECNOLOGICO 

(Actualización)



Descripción del proyecto



Este proyecto consiste en desarrollar una aplicación web para llevar el control de los prestamos de equipos tecnológicos de una institución.

&#x20;el sistemas permitirá registrar equipos solicitantes, prestamos y devoluciones, además de consultar la disponibilidad de cada equipo.



Tecnologías que se usaran en este proyecto:

\-Linux 

\-Apache

\-PHP 8

\-MariaDB

\-HTML y CSS

\-JavaScript



por e momento se utilizo MariaDb para realizar la estructura DDL de la base de datos



Base de Datos



La base de datos se llama Control\_prestamos y contiene las siguientes tablas:



1.-Categorías: almacena los tipos de equipos tecnológicos.

2.-Equipos: registra los equipos, sus características y su estado actual.

3.-Solicitantes: guarda los datos de las personas que solicitan equipos.

4.-Usuarios: registra a los encargados del sistema.

5.-Prestamos: almacena los préstamos realizados.

6.-Devoluciones: registra la devolución de los equipos.

7.-Departamentos: almacena los departamentos a los que pertenecen los solicitantes.



Relación entre tablas:



\-Una categoría puede tener varios equipos.

\-Un equipo puede tener varios préstamos en diferentes fechas.

\-Un solicitante puede realizar varios préstamos.

\-Un usuario puede registrar varios préstamos.

\-Un préstamo puede tener una devolución.

\-Un departamento puede tener varios solicitantes.



Primer avance

Realicé el diseño inicial de la base de datos mediante DDL, incluyendo seis tablas, llaves primarias, llaves foráneas y relaciones.



También agregué restricciones como PRIMARY KEY, FOREIGN KEY, UNIQUE y CHECK para mantener la integridad de los datos.



Modificaciones después de la revisión 



Las siguientes modificaciones que realice fueron gracias a las observaciones que realizo el Maestro.





1\. Se agregó la tabla departamentos.



Esta tabla permite almacenar los nombres de los departamentos de la institución sin tener que repetirlos en cada solicitante.



2\. Se modificó la tabla solicitantes.



Anteriormente tenía un campo llamado departamento, donde se guardaba directamente el nombre. Ahora se utiliza id\_departamento como llave foránea para relacionarla con la nueva tabla.



3\. Se actualizó la relación entre tablas.



Se agregó la relación entre departamentos y solicitantes, donde un departamento puede tener varios solicitantes.



4\. Se mantuvieron las restricciones originales.



Se conservaron las llaves primarias, foráneas y restricciones para validar la información de las tablas.



Con estas modificaciones, la base de datos pasó de tener seis tablas a siete.



Estado actual



La base de datos cuenta con siete tablas relacionadas y su estructura DDL.



Estas modificaciones permiten tener una mejor organización de la información y continuar con el desarrollo de la aplicación web.



