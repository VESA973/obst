import './bootstrap';

const registerModal = document.getElementById('register-modal');

if (registerModal instanceof HTMLDialogElement) {
    document.querySelectorAll('[data-open-register]').forEach((trigger) => {
        trigger.addEventListener('click', () => {
            registerModal.showModal();
        });
    });

    document.querySelectorAll('[data-close-register]').forEach((trigger) => {
        trigger.addEventListener('click', () => {
            registerModal.close();
        });
    });

    registerModal.addEventListener('click', (event) => {
        if (event.target === registerModal) {
            registerModal.close();
        }
    });

    if (registerModal.querySelector('.modal-register-form .form-error, .modal-login-form .form-error')) {
        registerModal.showModal();
    }
}

document.querySelectorAll('[data-site-search]').forEach((search) => {
    const input = search.querySelector('[data-search-input]');
    const results = search.querySelector('[data-search-results]');

    if (!(input instanceof HTMLInputElement) || !(results instanceof HTMLElement)) {
        return;
    }

    let searchController;
    const escapeHtml = (value) => String(value).replace(/[&<>"']/g, (character) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;',
    })[character]);

    input.addEventListener('input', () => {
        const query = input.value.trim();

        if (query.length < 2) {
            results.hidden = true;
            results.innerHTML = '';
            return;
        }

        if (searchController) {
            searchController.abort();
        }

        searchController = new AbortController();

        fetch(`${input.dataset.searchUrl}?q=${encodeURIComponent(query)}`, {
            headers: { Accept: 'application/json' },
            signal: searchController.signal,
        })
            .then((response) => response.json())
            .then((items) => {
                results.innerHTML = items.length
                    ? items.map((item) => `<a href="${escapeHtml(item.url)}"><span>${escapeHtml(item.type)}</span>${escapeHtml(item.title)}</a>`).join('')
                    : '<p>Aucun resultat</p>';
                results.hidden = false;
            })
            .catch((error) => {
                if (error.name !== 'AbortError') {
                    results.hidden = true;
                }
            });
    });

    document.addEventListener('click', (event) => {
        if (!search.contains(event.target)) {
            results.hidden = true;
        }
    });
});
