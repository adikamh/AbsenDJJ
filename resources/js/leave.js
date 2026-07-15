document.addEventListener('DOMContentLoaded', () => {
    const modalAddLeave = document.getElementById('modal-add-leave');
    const btnOpenAddLeave = document.getElementById('open-add-leave-modal');
    const btnCloseAddLeave = document.getElementById('close-add-leave-modal');
    const btnCancelAddLeave = document.getElementById('cancel-add-leave-modal');

    const toggleAddLeaveModal = (show) => {
        if (modalAddLeave) {
            modalAddLeave.classList.toggle('is-open', show);
        }
    };

    btnOpenAddLeave?.addEventListener('click', () => toggleAddLeaveModal(true));
    btnCloseAddLeave?.addEventListener('click', () => toggleAddLeaveModal(false));
    btnCancelAddLeave?.addEventListener('click', () => toggleAddLeaveModal(false));

    modalAddLeave?.addEventListener('click', (e) => {
        if (e.target === modalAddLeave) {
            toggleAddLeaveModal(false);
        }
    });
});
