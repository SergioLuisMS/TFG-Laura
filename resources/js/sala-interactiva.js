// resources/js/sala-interactiva.js

export function initSalaInteractiva(salaId, userPotato) {

    // =====================================================
    // TIMER
    // =====================================================

    let segundos = 0;
    let timerInterval = null;

    function iniciarTimer() {
        const el = document.getElementById('timer');
        if (!el) return;

        if (timerInterval) clearInterval(timerInterval);

        timerInterval = setInterval(() => {
            segundos++;

            const hrs  = Math.floor(segundos / 3600);
            const mins = Math.floor((segundos % 3600) / 60);
            const secs = segundos % 60;
            const fmt  = (n) => String(n).padStart(2, '0');

            el.textContent = `${fmt(hrs)}:${fmt(mins)}:${fmt(secs)}`;

            const barra = document.getElementById('focus-bar');
            if (barra) {
                const objetivo = 3600;
                barra.style.width = Math.min((segundos / objetivo) * 100, 100) + '%';
            }
        }, 1000);
    }

    iniciarTimer();

    // =====================================================
    // PULSO AUTOMATICO (respaldo cada 60s)
    // =====================================================

    // Envio el pulso con manejo de error visible en consola para facilitar el diagnostico
    function enviarPulso() {
        const root = document.getElementById('sala-interactiva-root');
        const csrf = document.querySelector('meta[name="csrf-token"]');
        if (!root || !csrf) return;

        fetch('/salas/registrar-pulso', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf.getAttribute('content'),
            },
            body: JSON.stringify({ sala: root.getAttribute('data-tipo') }),
        })
            .then(async (response) => {
                if (!response.ok) {
                    const text = await response.text();
                    // Registro el error en consola pero no interrumpo al usuario
                    console.warn('Pulso fallido:', response.status, text);
                }
            })
            .catch((err) => {
                // Error de red: el pulso falla silenciosamente
                // El handler de beforeunload guardara el tiempo al cerrar la pagina
                console.warn('Error de red en pulso automatico:', err.message);
            });
    }

    setInterval(enviarPulso, 60000);

    // =====================================================
    // GUARDADO AL CERRAR LA PAGINA (Bug #3)
    // Uso keepalive:true para que el navegador complete la peticion
    // incluso si el usuario cierra la pestana antes de que termine.
    // Esto actua como respaldo cuando el usuario no pulsa "Terminar".
    // =====================================================

    window.addEventListener('beforeunload', () => {
        const csrf  = document.querySelector('meta[name="csrf-token"]');
        const timer = document.getElementById('timer');
        const root  = document.getElementById('sala-interactiva-root');

        if (!csrf || !timer || !root) return;

        const partes = timer.textContent.split(':').map(Number);
        if (partes.length !== 3) return;

        const segundosTotales = partes[0] * 3600 + partes[1] * 60 + partes[2];
        if (segundosTotales === 0) return;

        // keepalive permite que la peticion termine aunque la pagina se este cerrando
        fetch('/salas/guardar', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf.getAttribute('content'),
            },
            body: JSON.stringify({
                sala:     root.getAttribute('data-tipo'),
                segundos: segundosTotales,
            }),
            keepalive: true,
        });
    });

    // =====================================================
    // CHAT (backend - polling cada 2.5s)
    // =====================================================

    let ultimoIdMensaje = 0;
    let pollInterval    = null;

    function renderizarMensaje(nombre, texto) {
        const box = document.getElementById('chat-box');
        if (!box) return;

        const div = document.createElement('div');
        div.className = 'mensaje';

        const nombreBold = document.createElement('b');
        nombreBold.textContent = (nombre || 'Patata') + ':';
        div.appendChild(nombreBold);
        div.appendChild(document.createTextNode(' ' + texto));

        box.appendChild(div);
        box.scrollTop = box.scrollHeight;
    }

    // Consulto al servidor los mensajes nuevos desde el ultimo id conocido
    function pollMensajes() {
        const csrf = document.querySelector('meta[name="csrf-token"]');
        if (!csrf) return;

        fetch(`/chat/obtener?sala=${encodeURIComponent(salaId)}&desde=${ultimoIdMensaje}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrf.getAttribute('content'),
            },
        })
            .then((r) => {
                if (!r.ok) {
                    // Error del servidor: muestro aviso en consola, no bloqueo el chat
                    console.warn('Error al obtener mensajes del chat:', r.status);
                    return null;
                }
                return r.json();
            })
            .then((data) => {
                if (!data || !data.mensajes || data.mensajes.length === 0) return;

                data.mensajes.forEach((m) => {
                    renderizarMensaje(m.nombre, m.mensaje);
                    if (m.id > ultimoIdMensaje) ultimoIdMensaje = m.id;
                });
            })
            .catch((err) => {
                // Error de red: el poll falla silenciosamente para no molestar al usuario
                console.warn('Error de red en el poll del chat:', err.message);
            });
    }

    // Envio un mensaje al backend y hago un poll inmediato para que aparezca al instante
    window.enviarMensaje = function () {
        const input = document.getElementById('chat-input');
        const csrf  = document.querySelector('meta[name="csrf-token"]');
        if (!input || !csrf) return;

        const texto = input.value.trim();
        if (texto === '') return;

        input.value = '';

        fetch('/chat/enviar', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf.getAttribute('content'),
            },
            body: JSON.stringify({ sala: salaId, mensaje: texto }),
        })
            .then((r) => {
                if (!r.ok) {
                    // Devuelvo el texto al input para que el usuario no lo pierda
                    input.value = texto;
                    console.warn('No se pudo enviar el mensaje:', r.status);
                    return null;
                }
                return r.json();
            })
            .then((data) => {
                if (data) pollMensajes();
            })
            .catch((err) => {
                input.value = texto;
                console.warn('Error de red al enviar mensaje:', err.message);
            });
    };

    function iniciarChat() {
        const box = document.getElementById('chat-box');
        if (!box) return;

        // Mensaje de bienvenida local (no se persiste en la BD)
        const bienvenida  = document.createElement('div');
        bienvenida.className = 'mensaje';
        const sistemaTag = document.createElement('b');
        sistemaTag.textContent = 'Sistema:';
        bienvenida.appendChild(sistemaTag);
        bienvenida.appendChild(
            document.createTextNode(' Hola ' + userPotato + ', bienvenida a ' + salaId + '.')
        );
        box.appendChild(bienvenida);

        // Primera carga de mensajes existentes en la sala
        pollMensajes();

        // Polling continuo cada 2.5 segundos
        if (pollInterval) clearInterval(pollInterval);
        pollInterval = setInterval(pollMensajes, 2500);
    }

    iniciarChat();

    // Atajo de teclado: Enter envia el mensaje
    const chatInput = document.getElementById('chat-input');
    if (chatInput) {
        chatInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') window.enviarMensaje();
        });
    }

    const btnEnviarChat = document.getElementById('btn-enviar-chat');
    if (btnEnviarChat) {
        btnEnviarChat.addEventListener('click', () => window.enviarMensaje());
    }

    // =====================================================
    // BOTICA: items interactivos (pociones y caldero)
    // =====================================================

    if (salaId === 'botica') {
        let boteActivo = null;
        let offX = 0;
        let offY = 0;

        const botes = document.querySelectorAll('.bote-interactivo');

        const calderoArea = { xMin: 18, xMax: 48, yMin: 35, yMax: 82 };

        botes.forEach((bote) => {
            bote.addEventListener('mousedown', (e) => {
                boteActivo = bote;
                const rect = bote.getBoundingClientRect();
                offX = e.clientX - rect.left;
                offY = e.clientY - rect.top;
                bote.style.zIndex = 1000;
            });
        });

        document.addEventListener('mousemove', (e) => {
            if (!boteActivo) return;
            const contenedor = document.querySelector('.capa-mapa').getBoundingClientRect();
            boteActivo.style.left = ((e.clientX - contenedor.left - offX) / contenedor.width) * 100 + '%';
            boteActivo.style.top  = ((e.clientY - contenedor.top - offY) / contenedor.height) * 100 + '%';
        });

        document.addEventListener('mouseup', (e) => {
            if (!boteActivo) return;

            const rImg = document.getElementById('fondo-img').getBoundingClientRect();
            const px   = ((e.clientX - rImg.left) / rImg.width) * 100;
            const py   = ((e.clientY - rImg.top) / rImg.height) * 100;

            if (px >= calderoArea.xMin && px <= calderoArea.xMax &&
                py >= calderoArea.yMin && py <= calderoArea.yMax) {
                boteActivo.style.display = 'none';
                document.querySelectorAll('.reaccion-caldero').forEach((r) => (r.style.display = 'none'));
                const reaccion = document.getElementById('reaccion-' + boteActivo.id);
                if (reaccion) reaccion.style.display = 'block';
            }

            boteActivo.style.zIndex = 100;
            boteActivo = null;
        });
    }
}

// =====================================================
// FUNCIONES GLOBALES
// =====================================================

window.toggleCajon = function () {
    const c = document.getElementById('cajon-overlay');
    if (!c) return;
    c.style.display = c.style.display === 'block' ? 'none' : 'block';
};

/**
 * Intercepto el clic en "Terminar sesion", envio el tiempo exacto al servidor
 * y despues navego a la ruta destino.
 *
 * Este es el guardado principal. El handler de beforeunload es el respaldo
 * para cuando el usuario cierra la pestana sin pulsar este boton.
 */
window.finalizarSesion = function (event) {
    event.preventDefault();

    const timer      = document.getElementById('timer');
    const root       = document.getElementById('sala-interactiva-root');
    const urlDestino = event.currentTarget.href;

    if (!timer || !root) {
        window.location.href = urlDestino;
        return;
    }

    const partes          = timer.innerText.split(':').map(Number);
    const segundosTotales = partes[0] * 3600 + partes[1] * 60 + partes[2];
    const csrf            = document.querySelector('meta[name="csrf-token"]');

    fetch('/salas/guardar', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf ? csrf.getAttribute('content') : '',
        },
        body: JSON.stringify({
            sala:     root.getAttribute('data-tipo'),
            segundos: segundosTotales,
        }),
    })
        .catch((err) => {
            // Si falla el guardado final, registro el error pero navego igualmente
            console.warn('Error al guardar la sesion:', err.message);
        })
        .finally(() => {
            window.location.href = urlDestino;
        });
};
