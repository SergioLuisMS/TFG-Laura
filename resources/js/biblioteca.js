// resources/js/biblioteca.js

// =====================================================
// 1. ANADIR LIBROS (AJAX)
// =====================================================

/**
 * Guardo un libro en la estanteria del usuario sin recargar la pagina.
 *
 * Cuando la peticion falla (error de red o respuesta de error del servidor)
 * muestro un mensaje de error claro al usuario en lugar de fallar silenciosamente
 * o mostrar solo console.error (Mejora #24).
 */
window.añadirLibroSinRecargar = function (btn) {
    const metaToken = document.querySelector('meta[name="csrf-token"]');
    const metaRoute = document.querySelector('meta[name="route-guardar-libro"]');

    if (!metaToken || !metaRoute) {
        console.warn('Faltan metadatos de configuracion en el layout.');
        return;
    }

    const token    = metaToken.getAttribute('content');
    const urlGuardar = metaRoute.getAttribute('content');

    btn.disabled = true;
    const textoOriginal = btn.innerText;
    btn.innerText = 'Guardando...';

    const datos = {
        titulo:  btn.getAttribute('data-title'),
        autor:   btn.getAttribute('data-author'),
        genero:  btn.getAttribute('data-genre'),
        portada: btn.getAttribute('data-cover'),
        _token:  token,
    };

    fetch(urlGuardar, {
        method: 'POST',
        headers: {
            'Content-Type':   'application/json',
            'Accept':         'application/json',
            'X-CSRF-TOKEN':   token,
        },
        body: JSON.stringify(datos),
    })
        .then((response) => {
            if (!response.ok) {
                // Capturo errores HTTP (422, 500, etc.) para mostrarlos al usuario
                return response.json().then((err) => {
                    throw new Error(err.message || `Error del servidor: ${response.status}`);
                }).catch(() => {
                    throw new Error(`Error del servidor: ${response.status}`);
                });
            }
            return response.json();
        })
        .then((data) => {
            if (data.success) {
                // Muestro el mensaje de exito en la alerta AJAX si existe
                const alertaDiv     = document.getElementById('alerta-ajax');
                const alertaMensaje = document.getElementById('alerta-mensaje');
                if (alertaMensaje) alertaMensaje.innerText = data.message;
                if (alertaDiv)     alertaDiv.style.display = 'block';

                btn.innerText           = 'Anadido';
                btn.style.backgroundColor = '#4CAF50';
                btn.style.color         = 'white';

                setTimeout(() => {
                    btn.disabled          = false;
                    btn.innerText         = '+ Anadir';
                    btn.style.backgroundColor = '';
                    btn.style.color       = '';
                }, 2000);

                setTimeout(() => {
                    if (alertaDiv) alertaDiv.style.display = 'none';
                }, 4000);
            } else {
                mostrarErrorLibro(btn, textoOriginal, data.message || 'No se pudo guardar el libro.');
            }
        })
        .catch((error) => {
            // Muestro el mensaje de error real al usuario en lugar de un alert generico
            mostrarErrorLibro(btn, textoOriginal, error.message);
        });
};

/**
 * Muestro el error en el contenedor de alerta si existe, o como alerta del navegador
 * si el contenedor no esta disponible en la pagina actual.
 */
function mostrarErrorLibro(btn, textoOriginal, mensaje) {
    const alertaDiv     = document.getElementById('alerta-ajax');
    const alertaMensaje = document.getElementById('alerta-mensaje');

    if (alertaMensaje && alertaDiv) {
        alertaMensaje.innerText    = 'Error: ' + mensaje;
        alertaDiv.style.display    = 'block';
        alertaDiv.style.background = '#fee2e2';

        setTimeout(() => {
            alertaDiv.style.display    = 'none';
            alertaDiv.style.background = '';
        }, 5000);
    } else {
        alert('Error al guardar: ' + mensaje);
    }

    btn.disabled  = false;
    btn.innerText = textoOriginal;
}

// =====================================================
// 2. FILTRO POR GENERO (AJAX)
// =====================================================

document.addEventListener('click', function (e) {
    if (!e.target || !e.target.classList.contains('btn-filtro')) return;

    const boton     = e.target;
    const genero    = boton.getAttribute('data-genero');
    const contenedor = document.getElementById('contenedor-libros-ajax');

    // Actualizo el estado visual del boton activo
    document.querySelectorAll('.btn-filtro').forEach((b) => b.classList.remove('active'));
    boton.classList.add('active');

    if (contenedor) contenedor.style.opacity = '0.5';

    fetch(`/estanteria/filtrar?genero=${encodeURIComponent(genero)}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
    })
        .then((response) => {
            if (!response.ok) {
                throw new Error(`Error al filtrar libros: ${response.status}`);
            }
            return response.json();
        })
        .then((data) => {
            if (contenedor) {
                contenedor.innerHTML    = data.html;
                contenedor.style.opacity = '1';
            }
        })
        .catch((error) => {
            // Restauro la opacidad y muestro el error en consola
            if (contenedor) contenedor.style.opacity = '1';
            console.error('Error al filtrar la estanteria:', error.message);
        });
});
