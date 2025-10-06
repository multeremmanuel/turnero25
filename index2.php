<?php
// index.php (corregido)
// Nota: ajustar la ruta de 'conexion.php' si tu archivo está en otra carpeta.
require 'conexion.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=720, height=1080, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title>Pantalla de Turnos - Tiempo Real</title>

<!-- bootstrap (solo para utilidades; los estilos principales son custom para TV) -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<style>
  /* ===== Base ===== */
  html,body { height:100%; margin:0; padding:0; background:#f5f7fa; font-family: "Segoe UI", Tahoma, sans-serif; overflow:hidden; }
  .container-fluid { height:100%; padding:6px; }

  /* ===== Layout horizontal adaptado a 720x1080 TV ===== */
  .row.h-100 { height:100%; }

  .left-col, .right-col { height:100%; }

  /* Video (izquierda) */
  .video-wrapper {
    height: 62%; /* ocupa mayor parte de columna izquierda */
    border-radius:10px;
    overflow:hidden;
    box-shadow: 0 6px 18px rgba(0,0,0,0.12);
  }
  .video-wrapper video { width:100%; height:100%; object-fit:cover; display:block; }

  /* Turnos en curso debajo del video */
  .turnos-en-curso {
    height: 34%;
    margin-top:6px;
    display:flex;
    flex-wrap:wrap;
    gap:6px;
    align-items:center;
    justify-content:center;
  }

  .turno-card {
    background:#fff;
    min-width:64px;
    width:8%;
    max-width:110px;
    padding:8px 6px;
    border-radius:8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    text-align:center;
    font-weight:700;
    transition: transform .12s;
  }
  .turno-card:hover{ transform: translateY(-4px); }

  .turno-card .caja { font-size: clamp(10px, 1.6vh, 16px); color:#444; font-weight:600; margin-top:4px; }

  /* Prioridades (solo color del texto del número) */
  .normal { color:#198754; }      /* verde */
  .prioritario { color:#fd7e14; } /* naranja */
  .especial { color:#0d6efd; }    /* azul PAN */

  /* ===== Right column (Turno actual / Siguiente / Total) ===== */
  .right-panel {
    display:flex;
    flex-direction:column;
    justify-content:flex-start;
    gap:12px;
    padding-left:10px;
    padding-right:6px;
  }

  .card-display {
    background:#fff;
    border-radius:12px;
    padding:12px;
    box-shadow: 0 8px 26px rgba(0,0,0,0.10);
    text-align:center;
  }

  /* Turno grande - se escala con viewport */
  .turno-grande {
    font-size: clamp(48px, 16vh, 220px); /* Escala con la pantalla */
    line-height: 0.95;
    font-weight:900;
    margin: 6px 0 4px;
  }
  .modulo {
    font-size: clamp(16px, 3.2vh, 36px);
    color:#333;
    font-weight:700;
  }

  .turno-mediano {
    font-size: clamp(28px, 6vh, 84px);
    font-weight:800;
    margin-top:6px;
  }

  /* Total turnos atendidos */
  .total-card {
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;
    gap:6px;
    padding:14px;
  }
  #total-turnos {
    font-size: clamp(20px, 6vh, 64px);
    font-weight:900;
    color:#005BAC; /* azul PAN */
  }

  /* Títulos */
  h2.page-title { font-size: clamp(14px, 3vh, 28px); margin-bottom:6px; color:#222; font-weight:700; }

  /* Ajustes responsive fino por si la TV tiene distinta densidad */
  @media (max-width:720px) {
    .turno-card { min-width:58px; }
  }
</style>
</head>
<body>

<!-- audio tono -->
<audio id="audioTurno" src="tono.mp3" preload="auto"></audio>

<div class="container-fluid">
  <div class="row h-100 gx-2">

    <!-- IZQUIERDA: video + turnos en curso -->
    <div class="col-6 left-col d-flex flex-column">
      <div class="video-wrapper">
        <video autoplay muted loop playsinline>
          <source src="video.mp4" type="video/mp4">
          Tu dispositivo no soporta video HTML5.
        </video>
      </div>

      <div class="turnos-en-curso mt-2" id="turnos-en-curso" aria-live="polite">
        <!-- tarjetas se llenan por JS -->
      </div>
    </div>

    <!-- DERECHA: turno actual / siguiente / total -->
    <div class="col-6 right-col">
      <div class="right-panel">

        <div class="card-display">
          <h2 class="page-title">Turno Actual</h2>
          <div id="ultimo-turno" aria-live="polite">
            <!-- contenido via JS -->
            <div class="turno-grande">--</div>
            <div class="modulo">Caja: --</div>
          </div>
        </div>

        <div class="card-display">
          <h2 class="page-title">Siguiente Turno</h2>
          <div id="siguiente-turno" aria-live="polite">
            <div class="turno-mediano">--</div>
          </div>
        </div>

        <div class="card-display total-card">
          <div style="font-size:14px;color:#444;font-weight:700;">Total Turnos Atendidos</div>
          <div id="total-turnos">0</div>
          <div style="font-size:13px;color:#666;">(Actualizado cada 5s)</div>
        </div>

      </div>
    </div>

  </div>
</div>

<script>
/*
  JS: usa los endpoints que ya tienes:
  - get_turnos_index.php  -> devuelve { ultimo: {...}, siguiente: {...}, enCurso: [...] }
  - get_total_turnos.php  -> devuelve { total: N }
*/

let ultimoTurnoId = null;

function actualizarTurnos() {
  $.ajax({
    url: 'get_turnos_index.php',
    method: 'GET',
    dataType: 'json',
    cache: false,
    success: function(data) {
      try {
        // reproducir tono si cambió
        let nuevo = data.ultimo ? data.ultimo.turno : null;
        if (ultimoTurnoId !== null && nuevo !== ultimoTurnoId) {
          const audio = document.getElementById('audioTurno');
          if (audio) { audio.play().catch(()=>{}); }
        }
        ultimoTurnoId = nuevo;

        // turno actual
        if (data.ultimo) {
          let cls = 'normal';
          switch (parseInt(data.ultimo.prioridad)) {
            case 1: cls = 'prioritario'; break;
            case 3: cls = 'especial'; break;
            default: cls = 'normal';
          }
          $('#ultimo-turno').html(`
            <div class="${cls} turno-grande">${data.ultimo.turno}</div>
            <div class="modulo">Caja: ${data.ultimo.caja ? data.ultimo.caja : '--'}</div>
          `);
        } else {
          $('#ultimo-turno').html('<div class="turno-grande">--</div><div class="modulo">Caja: --</div>');
        }

        // siguiente turno
        if (data.siguiente) {
          let cls2 = 'normal';
          switch (parseInt(data.siguiente.prioridad)) {
            case 1: cls2 = 'prioritario'; break;
            case 3: cls2 = 'especial'; break;
            default: cls2 = 'normal';
          }
          $('#siguiente-turno').html(`<div class="${cls2} turno-mediano">${data.siguiente.turno}</div>`);
        } else {
          $('#siguiente-turno').html('<div class="turno-mediano">--</div>');
        }

        // en curso (tarjetas)
        if (Array.isArray(data.enCurso) && data.enCurso.length > 0) {
          const list = data.enCurso.slice(0,12).map(t => {
            let c = 'normal';
            switch (parseInt(t.prioridad)) {
              case 1: c = 'prioritario'; break;
              case 3: c = 'especial'; break;
            }
            return `<div class="turno-card ${c}">
                      <div style="font-size:1.15rem">${t.turno}</div>
                      <div class="caja">${t.caja ? t.caja : ''}</div>
                    </div>`;
          }).join('');
          $('#turnos-en-curso').html(list);
        } else {
          $('#turnos-en-curso').html('<div style="color:#666">No hay turnos en curso</div>');
        }

      } catch (e) {
        console.error('Error procesando respuesta get_turnos_index:', e);
      }
    },
    error: function(xhr, status, err) {
      console.error('Error AJAX get_turnos_index:', status, err);
    }
  });
}

function actualizarTotal() {
  $.ajax({
    url: 'get_total_turnos.php',
    method: 'GET',
    dataType: 'json',
    cache: false,
    success: function(d) {
      if (d && typeof d.total !== 'undefined') {
        $('#total-turnos').text(d.total);
      }
    },
    error: function() { /* no mostrar error en pantalla TV */ }
  });
}

// Inicializar y refrescar
actualizarTurnos();
actualizarTotal();

setInterval(actualizarTurnos, 3000);
setInterval(actualizarTotal, 5000);
</script>

</body>
</html>
