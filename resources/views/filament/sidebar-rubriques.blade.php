{{--
    Rubriques repliables dans le menu de l'administration.
    Filament affiche toujours les sous-menus d'un élément sans lien : ce script ajoute
    l'ouverture / fermeture au clic, mémorisée dans le navigateur. Une rubrique contenant
    la page en cours reste ouverte.
--}}
<style>
    .fi-rubrique-toggle { cursor: pointer; }
    .fi-rubrique-chevron { margin-inline-start: auto; width: 1rem; height: 1rem; transition: transform .2s ease; opacity: .6; }
    .fi-rubrique-open > .fi-rubrique-toggle .fi-rubrique-chevron { transform: rotate(180deg); }
    @media (prefers-reduced-motion: reduce) { .fi-rubrique-chevron { transition: none; } }
</style>
<script>
    (() => {
        const STORAGE_KEY = 'admin-rubriques-fermees';

        const readClosed = () => {
            try {
                return JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');
            } catch (error) {
                return [];
            }
        };

        const writeClosed = (names) => {
            try {
                localStorage.setItem(STORAGE_KEY, JSON.stringify(names));
            } catch (error) {
                // Stockage indisponible : l'état ne sera simplement pas mémorisé.
            }
        };

        const setup = () => {
            // Éléments du menu sans lien et avec un sous-menu : ce sont les rubriques.
            document.querySelectorAll('.fi-sidebar-nav li.fi-sidebar-item:not(.fi-sidebar-item-has-url)').forEach((item) => {
                const button = item.querySelector(':scope > .fi-sidebar-item-btn');
                const list = item.querySelector(':scope > .fi-sidebar-sub-group-items');

                if (!button || !list) {
                    return;
                }

                const name = button.querySelector('.fi-sidebar-item-label')?.textContent.trim() ?? '';
                const apply = (open) => {
                    list.hidden = !open;
                    button.setAttribute('aria-expanded', String(open));
                    item.classList.toggle('fi-rubrique-open', open);
                };

                if (!button.dataset.rubriqueReady) {
                    button.dataset.rubriqueReady = '1';
                    button.classList.add('fi-rubrique-toggle');
                    button.setAttribute('role', 'button');
                    button.setAttribute('tabindex', '0');
                    button.insertAdjacentHTML('beforeend', '<svg class="fi-rubrique-chevron" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.2 7.2a.75.75 0 0 1 1.06 0L10 10.94l3.74-3.74a.75.75 0 1 1 1.06 1.06l-4.27 4.27a.75.75 0 0 1-1.06 0L5.2 8.26a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/></svg>');

                    const toggle = () => {
                        const open = list.hidden;
                        apply(open);
                        const closed = readClosed().filter((closedName) => closedName !== name);
                        writeClosed(open ? closed : [...closed, name]);
                    };

                    button.addEventListener('click', (event) => {
                        event.preventDefault();
                        toggle();
                    });
                    button.addEventListener('keydown', (event) => {
                        if (event.key === 'Enter' || event.key === ' ') {
                            event.preventDefault();
                            toggle();
                        }
                    });
                }

                apply(item.classList.contains('fi-sidebar-item-has-active-child-items') || !readClosed().includes(name));
            });
        };

        setup();
        document.addEventListener('livewire:navigated', setup);
        // Le menu est un composant Livewire : on réapplique après chaque rafraîchissement.
        document.addEventListener('livewire:init', () => {
            window.Livewire.hook('morphed', () => setup());
        });
    })();
</script>
