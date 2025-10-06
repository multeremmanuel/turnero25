# Turnero - Sistema de Turnos

Turnero es un sistema de gestión de turnos desarrollado en PHP y MariaDB, que se integra con EasyAppointments. Permite asignar turnos normales, preferentes y especiales según la cita del usuario o sus necesidades (adultos mayores, embarazadas, personas con discapacidad, etc.).

## Roles

* Despachador: Otorga los turnos según la situación del usuario.
* Operador: Recibe y atiende los turnos asignados (cajero, asesor, etc.).
* Administrador: Administra el sistema, genera reportes, gestiona usuarios, cajas y módulos.

## Flujo de turnos

1. El usuario llega y proporciona su ID de cita.
2. El despachador ingresa el ID en Turnero:

   * Dentro del rango de cita → turno preferente.
   * Fuera del rango → turno normal.
3. Usuarios con necesidades especiales → turno especial.
4. Usuarios sin cita y sin necesidades especiales → turno normal.

Todos los turnos se registran y se pueden generar reportes por usuario, caja y fechas.

## Nota sobre la base de datos

El archivo .sql no está incluido en este repositorio. Para implementar el sistema en tu servidor, el archivo de la base de datos se enviará únicamente a quienes contribuyan o apoyen el proyecto.
url demo: https://turnero.estructuras.cloud 
usuarios
admin - admin (Administra el sistema)
prueba - prueba (cajero/asesor)
despachador - despachador (Asigna turnos)
