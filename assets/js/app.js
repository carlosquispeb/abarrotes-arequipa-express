// Funciones de apoyo en cliente - Abarrotes Arequipa Express
document.addEventListener('DOMContentLoaded', () => {
    const alertas = document.querySelectorAll('.alert-dismissible');
    alertas.forEach(alerta => {
        setTimeout(() => {
            alerta.classList.remove('show');
        }, 4000);
    });
});