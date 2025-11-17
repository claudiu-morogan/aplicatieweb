Embeddable Countdown Widget
===========================

Usage
-----

Drop the JS file into any page on the same origin and add a container element:

```html
<div class="aweb-countdown" data-season="christmas"></div>
<script src="/aplicatieweb/embed/widget.js" async></script>
```

Attributes on the container (optional):
- `data-season`: `autumn` or `christmas` (default: `christmas`)
- `data-index`: numeric index of the event in that season (0 = first). If omitted, the widget shows the next upcoming event.

The widget fetches `/aplicatieweb/json.php?format=json` to read events.

Notes
-----
- This is a minimal prototype: styling is scoped via Shadow DOM and can be customized.
- For cross-origin embedding, host the widget JS on the same origin or enable CORS on the API.
