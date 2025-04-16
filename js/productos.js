let productoEditando = null;

document.addEventListener("DOMContentLoaded", function () {
  cargarProductos();

  document.getElementById("formProducto").addEventListener("submit", function (e) {
    e.preventDefault();

    const formData = new FormData();
    formData.append("codigo", document.getElementById("codigo").value);
    formData.append("nombre", document.getElementById("nombre").value);
    formData.append("descripcion", document.getElementById("descripcion").value);
    formData.append("valor_unitario", document.getElementById("valor_unitario").value);

    if (productoEditando) {
      formData.append("id_producto", productoEditando);
      formData.append("accion", "editar");
    } else {
      formData.append("accion", "insertar");
    }

    fetch("../php/productos.php", {
      method: "POST",
      body: formData,
    })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          alert(productoEditando ? "Producto actualizado" : "Producto registrado");
          productoEditando = null;
          document.getElementById("formProducto").reset();
          cargarProductos();
        } else {
          alert("Error: " + data.error);
        }
      });
  });
});

function cargarProductos() {
  fetch("../php/productos.php")
    .then(res => res.json())
    .then(data => {
      const tabla = document.getElementById("tabla-productos");
      tabla.innerHTML = "";
      data.forEach(p => {
        const row = `
          <tr>
            <td>${p.codigo}</td>
            <td>${p.nombre}</td>
            <td>${p.descripcion}</td>
            <td>$${parseFloat(p.valor_unitario).toFixed(2)}</td>
            <td>
              <button onclick="editarProducto(${p.id_producto}, '${p.codigo}', '${p.nombre}', '${p.descripcion}', ${p.valor_unitario})">✏️</button>
              <button onclick="eliminarProducto(${p.id_producto})">🗑️</button>
            </td>
          </tr>`;
        tabla.innerHTML += row;
      });
    });
}

function editarProducto(id, codigo, nombre, descripcion, valor) {
  productoEditando = id;
  document.getElementById("codigo").value = codigo;
  document.getElementById("codigo").disabled = true;
  document.getElementById("nombre").value = nombre;
  document.getElementById("descripcion").value = descripcion;
  document.getElementById("valor_unitario").value = valor;
}

function eliminarProducto(id) {
  if (confirm("¿Eliminar este producto?")) {
    const formData = new FormData();
    formData.append("id_producto", id);
    formData.append("accion", "eliminar");

    fetch("../php/productos.php", {
      method: "POST",
      body: formData,
    })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          alert("Producto eliminado");
          cargarProductos();
        } else {
          alert("Error: " + data.error);
        }
      });
    }
}