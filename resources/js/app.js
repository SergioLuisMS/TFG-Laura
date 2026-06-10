import "./bootstrap";
import Swal from "sweetalert2";
import "./avatar-preview";
import "./biblioteca";
import { initSalaInteractiva } from "./sala-interactiva";

// Herramientas globales
window.Swal = Swal;

document.addEventListener("DOMContentLoaded", () => {
    // 1. ALERTAS DE VALIDACIÓN
    const alertData = document.getElementById("validation-alert");
    if (alertData) {
        const message = alertData.getAttribute("data-message");
        Swal.fire({
            icon: "error",
            title: "¡Ups! Algo va mal",
            text: message,
            confirmButtonColor: "#7c2d12",
            confirmButtonText: "Entendido",
        });
    }

    // 2. AUTO-DETECCIÓN DE SALA
    const salaRoot = document.getElementById("sala-interactiva-root");

    if (salaRoot) {
        const tipo = salaRoot.getAttribute("data-tipo");
        const user = salaRoot.getAttribute("data-user");

        // Retrasamos 200ms la ejecución para asegurar que el DOM esté "caliente"
        setTimeout(() => {
            initSalaInteractiva(tipo, user);

            if (typeof imageMapResize === "function") {
                imageMapResize();
            }
        }, 200);
    }
});

// --- FUNCIONALIDADES GLOBALES ---

window.toggleFormNombre = function () {
    const container = document.getElementById("form-nombre-container");
    if (!container) return;
    container.style.display =
        container.style.display === "none" || container.style.display === ""
            ? "block"
            : "none";
    if (container.style.display === "block")
        container.querySelector("input")?.focus();
};

window.alertaInvitado = function () {
    Swal.fire({
        title: "¡Hola, patata! 🥔",
        text: "Para añadir libros a tu biblioteca personal necesitas una cuenta.",
        icon: "info",
        confirmButtonColor: "#f97316",
        confirmButtonText: "¡Entendido!",
    });
};
