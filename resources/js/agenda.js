/**
 * Agenda : filtres (recherche, type, commune, mois), bascule liste / calendrier, calendrier mensuel
 * navigable au clavier, compte à rebours, copie du lien, carte Leaflet (chargée à la demande),
 * carrousel « Autres événements » et visionneuse des photos.
 */
import GLightbox from 'glightbox';
import 'glightbox/dist/css/glightbox.min.css';
import '../css/home.css';
import '../css/association.css';
import '../css/agenda.css';
import { initCarousels } from './shared/carousels';
import { initHeader, initReveal } from './shared/site-sections';

initHeader();
initReveal();
initCarousels();

const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const normalize = (text) => text.normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase().trim();

/* ---------- Filtres et vue liste ---------- */

const filters = document.querySelector('[data-agenda-filters]');
const list = document.querySelector('[data-agenda-list]');
const calendarEl = document.querySelector('[data-agenda-calendar]');
const empty = document.querySelector('[data-agenda-empty]');
const items = [...document.querySelectorAll('[data-agenda-list] [data-agenda-item]')];
const state = { search: '', type: '', commune: '', month: '', view: 'list' };
let renderCalendar = () => {};

const matches = (item) => (!state.type || item.dataset.type === state.type)
    && (!state.commune || item.dataset.commune === state.commune)
    && (!state.month || item.dataset.month === state.month)
    && (!state.search || item.dataset.search.includes(state.search));

const applyFilters = () => {
    let visible = 0;
    items.forEach((item) => {
        const show = matches(item);
        item.hidden = !show;
        visible += show ? 1 : 0;
    });

    // Un mois sans événement visible disparaît avec son titre.
    document.querySelectorAll('[data-agenda-month]').forEach((month) => {
        month.hidden = !month.querySelector('[data-agenda-item]:not([hidden])');
    });

    // Nombre de filtres actifs, affiché sur le bouton « Filtres » (mobile).
    const active = ['type', 'commune', 'month'].filter((key) => state[key]).length;
    const badge = filters?.querySelector('[data-filters-active]');
    if (badge) {
        badge.hidden = active === 0;
        badge.textContent = active;
    }

    const count = filters?.querySelector('[data-results-count]');
    if (count) {
        count.textContent = `${visible} événement${visible > 1 ? 's' : ''}`;
    }

    if (empty) {
        empty.hidden = visible > 0;
    }
    if (list) {
        list.hidden = state.view !== 'list' || visible === 0;
    }
    if (calendarEl) {
        calendarEl.hidden = state.view !== 'calendar';
        renderCalendar();
    }
};

if (filters && items.length) {
    filters.hidden = false;

    let debounce;
    filters.querySelector('[data-filter-search]')?.addEventListener('input', (event) => {
        clearTimeout(debounce);
        debounce = setTimeout(() => {
            state.search = normalize(event.target.value);
            applyFilters();
        }, 150);
    });

    filters.querySelectorAll('[data-filter-group]').forEach((group) => {
        group.addEventListener('click', (event) => {
            const chip = event.target.closest('[data-value]');
            if (!chip) {
                return;
            }
            group.querySelectorAll('[data-value]').forEach((other) => other.setAttribute('aria-pressed', String(other === chip)));
            state[group.dataset.filterGroup] = chip.dataset.value;
            applyFilters();
        });
    });

    const toggle = filters.querySelector('[data-filters-toggle]');
    toggle?.addEventListener('click', () => {
        const open = filters.classList.toggle('is-open');
        toggle.setAttribute('aria-expanded', String(open));
    });

    filters.querySelectorAll('[data-view]').forEach((button) => {
        button.addEventListener('click', () => {
            state.view = button.dataset.view;
            filters.querySelectorAll('[data-view]').forEach((other) => other.setAttribute('aria-pressed', String(other === button)));
            applyFilters();
        });
    });

    document.querySelectorAll('[data-filter-reset]').forEach((button) => button.addEventListener('click', () => {
        Object.assign(state, { search: '', type: '', commune: '', month: '' });
        filters.querySelector('[data-filter-search]').value = '';
        filters.querySelectorAll('[data-filter-group] [data-value]').forEach((chip) => chip.setAttribute('aria-pressed', String(chip.dataset.value === '')));
        applyFilters();
    }));

    applyFilters();
}

/* ---------- Calendrier mensuel (navigable au clavier) ---------- */

