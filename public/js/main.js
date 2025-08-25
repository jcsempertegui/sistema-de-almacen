// public/js/main.js

document.addEventListener("DOMContentLoaded", () => {
    console.log("✅ Sistema de Almacén inicializado");

    // 🗑️ Confirmación automática para botones de eliminar
    document.querySelectorAll(".btn-eliminar").forEach(btn => {
        btn.addEventListener("click", (e) => {
            if (!confirm("¿Seguro que deseas eliminar este registro?")) {
                e.preventDefault(); // cancela el link si el usuario no confirma
            }
        });
    });

    // 🔔 Alertas temporales (se ocultan en 3 segundos)
    const alerts = document.querySelectorAll(".alert-auto-close");
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.classList.add("fade");
            alert.style.opacity = "0";
            setTimeout(() => alert.remove(), 500);
        }, 3000);
    });
});
