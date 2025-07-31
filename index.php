<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8">
  <title>Countdown până la toamnă 🍁</title>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap');

    body {
      margin: 0;
      font-family: 'Roboto', sans-serif;
      background-image: url('https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=1920&q=80');
      background-size: cover;
      background-position: center;
      background-attachment: fixed;
      color: #fff;
      text-shadow: 1px 1px 2px #000;
    }

    .container {
      background-color: rgba(0, 0, 0, 0.6);
      max-width: 800px;
      margin: 60px auto;
      padding: 30px;
      border-radius: 15px;
      box-shadow: 0 0 20px rgba(0,0,0,0.5);
    }

    h1 {
      text-align: center;
      font-size: 2.5em;
      margin-bottom: 20px;
      color: #ffcc80;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      background-color: rgba(255, 255, 255, 0.1);
    }

    th, td {
      padding: 12px 16px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.2);
      text-align: left;
    }

    th {
      background-color: rgba(255, 204, 128, 0.3);
      color: #ffe0b2;
    }

    td {
      color: #fff3e0;
    }

    @media (max-width: 600px) {
      h1 {
        font-size: 1.8em;
      }

      .container {
        padding: 20px;
      }
    }
  </style>
</head>
<body>

  <div class="container">
    <h1>Etape până la toamnă 🍂</h1>
    <table>
      <thead>
        <tr>
          <th>Etapă</th>
          <th>Estimare</th>
          <th>Timp rămas</th>
        </tr>
      </thead>
      <tbody id="tabel-etape"></tbody>
    </table>
  </div>

  <script>
    const etape = [
      { etapa: "Scade temperatura sub 30°C (în general)", estimare: "2025-08-15T00:00:00" },
      { etapa: "Primele frunze galbene în copaci", estimare: "2025-09-01T00:00:00" },
      { etapa: "Vânt mai răcoros dimineața", estimare: "2025-09-05T00:00:00" },
      { etapa: "Simți nevoia de geacă dimineața", estimare: "2025-09-10T00:00:00" },
      { etapa: "Început oficial al toamnei", estimare: "2025-09-22T00:00:00" },
    ];

    const tbody = document.getElementById("tabel-etape");

    // Inițializare rânduri
    etape.forEach(({ etapa, estimare }, index) => {
      const tr = document.createElement("tr");
      tr.innerHTML = `
        <td>${etapa}</td>
        <td>${new Date(estimare).toLocaleDateString("ro-RO")}</td>
        <td id="timer-${index}">calculând...</td>
      `;
      tbody.appendChild(tr);
    });

    function updateTimers() {
      const now = new Date();

      etape.forEach(({ estimare }, index) => {
        const future = new Date(estimare);
        const diff = future - now;

        const element = document.getElementById(`timer-${index}`);

        if (diff <= 0) {
          element.textContent = "deja trecut";
          return;
        }

        const sec = Math.floor(diff / 1000) % 60;
        const min = Math.floor(diff / (1000 * 60)) % 60;
        const hrs = Math.floor(diff / (1000 * 60 * 60)) % 24;
        const days = Math.floor(diff / (1000 * 60 * 60 * 24));

        element.textContent = `${days} zile, ${hrs} ore, ${min} minute, ${sec} secunde`;
      });
    }

    // Actualizează la fiecare secundă
    updateTimers();
    setInterval(updateTimers, 1000);
  </script>

</body>
</html>