if (calendarEl && items.length) {
    const body = calendarEl.querySelector('[data-calendar-body]');
    const title = calendarEl.querySelector('[data-calendar-title]');
    const dayPanel = calendarEl.querySelector('[data-calendar-day]');
    const monthFormat = new Intl.DateTimeFormat('fr-FR', { month: 'long', year: 'numeric', timeZone: 'UTC' });
    const dayFormat = new Intl.DateTimeFormat('fr-FR', { weekday: 'long', day: 'numeric', month: 'long', timeZone: 'UTC' });

    // Événements par jour (AAAA-MM-JJ), à partir des cartes de la liste (tous les créneaux).
    const eventsByDay = new Map();
    items.forEach((item) => {
        (item.dataset.days || '').split(',').filter(Boolean).forEach((day) => {
            eventsByDay.set(day, [...(eventsByDay.get(day) ?? []), item]);
        });
    });

    const firstDay = [...eventsByDay.keys()].sort()[0] ?? new Date().toISOString().slice(0, 10);
    let current = new Date(`${firstDay.slice(0, 7)}-01T00:00:00Z`);
    let focused = firstDay;

    const iso = (date) => date.toISOString().slice(0, 10);
    const visibleEvents = (day) => (eventsByDay.get(day) ?? []).filter(matches);

    const showDay = (day) => {
        const events = visibleEvents(day);
        const date = new Date(`${day}T00:00:00Z`);
        dayPanel.innerHTML = '';
        const heading = document.createElement('p');
        heading.className = 'agenda-calendar-day-title';
        heading.textContent = dayFormat.format(date);
        dayPanel.append(heading);

        if (!events.length) {
            const none = document.createElement('p');
            none.textContent = 'Aucun événement ce jour-là.';
            dayPanel.append(none);
            return;
        }

        const ul = document.createElement('ul');
        events.forEach((item) => {
            const li = document.createElement('li');
            const link = document.createElement('a');
            link.href = item.querySelector('.home-card-link').href;
            link.textContent = item.querySelector('.home-card-link').textContent;
            li.style.setProperty('--type-color', item.style.getPropertyValue('--type-color'));
            li.append(link);
            ul.append(li);
        });
        dayPanel.append(ul);
    };

    renderCalendar = () => {
        if (calendarEl.hidden) {
            return;
        }

        title.textContent = monthFormat.format(current).replace(/^./, (letter) => letter.toUpperCase());
        body.innerHTML = '';

        // Premier lundi affiché (la semaine commence le lundi).
        const start = new Date(current);
        start.setUTCDate(1 - ((current.getUTCDay() + 6) % 7));
        if (focused.slice(0, 7) !== iso(current).slice(0, 7)) {
            focused = iso(current);
        }

        for (let week = 0; week < 6; week++) {
            const row = document.createElement('tr');
            for (let dayIndex = 0; dayIndex < 7; dayIndex++) {
                const date = new Date(start);
                date.setUTCDate(start.getUTCDate() + week * 7 + dayIndex);
                const day = iso(date);
                const events = visibleEvents(day);
                const cell = document.createElement('td');
                const button = document.createElement('button');
                button.type = 'button';
                button.dataset.day = day;
                button.tabIndex = day === focused ? 0 : -1;
                button.className = 'agenda-calendar-cell';
                button.classList.toggle('is-outside', date.getUTCMonth() !== current.getUTCMonth());
                button.classList.toggle('is-today', day === new Date().toISOString().slice(0, 10));
                button.setAttribute('aria-label', `${dayFormat.format(date)}${events.length ? `, ${events.length} événement${events.length > 1 ? 's' : ''}` : ''}`);
                button.innerHTML = `<span>${date.getUTCDate()}</span>`;
                if (events.length) {
                    const dots = document.createElement('span');
                    dots.className = 'agenda-calendar-dots';
                    events.slice(0, 3).forEach((item) => {
                        const dot = document.createElement('i');
                        dot.style.setProperty('--type-color', item.style.getPropertyValue('--type-color'));
                        dots.append(dot);
                    });
                    button.append(dots);
                    button.classList.add('has-events');
                }
                cell.append(button);
                row.append(cell);
            }
            body.append(row);
        }
        showDay(focused);
    };

    const moveTo = (day, focus = true) => {
        const date = new Date(`${day}T00:00:00Z`);
        if (date.getUTCMonth() !== current.getUTCMonth() || date.getUTCFullYear() !== current.getUTCFullYear()) {
            current = new Date(`${day.slice(0, 7)}-01T00:00:00Z`);
        }
        focused = day;
        renderCalendar();
        if (focus) {
            body.querySelector(`[data-day="${day}"]`)?.focus();
        }
    };

    const shiftMonth = (delta) => {
        current.setUTCMonth(current.getUTCMonth() + delta);
        focused = iso(current);
        renderCalendar();
    };

    calendarEl.querySelector('[data-calendar-prev]').addEventListener('click', () => shiftMonth(-1));
    calendarEl.querySelector('[data-calendar-next]').addEventListener('click', () => shiftMonth(1));

    body.addEventListener('click', (event) => {
        const button = event.target.closest('[data-day]');
        if (button) {
            moveTo(button.dataset.day);
        }
    });

    // Clavier : flèches (jour / semaine), Début / Fin (semaine), Page préc. / suiv. (mois).
    body.addEventListener('keydown', (event) => {
        const date = new Date(`${focused}T00:00:00Z`);
        const offsets = { ArrowLeft: -1, ArrowRight: 1, ArrowUp: -7, ArrowDown: 7 };
        if (event.key in offsets) {
            date.setUTCDate(date.getUTCDate() + offsets[event.key]);
        } else if (event.key === 'Home') {
            date.setUTCDate(date.getUTCDate() - ((date.getUTCDay() + 6) % 7));
        } else if (event.key === 'End') {
            date.setUTCDate(date.getUTCDate() + (6 - ((date.getUTCDay() + 6) % 7)));
        } else if (event.key === 'PageUp' || event.key === 'PageDown') {
            date.setUTCMonth(date.getUTCMonth() + (event.key === 'PageUp' ? -1 : 1));
        } else {
            return;
        }
        event.preventDefault();
        moveTo(iso(date));
    });
}

