<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Countdown Toamnă & Crăciun 🍁🎄</title>
  <meta name="description" content="Countdown etape până la toamnă cu temă sezonieră și mod Crăciun automat sau manual." />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css?v=1" />
</head>
<body>
  <header class="site-header" role="banner">
    <div class="inner">
      <div class="brand-block">
        <h1 id="site-title" class="gradient-text" data-autumn="Etape până la toamnă 🍂" data-christmas="Etape până la Crăciun 🎄">Etape până la toamnă 🍂</h1>
        <a class="author-link" href="https://www.claudiu-morogan.dev" target="_blank" rel="noopener noreferrer" title="Portofoliu Claudiu Morogan">Claudiu Morogan</a>
      </div>
      <nav class="toolbar" aria-label="Setări temă">
        <div class="theme-controls">
          <button id="toggle-theme" class="btn" type="button" aria-pressed="false" aria-label="Schimbă tema">Crăciun 🎄</button>
          <label class="auto-switch" title="Mod automat în funcție de sezon"><input type="checkbox" id="auto-mode" checked /> <span>Auto</span></label>
          <label class="auto-switch" title="Pornește/Opresc particulele"><input type="checkbox" id="particles-toggle" checked /> <span>Particule</span></label>
        </div>
      </nav>
    </div>
    <div class="lights-bar" aria-hidden="true">
      <svg class="wire" viewBox="0 0 100 40" preserveAspectRatio="none" role="img" aria-label="sârmă lumini decorative">
        <path class="wire-base" d="M0 18 C12 4 25 34 38 18 S63 34 76 18 90 30 100 22"/>
        <path class="wire-glow" d="M0 18 C12 4 25 34 38 18 S63 34 76 18 90 30 100 22"/>
      </svg>
    </div>
  </header>

  <main class="container" role="main">
    <section aria-labelledby="tabel-title">
      <h2 id="tabel-title" class="visually-hidden">Lista etapelor și timpul rămas</h2>
      <table class="countdown-table" aria-describedby="legend">
        <thead>
          <tr>
            <th scope="col">Etapă</th>
            <th scope="col">Estimare</th>
            <th scope="col">Timp rămas</th>
          </tr>
        </thead>
        <tbody id="tabel-etape"></tbody>
      </table>
      <p id="legend" class="legend">Date estimative – pot varia după vreme.</p>
    </section>

    <section class="next-big" aria-live="polite" aria-atomic="true">
      <h3>Următorul prag</h3>
      <p id="next-stage">Identificare...</p>
    </section>
  </main>

  <footer class="site-footer">
    <p>&copy; <span id="year"></span> Sezon | <span id="active-theme-label">Tema Toamnă</span></p>
  </footer>

  <!-- Datele etapelor au fost mutate în fișiere JSON în directorul /data
       Fișiere: data/etape.json și data/etape_craciun.json
       Dacă serverul nu le poate servi, aplicația încearcă în continuare să folosească
       variabilele globale `window.__ETAPE__` și `window.__ETAPE_CRACIUN__` ca fallback. -->
  <script>
    // Încarcă datele de sezon din API (json.php?format=json) și expune ca variabile globale
    (function(){
      const api = './json.php?format=json';
      console.info('[countdown] încerc fetch API seasons:', api);
      fetch(api, {cache: 'no-cache'})
        .then(res => {
          if(!res.ok) throw new Error('HTTP ' + res.status);
          const ct = res.headers.get('Content-Type') || '';
          if(ct.indexOf('application/json') === -1) throw new Error('Nu este JSON');
          return res.json();
        })
        .then(data => {
          if(data.autumn) window.__ETAPE__ = data.autumn;
          if(data.christmas) window.__ETAPE_CRACIUN__ = data.christmas;
          console.info('[countdown] seasons loaded from API', {autumn: (window.__ETAPE__||[]).length, christmas: (window.__ETAPE_CRACIUN__||[]).length});
          // Notificăm aplicația că datele au fost încărcate
          try{ window.dispatchEvent(new CustomEvent('seasons:loaded', { detail: data })); }catch(e){}
        })
        .catch(err => {
          console.warn('[countdown] Nu am putut încărca seasons API, folosesc fallbackuri dacă există', err);
        });
    })();
  </script>
  <script>console.info('[countdown] inline: index.php a încărcat script-urile');</script>
  <!-- Loader dinamic pentru js/app.js: încearcă mai multe căi și raportează onload/onerror în consolă -->
  <script>
    (function(){
      const candidates = ['./js/app.js?v=1','./js/app.js','/aplicatieweb/js/app.js?v=1','/aplicatieweb/js/app.js'];
      console.info('[countdown] încerc să încarc js/app.js din căi posibile:', candidates);
      let tried = 0;
      function tryLoad(idx){
        if(idx >= candidates.length){
          console.error('[countdown] Nu am reușit să încarc niciunul dintre fișierele js/app.js. Verifică calea și permisiunile.');
          // afișăm un mesaj vizibil pe pagină
          try{
            const el = document.getElementById('next-stage');
            if(el) el.textContent = 'Eroare: nu s-a putut încărca scriptul aplicației.';
          }catch(e){}
          return;
        }
        const url = candidates[idx];
        const s = document.createElement('script');
        s.src = url;
        s.async = false;
        s.onload = function(){ console.info('[countdown] script extern încărcat:', url); };
        s.onerror = function(ev){ console.warn('[countdown] eroare la încărcarea scriptului:', url); s.remove(); tryLoad(idx+1); };
        document.head.appendChild(s);
      }
      tryLoad(0);
    })();
  </script>
</body>
</html>

