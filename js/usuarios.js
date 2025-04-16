document.addEventListener("DOMContentLoaded", function () {
    cargarUsuarios();
  
    document.getElementById("formUsuario").addEventListener("submit", function (e) {
      e.preventDefault();
      
      const formData = new FormData();
      formData.append("codigo", document.getElementById("codigo").value);
      formData.append("nombre", document.getElementById("nombre").value);
      formData.append("contrasena", document.getElementById("contrasena").value);
      formData.append("tipo_usuario", document.getElementById("tipo_usuario").value);
  
      fetch("../php/usuarios.php", {
        method: "POST",
        body: formData,
      })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            alert("Usuario registrado");
            cargarUsuarios();
          } else {
            alert("Error: " + data.error);
          }
        });
    });
  });
  
  function cargarUsuarios() {
    fetch("../php/usuarios.php")
      .then(res => res.json())
      .then(data => {
        const tabla = document.getElementById("tablaUsuarios");
        tabla.innerHTML = "";
        data.forEach(u => {
          const row = `
            <tr>
              <td>${u.codigo}</td>
              <td>${u.nombre}</td>
              <td>${u.tipo_usuario}</td>
            </tr>`;
          tabla.innerHTML += row;
        });
      });
  }
  
