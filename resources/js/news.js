/**
 * Comportements de la rubrique Actualités (sans dépendance).
 */
import { initCopyLinks, initReadingProgress, initReveal, initThemeToggle } from './shared/editorial';

const root = document.querySelector('[data-news-root]');

if (root) {
    initThemeToggle(root);

    // Menu mobile.
    const menu = document.getElementById('news-menu');
    if (menu instanceof HTMLDialogElement) {
        root.querySelectorAll('[data-news-menu-open]').forEach((button) => {
            button.addEventListener('click', () => menu.showModal());
        });
        menu.querySelectorAll('[data-news-menu-close]').forEach((button) => button.addEventListener('click', () => menu.close()));
        menu.addEventListener('click', (event) => {
            if (event.target === menu) {
                menu.close();
            }
        });
    }

    initCopyLinks(root);
    initReadingProgress(root.querySelector('[data-reading-progress]'), root.querySelector('[data-reading-article]'));
    initReveal(root, '.news-reveal');

    // Sommaire généré depuis les titres H2 / H3 du contenu.
    const toc = root.querySelector('[data-article-toc]');
    const body = root.querySelector('[data-article-body]');
    if (toc && body) {
        const headings = [...body.querySelectorAll('h2, h3')].filter((heading) => heading.textContent.trim() !== '');

        if (headings.length >= 2) {
            const slugify = (text) => text.toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '')
                .replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '') || 'section';
            const used = new Set();
            const list = toc.querySelector('[data-article-toc-list]');

            headings.forEach((heading) => {
                let id = heading.id || slugify(heading.textContent);
                while (used.has(id) || (document.getElementById(id) && document.getElementById(id) !== heading)) {
                    id += '-2';
                }
                used.add(id);
                heading.id = id;

                const item = document.createElement('li');
                const link = document.createElement('a');
                link.href = `#${id}`;
                link.textContent = heading.textContent.trim();
                link.className = heading.tagName === 'H3'
                    ? 'block border-l-2 border-transparent py-1 pl-6 text-sm text-[var(--news-muted)] hover:text-current'
                    : 'block border-l-2 border-transparent py-1.5 pl-3 text-sm text-[var(--news-muted)] hover:text-current';
                item.append(link);
                list.append(item);
            });

            toc.hidden = false;

            // Section en cours surlignée dans le sommaire.
            const links = [...list.querySelectorAll('a')];
            const setCurrent = (id) => links.forEach((link) => link.setAttribute('aria-current', String(link.hash === `#${id}`)));
            if ('IntersectionObserver' in window) {
                const observer = new IntersectionObserver((entries) => {
                    const visible = entries.filter((entry) => entry.isIntersecting).sort((a, b) => a.boundingClientRect.top - b.boundingClientRect.top);
                    if (visible[0]) {
                        setCurrent(visible[0].target.id);
                    }
                }, { rootMargin: '0px 0px -70% 0px' });
                headings.forEach((heading) => observer.observe(heading));
            }
        }
    }

    // « Charger plus » : ajoute la page suivante sans recharger (le lien reste utilisable sans JS).
    root.querySelectorAll('[data-load-more]').forEach((link) => {
        const list = root.querySelector('[data-news-list]');
        link.addEventListener('click', async (event) => {
            event.preventDefault();
            link.setAttribute('aria-busy', 'true');
            link.textContent = 'Chargement…';

            try {
                const url = new URL(link.href);
                url.searchParams.set('fragment', '1');
                const response = await fetch(url, { headers: { Accept: 'text/html' } });
                const html = await response.text();
                const template = document.createElement('template');
                template.innerHTML = html;
                const items = [...template.content.querySelectorAll('[data-news-item]')];
                items.forEach((item) => list.append(item));
                // Le focus passe au premier nouvel article pour les utilisateurs clavier.
                items[0]?.querySelector('a.news-card-link')?.focus({ preventScroll: true });

                const nextPage = response.headers.get('X-Next-Page');
                if (!nextPage) {
                    link.closest('[data-load-more-container]').remove();
                } else {
                    link.href = nextPage;
                    link.textContent = "Charger plus d'articles";
                    link.removeAttribute('aria-busy');
                }
            } catch (error) {
                // En cas d'échec, navigation classique vers la page suivante.
                window.location.href = link.href;
            }
        });
    });
}
