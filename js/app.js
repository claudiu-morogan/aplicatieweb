/* Countdown & comutare temă */
(function(){
  const etape = window.__ETAPE__ || [];
  const tbody = document.getElementById('tabel-etape');
  const nextStageEl = document.getElementById('next-stage');
  const yearEl = document.getElementById('year');
  const toggleBtn = document.getElementById('toggle-theme');
  const autoModeCb = document.getElementById('auto-mode');
  const title = document.getElementById('site-title');
  const activeThemeLabel = document.getElementById('active-theme-label');

  yearEl.textContent = new Date().getFullYear();

  // Construcție rânduri
  etape.forEach(({ etapa, estimare }, index) => {
    const tr = document.createElement('tr');
    tr.dataset.index = index;
    tr.innerHTML = `<td>${etapa}</td><td>${new Date(estimare).toLocaleDateString('ro-RO')}</td><td id="timer-${index}" aria-live="off" class="mono">calculând...</td>`;
    tbody.appendChild(tr);
  });

  function plural(v, s) { return v + ' ' + s + (v === 1 ? '' : ''); } // simplu

  function updateTimers(){
    const now = Date.now();
    let nextIndex = -1;
    let nextDiff = Infinity;

    etape.forEach(({ estimare }, index) => {
      const future = new Date(estimare).getTime();
      const diff = future - now;
      const element = document.getElementById(`timer-${index}`);
      const row = tbody.querySelector(`tr[data-index='${index}']`);

      if(diff <= 0){
        element.textContent = 'deja trecut';
        row.classList.add('past');
        return;
      }

      const sec = Math.floor(diff / 1000) % 60;
      const min = Math.floor(diff / 60000) % 60;
      const hrs = Math.floor(diff / 3600000) % 24;
      const days = Math.floor(diff / 86400000);
      element.textContent = `${days} zile, ${hrs} ore, ${min} minute, ${sec} secunde`;

      if(diff < nextDiff){ nextDiff = diff; nextIndex = index; }
    });

    if(nextIndex >= 0){
      const item = etape[nextIndex];
      const remainingDays = Math.floor(nextDiff/86400000);
      nextStageEl.textContent = `${item.etapa} în aproximativ ${remainingDays} zile.`;
    } else {
      nextStageEl.textContent = 'Toate etapele au trecut.';
    }
  }

  updateTimers();
  setInterval(updateTimers, 1000);

  // Logică temă
  const body = document.body;
  const THEME_KEY = 'season-theme';
  const AUTO_KEY = 'season-auto';

  function computeAutoTheme(){
    const now = new Date();
    const month = now.getMonth(); // 0-11
  // Comută automat pe Crăciun de la 15 Nov la 31 Dec
    if(month === 10 && now.getDate() >= 15) return 'christmas';
    if(month === 11) return 'christmas';
    return 'autumn';
  }

  function applyTheme(theme){
    body.classList.toggle('theme-christmas', theme === 'christmas');
    const isXmas = theme === 'christmas';
    toggleBtn.textContent = isXmas ? 'Toamnă 🍁' : 'Crăciun 🎄';
    activeThemeLabel.textContent = 'Tema ' + (isXmas ? 'Crăciun' : 'Toamnă');
    title.textContent = isXmas ? title.dataset.christmas : title.dataset.autumn;
    spawnParticles();
  }

  function loadPrefs(){
    const auto = localStorage.getItem(AUTO_KEY);
    const theme = localStorage.getItem(THEME_KEY);
    if(auto !== null) autoModeCb.checked = auto === '1';
    if(autoModeCb.checked){
      applyTheme(computeAutoTheme());
    } else if(theme){
      applyTheme(theme);
    } else {
      applyTheme('autumn');
    }
  }

  toggleBtn.addEventListener('click', () => {
    if(autoModeCb.checked){
      autoModeCb.checked = false; // disable auto when manual toggle
    }
    const isChristmas = body.classList.contains('theme-christmas');
    applyTheme(isChristmas ? 'autumn' : 'christmas');
    persist();
  });

  autoModeCb.addEventListener('change', () => {
    persist();
    loadPrefs();
  });

  function persist(){
    localStorage.setItem(AUTO_KEY, autoModeCb.checked ? '1' : '0');
    if(!autoModeCb.checked){
      const theme = body.classList.contains('theme-christmas') ? 'christmas' : 'autumn';
      localStorage.setItem(THEME_KEY, theme);
    }
  }

  // Particule sezoniere (frunze / fulgi)
  let particleTimer; let layer;
  function spawnParticles(){
    if(layer){ layer.innerHTML=''; }
    if(!layer){
      layer = document.createElement('div');
      layer.className = 'particle-layer';
      document.body.appendChild(layer);
    }
    clearInterval(particleTimer);
    const isXmas = body.classList.contains('theme-christmas');
    const max = 20;
    for(let i=0;i<max;i++) addParticle(isXmas);
    particleTimer = setInterval(()=>{
      if(layer.children.length < max) addParticle(isXmas);
    }, 2000);
  }

  function addParticle(isXmas){
    const span = document.createElement('span');
    span.className = isXmas ? 'snowflake' : 'leaf';
    span.textContent = isXmas ? (Math.random()>.5?'❄':'✻') : (['🍁','🍂','🍃'][Math.floor(Math.random()*3)]);
    const start = Math.random()*100;
    const end = (start + (Math.random()*40 - 20));
    const duration = 10 + Math.random()*18;
    span.style.left = start+'vw';
    span.style.setProperty('--x-end', end + 'vw');
    span.style.animationDuration = duration+'s';
    span.style.setProperty('--rot-end', (Math.random() > .5 ? '' : '-') + (360 + Math.random()*720) + 'deg');
    layer.appendChild(span);
    setTimeout(()=> span.remove(), duration*1000);
  }

  loadPrefs();
})();
