document.addEventListener("DOMContentLoaded", function() {
    // 1. Inicializar iconos
    if (window.lucide) {
        lucide.createIcons();
    }

    // 2. Obtener los datos desde el HTML (Data Attributes)
    const container = document.getElementById('paypal-button-container');
    
    // Si el contenedor no existe, no ejecutamos nada
    if (!container) return;

    const total = container.dataset.total;
    const idReserva = container.dataset.idReserva;

    // 3. Configurar Botones de PayPal
    paypal.Buttons({
        style: {
            layout: 'vertical',
            color:  'gold',
            shape:  'rect',
            label:  'pay'
        },

        createOrder: function(data, actions) {
            return actions.order.create({
                purchase_units: [{
                    description: "Reserva de parking Zpot - ID #" + idReserva,
                    amount: {
                        currency_code: "EUR",
                        value: total
                    }
                }]
            });
        },

        onApprove: function(data, actions) {
            return actions.order.capture().then(function(orderData) {
                // Redirigir a confirmación tras el éxito
                window.location.href = "confirmacion.php?id_reserva=" + idReserva + "&order_id=" + orderData.id;
            });
        },

        onError: function (err) {
            console.error('PayPal Error:', err);
            alert("Hubo un error al procesar el pago.");
        },

        onCancel: function (data) {
            alert("Pago cancelado.");
        }
    }).render('#paypal-button-container');
});