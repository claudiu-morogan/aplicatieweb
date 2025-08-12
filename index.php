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
      <h1 id="site-title" class="gradient-text" data-autumn="Etape până la toamnă 🍂" data-christmas="Etape până la Crăciun 🎄">Etape până la toamnă 🍂</h1>
      <div class="theme-controls">
        <button id="toggle-theme" class="btn" type="button" aria-pressed="false" aria-label="Schimbă tema">Crăciun 🎄</button>
        <label class="auto-switch"><input type="checkbox" id="auto-mode" checked /> <span>Auto</span></label>
      </div>
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

  <script>
    window.__ETAPE__ = [
      { etapa: "Scade temperatura sub 30°C (în general)", estimare: "2025-08-15T00:00:00" },
      { etapa: "Primele frunze galbene în copaci", estimare: "2025-09-01T00:00:00" },
      { etapa: "Vânt mai răcoros dimineața", estimare: "2025-09-05T00:00:00" },
      { etapa: "Simți nevoia de geacă dimineața", estimare: "2025-09-10T00:00:00" },
      { etapa: "Început oficial al toamnei", estimare: "2025-09-22T00:00:00" }
    ];
  </script>
  <script src="js/app.js?v=1"></script>
</body>
</html>

