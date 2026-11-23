
    ////////////////////////////////////////////////////////
    // MÓDULO DE EQUIPOS
    ////////////////////////////////////////////////////////
    document.addEventListener("DOMContentLoaded", function () {
        const formulario = document.getElementById("formEquipos");
        const tabla = document.getElementById("tablaEquipos");
        const seccionEditar = document.getElementById("seccionEditar");
        const formEditar = document.getElementById("formEditarEquipo");
        const cancelarEdicion = document.getElementById("cancelarEdicion");
        // Ejecutar solamente en la página de equipos
        if (!formulario || !tabla) {
            return;
        }
        // Consultar los equipos registrados en MariaDB
        async function cargarEquipos() {
            try {
                const respuesta = await fetch("../listar_equipos.php");
                if (!respuesta.ok) {
                    throw new Error("No se pudieron consultar los equipos");
                }
                const resultado = await respuesta.json();
                if (!Array.isArray(resultado.equipos)) {
                    throw new Error("Respuesta de datos inválida");
                }
                tabla.replaceChildren();
                resultado.equipos.forEach(function (equipo) {
                    const fila = tabla.insertRow();
                    [
                        equipo.num_inventario,
                        equipo.descripcion,
                        equipo.marca ?? "",
                        equipo.modelo ?? "",
                        equipo.categoria,
                        equipo.estado
                    ].forEach(function (valor) {
                        const celda = fila.insertCell();
                        celda.textContent = valor;
                    });
                    // Crear botón Editar
                    const celdaAcciones = fila.insertCell();
                    const botonEditar = document.createElement("button");
                    botonEditar.type = "button";
                    botonEditar.textContent = "Editar";
                    botonEditar.addEventListener("click", function () {
                        document.getElementById("editarId").value =
                            equipo.id_equipo;
                        document.getElementById("editarDescripcion").value =
                            equipo.descripcion;
                        document.getElementById("editarMarca").value =
                            equipo.marca ?? "";
                        document.getElementById("editarModelo").value =
                            equipo.modelo ?? "";
                        document.getElementById("editarCondicion").value =
                            equipo.condicion ?? "";
                        seccionEditar.hidden = false;
                        seccionEditar.scrollIntoView({
                            behavior: "smooth"
                        });
                    });
                    celdaAcciones.appendChild(botonEditar);
                    // Crear botón Desactivar
                    const botonDesactivar = document.createElement("button");
                    botonDesactivar.type = "button";
                    botonDesactivar.textContent = "Desactivar";
                    botonDesactivar.addEventListener("click", async function () {
                        const confirmar = confirm(
                            "¿Seguro que deseas desactivar el equipo " +
                            equipo.num_inventario + "?"
                        );
                        if (!confirmar) {
                            return;
                        }
                        botonDesactivar.disabled = true;
                        try {
                            const respuesta = await fetch(
                                "../desactivar_equipo.php",
                                {
                                    method: "POST",
                                    headers: {
                                        "Content-Type": "application/json"
                                    },
                                    body: JSON.stringify({
                                        id_equipo: equipo.id_equipo
                                    })
                                }
                            );
                            const resultado = await respuesta.json();
                            if (!respuesta.ok) {
                                alert(
                                    resultado.error ||
                                    "No se pudo desactivar el equipo"
                                );
                                return;
                            }
                            alert(resultado.mensaje);
                            await cargarEquipos();
                        } catch (error) {
                            console.error("Error al desactivar:", error);
                            alert("No fue posible desactivar el equipo");
                        } finally {
                            botonDesactivar.disabled = false;
                        }
                    });
                    celdaAcciones.appendChild(botonDesactivar);
                });
            } catch (error) {
                console.error("Error al cargar equipos:", error);
                alert("No se pudieron cargar los equipos");
            }
        }
        // Cargar equipos al abrir la página
        cargarEquipos();
        // Registrar un equipo nuevo
        formulario.addEventListener("submit", async function (evento) {
            evento.preventDefault();
            if (!formulario.reportValidity()) {
                return;
            }
            const datos = {
                inventario: document.getElementById("inventario").value.trim(),
                descripcion: document.getElementById("descripcion").value.trim(),
                marca: document.getElementById("marca").value.trim(),
                modelo: document.getElementById("modelo").value.trim(),
                categoria: document.getElementById("categoria").value,
                condicion: document.getElementById("condicion").value.trim()
            };
            const boton = formulario.querySelector('button[type="submit"]');
            boton.disabled = true;
            try {
                const respuesta = await fetch("../guardar_equipo.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify(datos)
                });
                const resultado = await respuesta.json();
                if (!respuesta.ok) {
                    alert(resultado.error || "No se pudo guardar el equipo");
                    return;
                }
                alert(resultado.mensaje);
                formulario.reset();
                await cargarEquipos();
            } catch (error) {
                console.error("Error al registrar equipo:", error);
                alert("No fue posible completar la solicitud");
            } finally {
                boton.disabled = false;
            }
        });
        // Guardar modificaciones de un equipo
        if (formEditar && seccionEditar) {
            formEditar.addEventListener("submit", async function (evento) {
                evento.preventDefault();
                if (!formEditar.reportValidity()) {
                    return;
                }
                const datos = {
                    id_equipo: Number(
                        document.getElementById("editarId").value
                    ),
                    descripcion: document.getElementById(
                        "editarDescripcion"
                    ).value.trim(),
                    marca: document.getElementById(
                        "editarMarca"
                    ).value.trim(),
                    modelo: document.getElementById(
                        "editarModelo"
                    ).value.trim(),
                    condicion: document.getElementById(
                        "editarCondicion"
                    ).value.trim()
                };
                const boton = formEditar.querySelector('button[type="submit"]');
                boton.disabled = true;
                try {
                    const respuesta = await fetch("../actualizar_equipo.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify(datos)
                    });
                    const resultado = await respuesta.json();
                    if (!respuesta.ok) {
                        alert(
                            resultado.error ||
                            "No se pudo actualizar el equipo"
                        );
                        return;
                    }
                    alert(resultado.mensaje);
                    seccionEditar.hidden = true;
                    formEditar.reset();
                    await cargarEquipos();
                } catch (error) {
                    console.error("Error al actualizar:", error);
                    alert("No fue posible actualizar el equipo");
                } finally {
                    boton.disabled = false;
                }
            });
            // Cancelar edición
            if (cancelarEdicion) {
                cancelarEdicion.addEventListener("click", function () {
                    formEditar.reset();
                    seccionEditar.hidden = true;
                });
            }
        }
    });
    ////////////////////////////////////////////////////////
    // MÓDULO DE PRÉSTAMOS
    ////////////////////////////////////////////////////////
    document.addEventListener("DOMContentLoaded", function () {
        const formPrestamos = document.getElementById("formPrestamos");
        // Ejecutar solamente en la página de préstamos
        if (!formPrestamos) {
            return;
        }
        const selectEquipo = document.getElementById("equipo");
        const selectSolicitante = document.getElementById("solicitante");
        const selectUsuario = document.getElementById("usuario");
        // Consultar y mostrar el historial de préstamos
        async function cargarPrestamos() {
            try {
                const respuesta = await fetch("../listar_prestamos.php");
                if (!respuesta.ok) {
                    throw new Error("No se pudieron consultar los préstamos");
                }
                const resultado = await respuesta.json();
                if (!Array.isArray(resultado.prestamos)) {
                    throw new Error("Respuesta inválida del servidor");
                }
                const tablaPrestamos =
                    document.getElementById("tablaPrestamos");
                tablaPrestamos.replaceChildren();
                resultado.prestamos.forEach(function (prestamo) {
                    const fila = tablaPrestamos.insertRow();
                    [
                        prestamo.id_prestamo,
                        prestamo.num_inventario + " - " + prestamo.equipo,
                        prestamo.solicitante,
                        prestamo.fecha_prestamo,
                        prestamo.fecha_prevista,
                        prestamo.estado
                    ].forEach(function (valor) {
                        const celda = fila.insertCell();
                        celda.textContent = valor;
                    });
                });
            } catch (error) {
                console.error("Error al consultar préstamos:", error);
                alert("No se pudo cargar el historial de préstamos");
            }
        }
        // Consultar equipos, solicitantes y usuarios de MariaDB
        async function cargarDatosPrestamo() {
            try {
                const respuesta = await fetch("../datos_prestamo.php");
                if (!respuesta.ok) {
                    throw new Error("No se pudieron cargar los datos");
                }
                const datos = await respuesta.json();
                if (!Array.isArray(datos.equipos) ||
                    !Array.isArray(datos.solicitantes) ||
                    !Array.isArray(datos.usuarios)) {
                    throw new Error("Respuesta del servidor inválida");
                }
                // Limpiar opciones anteriores
                selectEquipo.innerHTML =
                    '<option value="">Seleccione un equipo</option>';
                selectSolicitante.innerHTML =
                    '<option value="">Seleccione un solicitante</option>';
                selectUsuario.innerHTML =
                    '<option value="">Seleccione un usuario</option>';
                // Cargar equipos disponibles
                datos.equipos.forEach(function (equipo) {
                    const opcion = document.createElement("option");
                    opcion.value = equipo.id_equipo;
                    opcion.textContent =
                        equipo.num_inventario + " - " +
                        equipo.descripcion;
                    selectEquipo.appendChild(opcion);
                });
                // Cargar solicitantes
                datos.solicitantes.forEach(function (solicitante) {
                    const opcion = document.createElement("option");
                    opcion.value = solicitante.id_solicitante;
                    opcion.textContent = solicitante.nombre;
                    selectSolicitante.appendChild(opcion);
                });
                // Cargar usuarios responsables
                datos.usuarios.forEach(function (usuario) {
                    const opcion = document.createElement("option");
                    opcion.value = usuario.id_usuario;
                    opcion.textContent = usuario.nombre;
                    selectUsuario.appendChild(opcion);
                });
            } catch (error) {
                console.error("Error al cargar datos del préstamo:", error);
                alert("No se pudieron cargar los datos del préstamo");
            }
        }
        // Cargar datos al abrir prestamos.html
        cargarDatosPrestamo();
        cargarPrestamos();
        // Registrar un préstamo en MariaDB
        formPrestamos.addEventListener("submit", async function (evento) {
            evento.preventDefault();
            if (!formPrestamos.reportValidity()) {
                return;
            }
            const datos = {
                id_equipo: Number(selectEquipo.value),
                id_solicitante: Number(selectSolicitante.value),
                id_usuario: Number(selectUsuario.value),
                fecha_prestamo: document.getElementById("fechaPrestamo").value,
                fecha_prevista: document.getElementById("fechaPrevista").value
            };
            if (new Date(datos.fecha_prevista) < new Date(datos.fecha_prestamo)) {
                alert("La devolución prevista no puede ser anterior al préstamo");
                return;
            }
            const boton = formPrestamos.querySelector('button[type="submit"]');
            boton.disabled = true;
            try {
                const respuesta = await fetch("../guardar_prestamo.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify(datos)
                });
                const resultado = await respuesta.json();
                if (!respuesta.ok) {
                    alert(resultado.error || "No se pudo registrar el préstamo");
                    return;
                }
                alert(resultado.mensaje);
                formPrestamos.reset();
                // Actualizar los equipos disponibles y el historial
                await cargarDatosPrestamo();
                await cargarPrestamos();
            } catch (error) {
                console.error("Error al registrar préstamo:", error);
                alert("No fue posible comunicarse con el servidor");
            } finally {
                boton.disabled = false;
            }
        });
    });
    ////////////////////////////////////////////////////////
    // MÓDULO DE DEVOLUCIONES
    ////////////////////////////////////////////////////////
    document.addEventListener("DOMContentLoaded", function () {
        const formDevoluciones = document.getElementById("formDevoluciones");
        // Ejecutar solamente en devoluciones.html
        if (!formDevoluciones) {
            return;
        }
        const selectPrestamo = document.getElementById("prestamo");
        const tablaDevoluciones = document.getElementById("tablaDevoluciones");
        // Consultar préstamos que siguen activos
        async function cargarPrestamosActivos() {
            try {
                const respuesta = await fetch("../prestamos_activos.php");
                if (!respuesta.ok) {
                    throw new Error("No se pudieron consultar los préstamos activos");
                }
                const resultado = await respuesta.json();
                if (!Array.isArray(resultado.prestamos)) {
                    throw new Error("Respuesta inválida del servidor");
                }
                selectPrestamo.replaceChildren();
                const opcionInicial = document.createElement("option");
                opcionInicial.value = "";
                opcionInicial.textContent = "Seleccione un préstamo";
                selectPrestamo.appendChild(opcionInicial);
                resultado.prestamos.forEach(function (prestamo) {
                    const opcion = document.createElement("option");
                    opcion.value = prestamo.id_prestamo;
                    opcion.textContent =
                        "Préstamo " + prestamo.id_prestamo +
                        " - " + prestamo.num_inventario +
                        " - " + prestamo.equipo;
                    selectPrestamo.appendChild(opcion);
                });
            } catch (error) {
                console.error("Error al cargar préstamos activos:", error);
                alert("No se pudieron cargar los préstamos activos");
            }
        }
        // Consultar devoluciones registradas
        async function cargarDevoluciones() {
            try {
                const respuesta = await fetch("../listar_devoluciones.php");
                if (!respuesta.ok) {
                    throw new Error("No se pudieron consultar las devoluciones");
                }
                const resultado = await respuesta.json();
                if (!Array.isArray(resultado.devoluciones)) {
                    throw new Error("Respuesta inválida del servidor");
                }
                tablaDevoluciones.replaceChildren();
                resultado.devoluciones.forEach(function (devolucion) {
                    const fila = tablaDevoluciones.insertRow();
                    [
                        devolucion.id_devolucion,
                        devolucion.id_prestamo,
                        devolucion.equipo,
                        devolucion.fecha_devolucion,
                        devolucion.condicion_retorno,
                        devolucion.observaciones ?? ""
                    ].forEach(function (valor) {
                        const celda = fila.insertCell();
                        celda.textContent = valor;
                    });
                });
            } catch (error) {
                console.error("Error al cargar devoluciones:", error);
                alert("No se pudo cargar el historial de devoluciones");
            }
        }
        // Cargar información al abrir la página
        cargarPrestamosActivos();
        cargarDevoluciones();
        // Registrar una devolución
        formDevoluciones.addEventListener("submit", async function (evento) {
            evento.preventDefault();
            if (!formDevoluciones.reportValidity()) {
                return;
            }
            const datos = {
                id_prestamo: Number(selectPrestamo.value),
                fecha_devolucion:
                    document.getElementById("fechaDevolucion").value,
                condicion_retorno:
                    document.getElementById("condicionRetorno").value.trim(),
                observaciones:
                    document.getElementById("observaciones").value.trim()
            };
            const boton = formDevoluciones.querySelector('button[type="submit"]');
            boton.disabled = true;
            try {
                const respuesta = await fetch("../registrar_devolucion.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify(datos)
                });
                const resultado = await respuesta.json();
                if (!respuesta.ok) {
                    alert(resultado.error || "No se pudo registrar la devolución");
                    return;
                }
                alert(resultado.mensaje);
                formDevoluciones.reset();
                // Actualizar préstamos activos y devoluciones
                await cargarPrestamosActivos();
                await cargarDevoluciones();
            } catch (error) {
                console.error("Error al registrar devolución:", error);
                alert("No fue posible comunicarse con el servidor");
            } finally {
                boton.disabled = false;
            }
        });
    });
    ////////////////////////////////////////////////////////
    // MÓDULO DE SOLICITANTES
    ////////////////////////////////////////////////////////
    document.addEventListener("DOMContentLoaded", function () {
        const formSolicitantes = document.getElementById("formSolicitantes");
        // Ejecutar solamente en solicitantes.html
        if (!formSolicitantes) {
            return;
        }
        const selectDepartamento = document.getElementById("departamento");
        const tablaSolicitantes = document.getElementById("tablaSolicitantes");
        // Consultar departamentos registrados en MariaDB
        async function cargarDepartamentos() {
            try {
                const respuesta = await fetch("../listar_departamentos.php");
                if (!respuesta.ok) {
                    throw new Error("No se pudieron consultar los departamentos");
                }
                const resultado = await respuesta.json();
                if (!Array.isArray(resultado.departamentos)) {
                    throw new Error("Respuesta inválida del servidor");
                }
                selectDepartamento.replaceChildren();
                const opcionInicial = document.createElement("option");
                opcionInicial.value = "";
                opcionInicial.textContent = "Seleccione un departamento";
                selectDepartamento.appendChild(opcionInicial);
                resultado.departamentos.forEach(function (departamento) {
                    const opcion = document.createElement("option");
                    opcion.value = departamento.id_departamento;
                    opcion.textContent = departamento.nombre;
                    selectDepartamento.appendChild(opcion);
                });
            } catch (error) {
                console.error("Error al cargar departamentos:", error);
                alert("No se pudieron cargar los departamentos");
            }
        }
        // Consultar solicitantes registrados
        async function cargarSolicitantes() {
            try {
                const respuesta = await fetch("../listar_solicitantes.php");
                if (!respuesta.ok) {
                    throw new Error("No se pudieron consultar los solicitantes");
                }
                const resultado = await respuesta.json();
                if (!Array.isArray(resultado.solicitantes)) {
                    throw new Error("Respuesta inválida del servidor");
                }
                tablaSolicitantes.replaceChildren();
                resultado.solicitantes.forEach(function (solicitante) {
                    const fila = tablaSolicitantes.insertRow();
                    [
                        solicitante.id_solicitante,
                        solicitante.nombre,
                        solicitante.num_institucional,
                        solicitante.departamento
                    ].forEach(function (valor) {
                        const celda = fila.insertCell();
                        celda.textContent = valor;
                    });
                });
            } catch (error) {
                console.error("Error al cargar solicitantes:", error);
                alert("No se pudo cargar la lista de solicitantes");
            }
        }
        // Cargar información al abrir la página
        cargarDepartamentos();
        cargarSolicitantes();
        // Registrar un solicitante
        formSolicitantes.addEventListener("submit", async function (evento) {
            evento.preventDefault();
            if (!formSolicitantes.reportValidity()) {
                return;
            }
            const datos = {
                nombre: document.getElementById("nombre").value.trim(),
                numero: document.getElementById("numero").value.trim(),
                id_departamento: Number(selectDepartamento.value)
            };
            const boton = formSolicitantes.querySelector('button[type="submit"]');
            boton.disabled = true;
            try {
                const respuesta = await fetch("../guardar_solicitante.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify(datos)
                });
                const resultado = await respuesta.json();
                if (!respuesta.ok) {
                    alert(resultado.error || "No se pudo registrar el solicitante");
                    return;
                }
                alert(resultado.mensaje);
                formSolicitantes.reset();
                // Actualizar la tabla
                await cargarSolicitantes();
            } catch (error) {
                console.error("Error al registrar solicitante:", error);
                alert("No fue posible comunicarse con el servidor");
            } finally {
                boton.disabled = false;
            }
        });
    });