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

    if (registerModal.querySelector('.modal-register-form .form-error')) {
        registerModal.showModal();
    }
}
