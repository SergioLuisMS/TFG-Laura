"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.initSalaInteractiva = initSalaInteractiva;

// resources/js/sala-interactiva.js
function initSalaInteractiva(salaId, userPotato) {
  // =====================================================
  // 🕒 TIMER
  // =====================================================
  var segundos = 0;
  var timerInterval = null;

  function iniciarTimer() {
    var el = document.getElementById("timer");

    if (!el) {
      console.warn("⚠️ No se encontró el timer");
      return;
    } // Evita múltiples intervals


    if (timerInterval) {
      clearInterval(timerInterval);
    }

    console.log("✅ Timer iniciado");
    timerInterval = setInterval(function () {
      segundos++;
      var hrs = Math.floor(segundos / 3600);
      var mins = Math.floor(segundos % 3600 / 60);
      var secs = segundos % 60;

      var fmt = function fmt(n) {
        return String(n).padStart(2, "0");
      };

      el.textContent = "".concat(fmt(hrs), ":").concat(fmt(mins), ":").concat(fmt(secs));
      var barra = document.getElementById("focus-bar");

      if (barra) {
        var objetivo = 3600; // 1 hora

        var progreso = Math.min(segundos / objetivo * 100, 100);
        barra.style.width = progreso + "%";
      }
    }, 1000);
  }

  iniciarTimer(); // =====================================================
  // 💓 PULSO AUTOMÁTICO
  // =====================================================

  var pulsoInterval = null;

  if (pulsoInterval) {
    clearInterval(pulsoInterval);
  }

  pulsoInterval = setInterval(function () {
    var root = document.getElementById("sala-interactiva-root");
    var csrf = document.querySelector('meta[name="csrf-token"]');
    if (!root || !csrf) return;
    var tipoSala = root.getAttribute("data-tipo");
    fetch("/salas/registrar-pulso", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": csrf.getAttribute("content")
      },
      body: JSON.stringify({
        sala: tipoSala
      })
    }).then(function _callee(response) {
      var text;
      return regeneratorRuntime.async(function _callee$(_context) {
        while (1) {
          switch (_context.prev = _context.next) {
            case 0:
              _context.next = 2;
              return regeneratorRuntime.awrap(response.text());

            case 2:
              text = _context.sent;

              if (response.ok) {
                _context.next = 5;
                break;
              }

              throw new Error(text);

            case 5:
              return _context.abrupt("return", JSON.parse(text));

            case 6:
            case "end":
              return _context.stop();
          }
        }
      });
    }).then(function () {
      console.log("\uD83D\uDC93 Pulso enviado: ".concat(tipoSala));
    })["catch"](function (err) {
      console.error("❌ Error en el pulso:", err);
    });
  }, 60000); // =====================================================
  // 🥔 CHAT
  // =====================================================

  window.enviarMensaje = function () {
    var input = document.getElementById("chat-input");
    var box = document.getElementById("chat-box");
    if (!input || !box) return;
    var texto = input.value.trim();
    if (texto === "") return;
    var msjObj = {
      nombre: userPotato,
      texto: texto
    };
    var div = document.createElement("div");
    div.className = "mensaje";
    div.innerHTML = "<b>".concat(msjObj.nombre, ":</b> ").concat(msjObj.texto);
    box.appendChild(div);
    var hist = JSON.parse(localStorage.getItem("chat_" + salaId) || "[]");
    hist.push(msjObj);
    localStorage.setItem("chat_" + salaId, JSON.stringify(hist));
    input.value = "";
    box.scrollTop = box.scrollHeight;
  };

  function cargarMensajes() {
    var box = document.getElementById("chat-box");
    if (!box) return;
    var hist = JSON.parse(localStorage.getItem("chat_" + salaId) || "[]");
    box.innerHTML = "\n            <div class=\"mensaje\">\n                <b>Sistema:</b>\n                Hola ".concat(userPotato, ", bienvenida a ").concat(salaId, ".\n            </div>\n        ");
    hist.forEach(function (m) {
      var div = document.createElement("div");
      div.className = "mensaje";
      div.innerHTML = "\n                <b>".concat(m.nombre || "Patata", ":</b>\n                ").concat(m.texto, "\n            ");
      box.appendChild(div);
    });
    box.scrollTop = box.scrollHeight;
  }

  cargarMensajes();
  var chatInput = document.getElementById("chat-input");

  if (chatInput) {
    chatInput.addEventListener("keypress", function (e) {
      if (e.key === "Enter") {
        window.enviarMensaje();
      }
    });
  } // =====================================================
  // 🧪 BOTICA
  // =====================================================


  if (salaId === "botica") {
    var boteActivo = null;
    var offX = 0;
    var offY = 0;
    var botes = document.querySelectorAll(".bote-interactivo");
    var calderoArea = {
      xMin: 18,
      xMax: 48,
      yMin: 35,
      yMax: 82
    };
    botes.forEach(function (bote) {
      bote.addEventListener("mousedown", function (e) {
        boteActivo = bote;
        var rect = bote.getBoundingClientRect();
        offX = e.clientX - rect.left;
        offY = e.clientY - rect.top;
        bote.style.zIndex = 1000;
      });
    });
    document.addEventListener("mousemove", function (e) {
      if (!boteActivo) return;
      var contenedor = document.querySelector(".capa-mapa").getBoundingClientRect();
      boteActivo.style.left = (e.clientX - contenedor.left - offX) / contenedor.width * 100 + "%";
      boteActivo.style.top = (e.clientY - contenedor.top - offY) / contenedor.height * 100 + "%";
    });
    document.addEventListener("mouseup", function (e) {
      if (!boteActivo) return;
      var rImg = document.getElementById("fondo-img").getBoundingClientRect();
      var px = (e.clientX - rImg.left) / rImg.width * 100;
      var py = (e.clientY - rImg.top) / rImg.height * 100;

      if (px >= calderoArea.xMin && px <= calderoArea.xMax && py >= calderoArea.yMin && py <= calderoArea.yMax) {
        boteActivo.style.display = "none";
        document.querySelectorAll(".reaccion-caldero").forEach(function (r) {
          r.style.display = "none";
        });
        var reaccion = document.getElementById("reaccion-" + boteActivo.id);

        if (reaccion) {
          reaccion.style.display = "block";
        }
      }

      boteActivo.style.zIndex = 100;
      boteActivo = null;
    });
  }
} // =====================================================
// 🌐 FUNCIONES GLOBALES
// =====================================================


window.toggleCajon = function () {
  var c = document.getElementById("cajon-overlay");
  if (!c) return;
  c.style.display = c.style.display === "block" ? "none" : "block";
};

window.finalizarSesion = function (event) {
  event.preventDefault();
  var timer = document.getElementById("timer");
  var root = document.getElementById("sala-interactiva-root");
  var urlDestino = event.currentTarget.href;

  if (!timer || !root) {
    window.location.href = urlDestino;
    return;
  }

  var partes = timer.innerText.split(":").map(Number);
  var segundosTotales = partes[0] * 3600 + partes[1] * 60 + partes[2];
  fetch("/salas/guardar", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
    },
    body: JSON.stringify({
      sala: root.getAttribute("data-tipo"),
      segundos: segundosTotales
    })
  })["finally"](function () {
    window.location.href = urlDestino;
  });
};
//# sourceMappingURL=sala-interactiva.dev.js.map
