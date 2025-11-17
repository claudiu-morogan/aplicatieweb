// Embeddable countdown widget (prototype)
// Usage: include <div class="aweb-countdown" data-season="christmas"></div>
// and then <script src="/aplicatieweb/embed/widget.js" async></script>

(function(){
  // Compute a sensible API base for json.php.
  // Priority: container[data-api] -> script[data-api] -> script src dir -> site root '/aplicatieweb/' fallback
  function computeApiUrl(container){
    // 1) per-container override
    if(container && container.dataset && container.dataset.api){
      return container.dataset.api + (container.dataset.api.indexOf('?') === -1 ? (container.dataset.api.endsWith('/') ? 'json.php?format=json' : '/json.php?format=json') : '');
    }
    // 2) script tag override
    try{
      const scripts = document.getElementsByTagName('script');
      for(let i=scripts.length-1;i>=0;i--){
        const s = scripts[i];
        if(!s.src) continue;
        if(s.src.indexOf('embed/widget.js') !== -1 || s.src.indexOf('/embed/') !== -1){
          // use script directory as base
          const url = new URL(s.src, location.href);
          url.pathname = url.pathname.replace(/\/[^/]*$/, '/');
          // assume json.php is one level up from embed/ or at root
          if(url.pathname.endsWith('/embed/')){
            return url.origin + url.pathname.replace(/\/embed\/$/, '/') + 'json.php?format=json';
          }
          return url.origin + url.pathname + 'json.php?format=json';
        }
      }
    }catch(e){ /* ignore */ }
    // 3) fallback to site root path used in this project
    return '/aplicatieweb/json.php?format=json';
  }

  function qs(el, sel){ return el.querySelector(sel); }

  function formatNumber(n){ return String(n).padStart(2,'0'); }

  function createWidget(container, seasons, opts){
    const season = container.getAttribute('data-season') || opts.defaultSeason || 'christmas';
    const idxAttr = container.getAttribute('data-index');
    const idx = idxAttr !== null ? parseInt(idxAttr,10) : null;

    const items = seasons[season] || [];
    if(items.length === 0){ container.textContent = 'No events'; return; }

    let target = null;
    if(idx !== null && items[idx]){
      target = items[idx];
    } else {
      // pick first future item
      const now = new Date();
      for(const it of items){
        const d = new Date(it.estimare);
        if(d > now){ target = it; break; }
      }
      if(!target) target = items[items.length-1];
    }

      // Theme color tokens (can be expanded)
      let bg = 'linear-gradient(180deg,#fff,#f9fafb)';
      let accent = '#c7254e';
      let text = '#111';
      if(season === 'christmas'){
        bg = 'linear-gradient(180deg,#fffef6,#f0fff4)';
        accent = '#d63447'; // red
        text = '#08311b'; // deep green-ish
      } else if(season === 'autumn'){
        bg = 'linear-gradient(180deg,#fff7ed,#fff2e6)';
        accent = '#d97706'; // orange
        text = '#3b2f2f';
      }

      // expose CSS vars on host (inherited into Shadow DOM :host)
      try{ container.style.setProperty('--widget-bg', bg); }catch(e){}
      try{ container.style.setProperty('--widget-accent', accent); }catch(e){}
      try{ container.style.setProperty('--widget-text', text); }catch(e){}

      const root = container.attachShadow({mode:'open'});
      const style = document.createElement('style');
      style.textContent = `
        :host{font-family:system-ui,Segoe UI,Arial;background:var(--widget-bg);display:inline-block;padding:12px;border-radius:12px;box-shadow:0 8px 22px rgba(0,0,0,0.08);min-width:240px;color:var(--widget-text)}
        .wrap{display:flex;align-items:center;gap:12px}
        .accent{width:10px;height:40px;border-radius:6px;background:var(--widget-accent);flex:0 0 auto}
        .content{flex:1}
        .title{font-weight:700;margin:0 0 6px 0;font-size:15px}
        .count{font-size:18px;letter-spacing:0.2px;display:flex;gap:8px;align-items:baseline}
        .seg{display:inline-flex;flex-direction:column;align-items:center}
        .num{font-weight:700;display:inline-block;padding:4px 8px;border-radius:6px;min-width:44px;text-align:center;background:rgba(255,255,255,0.6);color:var(--widget-text);box-shadow:inset 0 -6px 12px rgba(0,0,0,0.03)}
        .lbl{font-size:11px;color:rgba(0,0,0,0.45);margin-top:4px}
        /* animation when a number changes */
        .num.pulse{animation: aweb-pulse .42s cubic-bezier(.2,.9,.2,1)}
        @keyframes aweb-pulse{0%{transform:translateY(0) scale(1);opacity:1}30%{transform:translateY(-8px) scale(1.05);opacity:1}100%{transform:translateY(0) scale(1);opacity:1}}
        .small{font-size:12px;color:rgba(0,0,0,0.5);margin-top:6px}
        .pill{display:inline-block;padding:4px 8px;border-radius:999px;background:rgba(0,0,0,0.04);font-size:12px;color:var(--widget-text)}
      `;

      const wrapper = document.createElement('div');
      wrapper.className = 'widget';
      wrapper.innerHTML = `
        <div class="wrap">
          <div class="accent" aria-hidden="true"></div>
          <div class="content">
            <div class="title">${escapeHtml(target.etapa)}</div>
            <div class="count" aria-live="polite">--:--:--:--</div>
            <div class="small">${escapeHtml(target.estimare)}</div>
          </div>
          <div class="controls" aria-hidden="false" style="display:flex;flex-direction:column;gap:6px;margin-left:8px;">
            <button class="aweb-prev" title="Previous" aria-label="Previous">◀</button>
            <button class="aweb-next" title="Next" aria-label="Next">▶</button>
          </div>
        </div>
      `;

      root.appendChild(style);
      root.appendChild(wrapper);

  const countEl = root.querySelector('.count');
  const titleEl = root.querySelector('.title');
  const smallEl = root.querySelector('.small');
  const prevBtn = root.querySelector('.aweb-prev');
  const nextBtn = root.querySelector('.aweb-next');

  // store items and current index on the container for navigation
  container._aweb_items = items;
    // previous values for animation diffing
    container._aweb_prev = container._aweb_prev || {};
    // determine current index
    let currentIndex = 0;
    if(idx !== null && items[idx]){
      currentIndex = idx;
    } else {
      currentIndex = items.indexOf(target);
      if(currentIndex === -1) currentIndex = items.length-1;
    }
    container._aweb_idx = currentIndex;

    function setTargetByIndex(i){
      if(!items[i]) return;
      container._aweb_idx = i;
      target = items[i];
      // update title/date immediately
      if(titleEl) titleEl.textContent = target.etapa;
      if(smallEl) smallEl.textContent = target.estimare;
      // reset prev so numbers re-render
      container._aweb_prev = {};
      update();
    }

    if(prevBtn){ prevBtn.addEventListener('click', function(){
      const i = (container._aweb_idx - 1 + items.length) % items.length;
      setTargetByIndex(i);
    }); }
    if(nextBtn){ nextBtn.addEventListener('click', function(){
      const i = (container._aweb_idx + 1) % items.length;
      setTargetByIndex(i);
    }); }
    function update(){
      const now = new Date();
      const then = new Date(target.estimare);
      let diff = Math.max(0, then - now);
      const days = Math.floor(diff / (1000*60*60*24));
      diff -= days * (1000*60*60*24);
      const hours = Math.floor(diff / (1000*60*60));
      diff -= hours * (1000*60*60);
      const minutes = Math.floor(diff / (1000*60));
      diff -= minutes * (1000*60);
      const seconds = Math.floor(diff / 1000);

      const parts = {
        days: String(days),
        hours: formatNumber(hours),
        minutes: formatNumber(minutes),
        seconds: formatNumber(seconds)
      };

      // initialize segments on first run
      if(!container._aweb_prev.initialized){
        countEl.innerHTML = `
          <div class="seg"><div class="num" data-key="days">${escapeHtml(parts.days)}</div><div class="lbl">zile</div></div>
          <div class="seg"><div class="num" data-key="hours">${escapeHtml(parts.hours)}</div><div class="lbl">ore</div></div>
          <div class="seg"><div class="num" data-key="minutes">${escapeHtml(parts.minutes)}</div><div class="lbl">min</div></div>
          <div class="seg"><div class="num" data-key="seconds">${escapeHtml(parts.seconds)}</div><div class="lbl">sec</div></div>
        `;
        container._aweb_prev = Object.assign({}, parts, {initialized:true});
        return;
      }

      // update only changed segments with a pulse animation
      for(const k of ['days','hours','minutes','seconds']){
        const v = parts[k];
        if(container._aweb_prev[k] !== v){
          const el = countEl.querySelector('.num[data-key="'+k+'"]');
          if(el){
            el.textContent = v;
            el.classList.remove('pulse');
            // trigger reflow to restart animation
            void el.offsetWidth;
            el.classList.add('pulse');
            // cleanup after animation
            setTimeout(()=> el.classList.remove('pulse'), 500);
          }
        }
      }
      container._aweb_prev = Object.assign({}, parts);
    }

  update();
  // clear previous interval if exists
  if(container._aweb_tid) try{ clearInterval(container._aweb_tid); }catch(e){}
  const tid = setInterval(update, 1000);
    // store interval id so it can be cleared later if needed
    container._aweb_tid = tid;
  }

  function escapeHtml(s){ return (s||'').replace(/[&<>"']/g, function(m){return{'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[m];}); }

  // Auto-find containers
  function init(){
    const containers = Array.from(document.querySelectorAll('.aweb-countdown'));
    if(containers.length === 0) return;

  // fetch seasons JSON (allow overrides). Use the first container for any per-container override,
  // otherwise computeApiUrl can handle null and return the default fallback.
  const api = computeApiUrl(containers[0] || null);
    fetch(api).then(r=>{
      if(!r.ok) throw new Error('HTTP ' + r.status);
      return r.json();
    }).then(data=>{
      for(const c of containers){
        try{ createWidget(c, data, {defaultSeason:'christmas'}); } catch(e){ c.textContent = 'Error'; console.error(e); }
      }
    }).catch(err=>{
      console.error('widget fetch failed', err);
      for(const c of containers) c.textContent = 'Data unavailable';
    });
  }

  // Run on DOMContentLoaded if script loaded in head; otherwise run immediately
  if(document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();

})();