/* ---------- Compte à rebours ---------- */

document.querySelectorAll('[data-countdown]').forEach((countdown) => {
    const target = new Date(countdown.dataset.countdown).getTime();
    const update = () => {
        const remaining = Math.max(0, target - Date.now());
        const minutes = Math.floor(remaining / 60000);
        countdown.querySelector('[data-unit="days"]').textContent = Math.floor(minutes / 1440);
        countdown.querySelector('[data-unit="hours"]').textContent = Math.floor((minutes % 1440) / 60);
        countdown.querySelector('[data-unit="minutes"]').textContent = minutes % 60;
    };
    update();
    setInterval(update, 60000);
});

/* ---------- Copier le lien ---------- */

document.querySelectorAll('[data-copy-link]').forEach((button) => {
    button.addEventListener('click', async () => {
        const label = button.querySelector('[data-copy-label]');
        try {
            await navigator.clipboard.writeText(button.dataset.copyLink);
            label.textContent = 'Lien copié !';
        } catch (error) {
            window.prompt('Copiez le lien :', button.dataset.copyLink);
        }
        setTimeout(() => {
            label.textContent = 'Copier le lien';
        }, 2500);
    });
});

/* ---------- Carte Leaflet (chargée seulement quand elle approche de l'écran) ---------- */

const mapEl = document.querySelector('[data-agenda-map]');
if (mapEl) {
    const escapeHtml = (text) => String(text ?? '').replace(/[&<>"']/g, (char) => `&#${char.charCodeAt(0)};`);

    const loadMap = async () => {
        const [{ default: L }] = await Promise.all([import('leaflet'), import('leaflet/dist/leaflet.css')]);
        mapEl.innerHTML = '';
        const map = L.map(mapEl, { scrollWheelZoom: false, zoomAnimation: !reducedMotion });
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 18,
            attribution: '&copy; contributeurs <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
        }).addTo(map);

        // Carte de l'agenda : un marqueur par événement, avec la miniature de l'affiche.
        if (mapEl.dataset.points) {
            const points = JSON.parse(mapEl.dataset.points);
            const markers = points.map((point) => {
                const thumb = point.thumb
                    ? `<img src="${escapeHtml(point.thumb)}" alt="" style="object-position: ${escapeHtml(point.position)}">`
                    : '';
                const icon = L.divIcon({ className: 'agenda-map-pin', html: thumb, iconSize: [52, 52], iconAnchor: [26, 52], popupAnchor: [0, -50] });
                return L.marker([point.lat, point.lng], { icon, title: point.title, alt: point.title })
                    .bindPopup(`<div class="agenda-map-popup">${thumb}<strong>${escapeHtml(point.title)}</strong>`
                        + (point.date ? `<span>${escapeHtml(point.date)}</span>` : '')
                        + (point.place ? `<span>${escapeHtml(point.place)}</span>` : '')
                        + `<a href="${escapeHtml(point.url)}">Voir l'événement</a></div>`)
                    .addTo(map);
            });
            map.fitBounds(L.featureGroup(markers).getBounds().pad(0.3), { maxZoom: 13 });
            return;
        }

        const position = [Number(mapEl.dataset.lat), Number(mapEl.dataset.lng)];
        map.setView(position, mapEl.hasAttribute('data-exact') ? 15 : 11);
        // Adresse précise : marqueur ; sinon cercle, la position n'étant que celle de la commune.
        const shape = mapEl.hasAttribute('data-exact')
            ? L.marker(position, { icon: L.divIcon({ className: 'agenda-map-pin agenda-map-pin--dot', iconSize: [22, 22], iconAnchor: [11, 22] }) })
            : L.circle(position, { radius: 1500, color: '#3a00f5', fillColor: '#c62bc8', fillOpacity: 0.25 });
        shape.addTo(map).bindTooltip(mapEl.dataset.label, { permanent: true, direction: 'top' });
    };

    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            if (entries.some((entry) => entry.isIntersecting)) {
                observer.disconnect();
                loadMap();
            }
        }, { rootMargin: '200px' });
        observer.observe(mapEl);
    } else {
        loadMap();
    }
}

/* ---------- Visionneuse des photos de l'événement ---------- */

if (document.querySelector('.glightbox')) {
    GLightbox({ selector: '.glightbox', touchNavigation: true, openEffect: reducedMotion ? 'none' : 'fade', closeEffect: reducedMotion ? 'none' : 'fade' });
}
