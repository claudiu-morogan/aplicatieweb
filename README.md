# AplicatieWeb — Countdown Toamnă & Crăciun 🍂🎄

> Un proiect mic, vesel și sezonier care numără zilele până la momentele pline de frunze sau beteală.

Acest repo conține o pagină principală (`index.php`) care afișează etape pentru toamnă și Crăciun. Datele sunt păstrate în JSON-uri în `data/` și există un mic API (`json.php`) care le servește.

Ce e aici (pe scurt)
- `index.php` — interfața principală (teme: toamnă / Crăciun, particule, luminițe)
- `css/style.css` — stiluri principale (tema sezonieră)
- `js/app.js` — logica de countdown, animatii, schimbare temă
- `json.php` — listare `data/` + endpoint API: `?format=json` (returnează `autumn` + `christmas`)
- `data/etape_toamna.json` — etapele pentru toamnă (2026)
- `data/etape_craciun.json` — etapele pentru Crăciun
- `embed/` — prototip widget embeddable (JS + demo + README)

De ce e mișto
- Design dual: toamnă caldă și Crăciun festiv.
- Widget embeddable: poți pune un countdown în orice pagină (same-origin) cu un singur div.
- API simplu: consumă `json.php?format=json` și primești structura sezonieră.

Cum rulezi local (XAMPP pe Windows)
1. Pornește Apache în XAMPP.
2. Copiază proiectul în `C:\xampp\htdocs` (dacă nu e deja).
3. Deschide în browser:
   - Pagina principală: `http://localhost/aplicatieweb/`
   - API JSON: `http://localhost/aplicatieweb/json.php?format=json`
   - Demo widget: `http://localhost/aplicatieweb/embed/demo.html`

Widget embeddable — folosește rapid
1. Pune în pagina ta (same origin) elementul:

```html
<div class="aweb-countdown" data-season="christmas"></div>
<script src="/aplicatieweb/embed/widget.js" async></script>
```

2. Atribute utile:
- `data-season="autumn"|"christmas"` — forțează tema/event set
- `data-index="0"` — arată evenimentul cu indexul 0 (primul)
- `data-api="/cale/personalizata/json.php?format=json"` — override pentru URL API

Design notes & comportament
- Widget-ul folosește Shadow DOM pentru a nu ștearsă stilurile gazdă.
- Include animație subtilă la schimbarea cifrelor și butoane Prev/Next.

Cum contribui (rapid)
1. Fork → branch → commit → pull request.
2. Testează local (XAMPP) și păstrează limbajul românesc pentru UI (dacă modifici texte existente).

Licență
— Feel free to use and remix this repo for personal or learning projects. Dacă vrei să-l folosești comercial, dă-mi un link și un credit frumos.

Contact
- Dacă ai idei sau bug-uri, deschide un issue sau scrie-mi (link în footerul paginii).

P.S. Daca vrei, îți bag și un badge "🎉 Made with cookies" în README. Sau două. Sau o tură de glazură. 🍪
