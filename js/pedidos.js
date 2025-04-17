document.addEventListener("DOMContentLoaded", () => {
    cargarUsuarios();
    cargarProductos();

    document.getElementById("agregarProducto").addEventListener("click", agregarFilaProducto);
    document.getElementById("guardarPedido").addEventListener("click", guardarPedido);
});

let productos = [];

function cargarUsuarios() {
    fetch("../php/usuarios.php")
        .then(res => res.json())
        .then(data => {
            const select = document.getElementById("usuario");
            data.forEach(u => {
                const option = document.createElement("option");
                option.value = u.id_usuario;
                option.textContent = u.nombre;
                select.appendChild(option);
            });
        });
}

function cargarProductos() {
    fetch("../php/productos.php")
        .then(res => res.json())
        .then(data => productos = data);
}

function agregarFilaProducto() {
    const fila = document.createElement("tr");

    const celdaProducto = document.createElement("td");
    const select = document.createElement("select");
    productos.forEach(p => {
        const opt = document.createElement("option");
        opt.value = p.id_producto;
        opt.textContent = `${p.nombre} ($${p.valor_unitario})`;
        opt.dataset.precio = p.valor_unitario;
        select.appendChild(opt);
    });
    celdaProducto.appendChild(select);

    const celdaValor = document.createElement("td");
    celdaValor.textContent = productos[0].valor_unitario;

    const celdaCantidad = document.createElement("td");
    const inputCantidad = document.createElement("input");
    inputCantidad.type = "number";
    inputCantidad.min = 1;
    inputCantidad.value = 1;
    celdaCantidad.appendChild(inputCantidad);

    const celdaSubtotal = document.createElement("td");
    celdaSubtotal.textContent = productos[0].valor_unitario;

    const celdaAccion = document.createElement("td");
    const btnEliminar = document.createElement("button");
    btnEliminar.textContent = "Eliminar";
    btnEliminar.onclick = () => fila.remove();
    celdaAccion.appendChild(btnEliminar);

    fila.append(celdaProducto, celdaValor, celdaCantidad, celdaSubtotal, celdaAccion);
    document.getElementById("detallePedido").appendChild(fila);

    // eventos dinámicos
    select.addEventListener("change", () => {
        const selected = select.selectedOptions[0];
        celdaValor.textContent = selected.dataset.precio;
        calcularSubtotal();
    });

    inputCantidad.addEventListener("input", calcularSubtotal);

    function calcularSubtotal() {
        const precio = parseFloat(select.selectedOptions[0].dataset.precio);
        const cantidad = parseInt(inputCantidad.value);
        celdaSubtotal.textContent = (precio * cantidad).toFixed(2);
    }
}



function guardarPedido() {
    const usuario = document.getElementById("usuario").value;
    const filas = document.querySelectorAll("#detallePedido tr");

    if (filas.length === 0) {
        alert("Agrega al menos un producto");
        return;
    }

    const detalle = [];
    filas.forEach(fila => {
        const select = fila.querySelector("select");
        const input = fila.querySelector("input");
        const precio = parseFloat(select.selectedOptions[0].dataset.precio);
        const cantidad = parseInt(input.value);
        const subtotal = precio * cantidad;

        detalle.push({
            id_producto: select.value,
            cantidad: cantidad,
            subtotal: subtotal
        });
    });

    const datos = new FormData();
    datos.append("id_usuario", usuario);
    datos.append("estado", "pendiente");
    datos.append("detalle", JSON.stringify(detalle));
    datos.append("accion", "insertar"); // ✅ ESTA LÍNEA ES LA CLAVE

    fetch("../php/pedidos.php", {
        method: "POST",
        body: datos
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert("Pedido guardado exitosamente");
            location.reload();
        } else {
            alert("Error: " + data.error);
        }
    });
}







function cargarPedidos(fecha = "") {
    const formData = new FormData();
    formData.append("accion", "listar");
    if (fecha) formData.append("fecha", fecha);

    fetch("../php/pedidos.php", {
        method: "POST",
        body: formData
    })
        .then(res => res.json())
        .then(data => {
            const tabla = document.querySelector("#tablaPedidos tbody");
            tabla.innerHTML = "";
            data.forEach(p => {
                const productos = p.detalles.map(d =>
                    `${d.nombre} (${d.cantidad}) = $${parseFloat(d.subtotal).toFixed(2)}`
                ).join("<br>");

                const row = `
            <tr>
              <td>${p.id_pedido}</td>
              <td>${p.fecha}</td>
              <td>${p.usuario}</td>
              <td>
                <select onchange="actualizarEstado(${p.id_pedido}, this.value)">
                <option value="pendiente" ${p.estado === "pendiente" ? "selected" : ""}>pendiente</option>
                <option value="proceso" ${p.estado === "proceso" ? "selected" : ""}>proceso</option>
                <option value="pagado" ${p.estado === "pagado" ? "selected" : ""}>pagado</option>
                </select>
              </td>
              <td>${productos}</td>
            </tr>`;
                tabla.innerHTML += row;
            });
        });
}

function filtrarPedidos() {
    const fecha = document.getElementById("filtroFecha").value;
    cargarPedidos(fecha);
}

// Llamada inicial
document.addEventListener("DOMContentLoaded", () => {
    cargarPedidos();
});



function actualizarEstado(id_pedido, nuevoEstado) {
    const formData = new FormData();
    formData.append("accion", "actualizar_estado");
    formData.append("id_pedido", id_pedido);
    formData.append("estado", nuevoEstado);
  
    fetch("../php/pedidos.php", {
      method: "POST",
      body: formData
    })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          alert(`Estado actualizado a ${nuevoEstado}`);
          if (data.venta_generada) {
            alert(`✅ Venta registrada con ID ${data.id_venta}`);
          }
        } else {
          alert("Error: " + data.error);
        }
      });
  }
  