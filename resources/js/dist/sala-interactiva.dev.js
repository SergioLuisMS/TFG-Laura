"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.initSalaInteractiva = initSalaInteractiva;

// resources/js/sala-interactiva.js
function initSalaInteractiva(salaId, userPotato) {
  var segundos = 0;
  var boteActivo = null;
  var offX, offY; // --- 🕒 TIMER ---

  var timerInterval = setInterval(function () {
    segundos++;
    var el = document.getElementById("timer");

    if (el) {
      var hrs = Math.floor(segundos / 3600);
      var mins = Math.floor(segundos % 3600 / 60);
      var secs = segundos % 60;

      var fmt = function fmt(n) {
        return n < 10 ? "0" + n : n;
      };

      el.innerText = "".concat(fmt(hrs), ":").concat(fmt(mins), ":").concat(fmt(secs));
    } else {
      clearInterval(timerInterval);
    }
  }, 1000); // --- 💓 PULSO (Envío automático cada 60s) ---

  var pulsoInterval = setInterval(function () {
    var root = document.getElementById("sala-interactiva-root");
    var csrf = document.querySelector('meta[name="csrf-token"]'); // Si no estamos en una sala o no hay token, no hacemos nada

    if (!root || !csrf) return; // Obtenemos el tipo de sala directamente del atributo data del HTML

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
    }).then(function (response) {
      return response.json();
    }).then(function (data) {
      return console.log("\uD83D\uDC93 Pulso registrado en: ".concat(tipoSala));
    })["catch"](function (err) {
      return console.error("Error en el pulso:", err);
    });
  }, 60000); // 60 segundos
  // --- 🥔 CHAT ---

  window.enviarMensaje = function () {
    var input = document.getElementById("chat-input");
    var box = document.getElementById("chat-box");
    if (!input || !box) return;
    var texto = input.value.trim();

    if (texto !== "") {
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
    }
  };

  function cargarMensajes() {
    var box = document.getElementById("chat-box");
    if (!box) return;
    var hist = JSON.parse(localStorage.getItem("chat_" + salaId) || "[]");
    box.innerHTML = "<div class=\"mensaje\"><b>Sistema:</b> Hola ".concat(userPotato, ", bienvenida a ").concat(salaId, ".</div>");
    hist.forEach(function (m) {
      var div = document.createElement("div");
      div.className = "mensaje";
      div.innerHTML = "<b>".concat(m.nombre || "Patata", ":</b> ").concat(m.texto);
      box.appendChild(div);
    });
    box.scrollTop = box.scrollHeight;
  }

  cargarMensajes();
  var chatInput = document.getElementById("chat-input");

  if (chatInput) {
    chatInput.addEventListener("keypress", function (e) {
      if (e.key === "Enter") window.enviarMensaje();
    });
  } // --- 🧪 LÓGICA EXCLUSIVA DE BOTICA ---


  if (salaId === "botica") {
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
          return r.style.display = "none";
        });
        var r = document.getElementById("reaccion-" + boteActivo.id);
        if (r) r.style.display = "block";
      }

      boteActivo.style.zIndex = 100;
      boteActivo = null;
    });
  }
} // --- 🌐 FUNCIONES GLOBALES (Fuera del init) ---


window.toggleCajon = function () {
  var c = document.getElementById("cajon-overlay");
  if (c) c.style.display = c.style.display === "block" ? "none" : "block";
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
      // Cambia 'data-sala' por 'data-tipo'
      segundos: segundosTotales
    })
  })["finally"](function () {
    window.location.href = urlDestino;
  });
};

document.addEventListener("DOMContentLoaded", function () {
  var root = document.getElementById("sala-interactiva-root");

  if (root) {
    var salaId = root.getAttribute("data-tipo");
    var user = root.getAttribute("data-user"); // ¡Aquí activamos todo!

    initSalaInteractiva(salaId, user);
    console.log("🚀 Sistema iniciado para:", salaId);
  }
});
//# sourceMappingURL=sala-interactiva.dev.js.map
