document.addEventListener('DOMContentLoaded', () => {
    // Selfie Modal controls
    const selfieModal = document.getElementById('selfie-modal');
    const modalSelfieImg = document.getElementById('modal-selfie-img');
    const modalSelfieTitle = document.getElementById('modal-selfie-title');
    const closeSelfieModal = document.getElementById('close-selfie-modal');

    window.showImageModal = function(src, title) {
        if (!selfieModal || !modalSelfieImg || !modalSelfieTitle) return;
        modalSelfieImg.src = src;
        modalSelfieTitle.textContent = title;
        selfieModal.classList.add('is-open');
    };

    closeSelfieModal?.addEventListener('click', () => {
        selfieModal?.classList.remove('is-open');
    });

    selfieModal?.addEventListener('click', (e) => {
        if (e.target === selfieModal) {
            selfieModal.classList.remove('is-open');
        }
    });

    // Daily Photos Modal controls
    const photosModal = document.getElementById('daily-photos-modal');
    const imgMasuk = document.getElementById('modal-foto-masuk');
    const imgPulang = document.getElementById('modal-foto-pulang');
    const containerMasuk = document.getElementById('modal-masuk-container');
    const containerPulang = document.getElementById('modal-pulang-container');
    const modalPhotosTitle = document.getElementById('modal-photos-title');
    const closePhotosModal = document.getElementById('close-photos-modal');

    window.showSelfiePopup = function(fotoMasuk, fotoPulang, formattedDate) {
        if (!photosModal || !modalPhotosTitle || !imgMasuk || !imgPulang) return;

        modalPhotosTitle.textContent = `Foto Absensi - ${formattedDate}`;

        // Set check-in photo
        if (fotoMasuk) {
            imgMasuk.src = fotoMasuk;
            containerMasuk.style.display = 'flex';
        } else {
            imgMasuk.src = '';
            containerMasuk.style.display = 'none';
        }

        // Set check-out photo
        if (fotoPulang) {
            imgPulang.src = fotoPulang;
            containerPulang.style.display = 'flex';
        } else {
            imgPulang.src = '';
            containerPulang.style.display = 'none';
        }

        photosModal.classList.add('is-open');
    };

    closePhotosModal?.addEventListener('click', () => {
        photosModal?.classList.remove('is-open');
    });

    photosModal?.addEventListener('click', (e) => {
        if (e.target === photosModal) {
            photosModal.classList.remove('is-open');
        }
    });
});
