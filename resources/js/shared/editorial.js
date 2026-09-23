/**
 * Comportements communs aux sections éditoriales (Actualités, Santé de la femme).
 */

const reducedMotion = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches;

/**
 * Mode sombre limité au conteneur `root`, mémorisé dans le navigateur (clé commune aux sections).
 */
export function initThemeToggle(root, storageKey = 'news-theme') {
    const buttons = root.querySelectorAll('[data-theme-toggle]');
    const sync = () => {
        const dark = root.classList.contains('dark');
        buttons.forEach((button) => {
            button.setAttribute('aria-pressed', String(dark));
            const label = button.querySelector('.sr-only');
            if (label) {
                label.textContent = dark ? 'Activer le mode clair' : 'Activer le mode sombre';
            }
        });
    };

    buttons.forEach((button) => {
        button.addEventListener('click', () => {
            const dark = root.classList.toggle('dark');
            try {
                localStorage.setItem(storageKey, dark ? 'dark' : 'light');
            } catch (error) {
                // Stockage indisponible (navigation privée) : le choix vaut pour la page seulement.
            }
            sync();
        });
    });
    sync();
}

/**
 * Boutons « Copier le lien » (composant x-news.share).
 */
export function initCopyLinks(root) {
    root.querySelectorAll('[data-copy-link]').forEach((button) => {
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
}

/**
 * Barre de progression de lecture : `bar` avance selon le défilement dans `article`.
 */
export function initReadingProgress(bar, article) {
    if (!bar || !article) {
        return;
    }

    let scheduled = false;
    const update = () => {
        const rect = article.getBoundingClientRect();
        const total = rect.height - window.innerHeight;
        const ratio = total > 0 ? Math.min(Math.max(-rect.top / total, 0), 1) : 1;
        bar.style.setProperty('--progress', ratio.toFixed(4));
        scheduled = false;
    };

    window.addEventListener('scroll', () => {
        if (!scheduled) {
            scheduled = true;
            requestAnimationFrame(update);
        }
    }, { passive: true });
    update();
}

/**
 * Apparition au défilement des éléments `selector` (ajoute la classe is-visible).
 */
export function initReveal(root, selector) {
    const elements = root.querySelectorAll(selector);

    if (reducedMotion() || !('IntersectionObserver' in window)) {
        elements.forEach((element) => element.classList.add('is-visible'));
        return;
    }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, { rootMargin: '0px 0px -10% 0px' });

    elements.forEach((element) => observer.observe(element));
}
