let productosSeleccionados = [];

function cargarProductos() {
    fetch('../php/productos.php')
        .then(res => res.json())
        .then(data => {
            const contenedor = document.getElementById('productos-lista');
            contenedor.innerHTML = '';
            data.forEach(p => {
                contenedor.innerHTML += `
                    <div>
                        <label>${p.nombre} ($${p.valor_unitario})</label>
                        <input type="number" id="prod-${p.id_producto}" placeholder="Cantidad" min="0">
                    </div>`;
            });
        });
}

function enviarPedido() {
    fetch('../php/productos.php')
        .then(res => res.json())
        .then(data => {
            productosSeleccionados = [];
            data.forEach(p => {
                const cantidad = parseInt(document.getElementById(`prod-${p.id_producto}`).value);
                if (cantidad > 0) {
                    productosSeleccionados.push({ id_producto: p.id_producto, cantidad });
                }
            });

            if (productosSeleccionados.length === 0) {
                alert('Selecciona al menos un producto');
                return;
            }

            fetch('../php/pedidos.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ productos: productosSeleccionados })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('Pedido guardado');
                    cargarPedidos();
                }
            });
        });
}

function cargarPedidos() {
    fetch('../php/pedidos.php')
        .then(res => res.json())
        .then(data => {
            const tbody = document.querySelector('#tabla-pedidos tbody');
            tbody.innerHTML = '';
            data.forEach(p => {
                tbody.innerHTML += `
                    <tr>
                        <td>${p.id_pedido}</td>
                        <td>${p.fecha}</td>
                        <td>${p.nombre}</td>
                        <td>${p.cantidad}</td>
                    </tr>`;
            });
        });
}

cargarProductos();
cargarPedidos();