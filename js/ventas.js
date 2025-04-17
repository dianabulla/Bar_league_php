document.addEventListener("DOMContentLoaded", () => {
    cargarVentas(); // carga inicial
  });
  
  function cargarVentas(fecha = "") {
    const formData = new FormData();
    formData.append("accion", "listar");
    if (fecha) formData.append("fecha", fecha);
  
    fetch("../php/ventas.php", {
      method: "POST",
      body: formData
    })
      .then(res => res.json())
      .then(data => {
        const tabla = document.getElementById("tablaVentas");
        tabla.innerHTML = "";
  
        data.forEach(v => {
          const fila = `
            <tr>
              <td>${v.id_venta}</td>
              <td>${v.id_pedido}</td>
              <td>$${parseFloat(v.total_venta).toFixed(2)}</td>
              <td>${v.fecha_venta}</td>
            </tr>
          `;
          tabla.innerHTML += fila;
        });
      });
  }
  
  function filtrarVentas() {
    const fecha = document.getElementById("filtroFecha").value;
    cargarVentas(fecha);
  }
  