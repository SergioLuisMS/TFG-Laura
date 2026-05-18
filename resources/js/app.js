import './bootstrap';
import Swal from 'sweetalert2';
import './avatar-preview';
import './biblioteca';
import { initSalaInteractiva } from './sala-interactiva';

// Herramientas globales
window.Swal = Swal;

document.addEventListener('DOMContentLoaded', () => {

    // 1. ALERTAS DE VALIDACIÓN
    const alertData = document.getElementById('validation-alert');
    if (alertData) {
        const message = alertData.getAttribute('data-message');
        Swal.fire({
            icon: 'error',
            title: '¡Ups! Algo va mal',
            text: message,
            confirmButtonColor: '#7c2d12',
            confirmButtonText: 'Entendido'
        });
    }

    // 2. AUTO-DETECCIÓN DE SALA (El "Motor" principal)
    const salaRoot = document.getElementById('sala-interactiva-root');
    if (salaRoot) {
        // En app.js, dentro del if(salaRoot)
        const tipo = salaRoot.getAttribute('data-tipo'); // Esto debería ser solo 'despacho-rosa'
        const user = salaRoot.getAttribute('data-user');

        // Redimensionador de mapas (Librería externa)
        if (typeof imageMapResize === 'function') {
            imageMapResize();
        }

        // Arrancamos TODO (Timer, Botes, Chat y PULSO)
        // Como ya incluimos el pulso dentro de esta función, no hace falta llamarlo aparte
        initSalaInteractiva(tipo, user);

        console.log("🚀 Sistema de sala iniciado para:", tipo);
    }
});

// --- FUNCIONALIDADES GLOBALES ---

window.toggleFormNombre = function () {
    const container = document.getElementById('form-nombre-container');
    if (!container) return;
    container.style.display = (container.style.display === 'none' || container.style.display === '') ? 'block' : 'none';
    if (container.style.display === 'block') container.querySelector('input')?.focus();
};

window.alertaInvitado = function () {
    Swal.fire({
        title: '¡Hola, patata! 🥔',
        text: 'Para añadir libros a tu biblioteca personal necesitas una cuenta.',
        icon: 'info',
        confirmButtonColor: '#f97316',
        confirmButtonText: '¡Entendido!'
    });
};