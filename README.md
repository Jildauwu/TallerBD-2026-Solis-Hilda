Sistema de control de préstamos de equipo tecnológico



Descripción del proyecto:



Este proyecto consiste en desarrollar una aplicación web para llevar el control de los prestamos de equipos tecnológicos de una institución.



El sistema permitirá registrar equipos, solicitantes, prestamos y devoluciones, además de consultar la disponibilidad de cada equipo.



Tecnologías que se usaran en este proyecto:



Linux



Apache



PHP 8



MariaDB



HTML y CSS



por el momento solo usamos mariadb para el DDL



Base de datos 



La base de datos se llama control\_prestamos y contiene las sig tablas:



1.-Categorias: almacena los tipos de equipos tecnológicos.

2.-Equipos: registra los equipos y su estado actual.

3.-Usuarios: registra a los encargados del sistema

5.-Prestamos: almacena los prestamos realizados.

6.-Devoluciones: registra la devolución de los equipos



Relación entre tablas 



\-Una categoría puede tener varios equipos

\-Un equipo puede tener varios prestamos en diferentes fechas

\-Un solicitante puede realizar varios prestamos

\-Un usuario puede registrar varios prestamos

\-Un préstamo puede tener una devolución



Primer avance



Realice el diseño inicial de la base de datos mediante el DDL incluyendo tablas, llaves primarias, foráneas y relaciones









