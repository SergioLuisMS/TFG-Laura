// resources/js/sala-interactiva.js

export function initSalaInteractiva(salaId, userPotato) {
    let segundos = 0;
    let boteActivo = null;
    let offX, offY;

    // --- 🕒 TIMER ---
    const timerInterval = setInterval(() => {
        segundos++;
        const el = document.getElementById("timer");
        if (el) {
            let hrs = Math.floor(segundos / 3600);
            let mins = Math.floor((segundos % 3600) / 60);
            let secs = segundos % 60;
            const fmt = (n) => n < 10 ? "0" + n : n;
            el.innerText = `${fmt(hrs)}:${fmt(mins)}:${fmt(secs)}`;
        } else {
            clearInterval(timerInterval);
        }
    }, 1000);

    // --- 💓 PULSO (Envío automático cada 60s) ---
    const pulsoInterval = setInterval(() => {
        const csrf = document.querySelector('meta[name="csrf-token"]');
        if (!csrf) return;

        fetch('/salas/registrar-pulso', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf.getAttribute('content')
            },
            body: JSON.stringify({ tipo: salaId })
        })
            .then(() => console.log(`💓 Pulso registrado en ${salaId}`))
            .catch(err => console.error("Error en el pulso:", err));
    }, 60000);

    // --- 🥔 CHAT ---
    window.enviarMensaje = function () {
        const input = document.getElementById('chat-input');
        const box = document.getElementById('chat-box');
        if (!input || !box) return;
        const texto = input.value.trim();

        if (texto !== "") {
            const msjObj = { nombre: userPotato, texto: texto };
            const div = document.createElement('div');
            div.className = 'mensaje';
            div.innerHTML = `<b>${msjObj.nombre}:</b> ${msjObj.texto}`;
            box.appendChild(div);

            const hist = JSON.parse(localStorage.getItem('chat_' + salaId) || "[]");
            hist.push(msjObj);
            localStorage.setItem('chat_' + salaId, JSON.stringify(hist));

            input.value = "";
            box.scrollTop = box.scrollHeight;
        }
    };

    function cargarMensajes() {
        const box = document.getElementById('chat-box');
        if (!box) return;
        const hist = JSON.parse(localStorage.getItem('chat_' + salaId) || "[]");
        box.innerHTML = `<div class="mensaje"><b>Sistema:</b> Hola ${userPotato}, bienvenida a ${salaId}.</div>`;

        hist.forEach(m => {
            const div = document.createElement('div');
            div.className = 'mensaje';
            div.innerHTML = `<b>${m.nombre || 'Patata'}:</b> ${m.texto}`;
            box.appendChild(div);
        });
        box.scrollTop = box.scrollHeight;
    }

    cargarMensajes();

    const chatInput = document.getElementById('chat-input');
    if (chatInput) {
        chatInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') window.enviarMensaje();
        });
    }

    // --- 🧪 LÓGICA EXCLUSIVA DE BOTICA ---
    if (salaId === 'botica') {
        const botes = document.querySelectorAll('.bote-interactivo');
        const calderoArea = { xMin: 18, xMax: 48, yMin: 35, yMax: 82 };

        botes.forEach(bote => {
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
            boteActivo.style.left = ((e.clientX - contenedor.left - offX) / contenedor.width * 100) + '%';
            boteActivo.style.top = ((e.clientY - contenedor.top - offY) / contenedor.height * 100) + '%';
        });

        document.addEventListener('mouseup', (e) => {
            if (!boteActivo) return;
            const rImg = document.getElementById('fondo-img').getBoundingClientRect();
            const px = ((e.clientX - rImg.left) / rImg.width) * 100;
            const py = ((e.clientY - rImg.top) / rImg.height) * 100;

            if (px >= calderoArea.xMin && px <= calderoArea.xMax && py >= calderoArea.yMin && py <= calderoArea.yMax) {
                boteActivo.style.display = 'none';
                document.querySelectorAll('.reaccion-caldero').forEach(r => r.style.display = 'none');
                const r = document.getElementById('reaccion-' + boteActivo.id);
                if (r) r.style.display = 'block';
            }
            boteActivo.style.zIndex = 100;
            boteActivo = null;
        });
    }
}

// --- 🌐 FUNCIONES GLOBALES (Fuera del init) ---

window.toggleCajon = function () {
    const c = document.getElementById('cajon-overlay');
    if (c) c.style.display = (c.style.display === "block") ? "none" : "block";
};

window.finalizarSesion = function (event) {
    event.preventDefault();
    const timer = document.getElementById('timer');
    const root = document.getElementById('sala-interactiva-root');
    const urlDestino = event.currentTarget.href;

    if (!timer || !root) {
        window.location.href = urlDestino;
        return;
    }

    const partes = timer.innerText.split(':').map(Number);
    const segundosTotales = (partes[0] * 3600) + (partes[1] * 60) + partes[2];

    fetch('/salas/guardar', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            tipo: root.getAttribute('data-tipo'),
            segundos: segundosTotales
        })
    }).finally(() => {
        window.location.href = urlDestino;
    });
};