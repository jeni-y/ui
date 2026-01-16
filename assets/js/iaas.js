document.addEventListener('DOMContentLoaded', () => {

    const overlay = document.getElementById('overlay');
    const modals = document.querySelectorAll('.modal');

    /* ---------- OPEN MODAL ---------- */
    document.querySelectorAll('.price-card').forEach(card => {
        card.addEventListener('click', () => {
            const modalId = card.dataset.modal;
            document.getElementById(modalId).style.display = 'block';
            overlay.style.display = 'block';
        });
    });

    /* ---------- CLOSE MODAL (X BUTTON) ---------- */
    document.querySelectorAll('.close').forEach(btn => {
        btn.addEventListener('click', closeModal);
    });

    /* ---------- CLOSE MODAL (OVERLAY) ---------- */
    overlay.addEventListener('click', closeModal);

    function closeModal() {
        overlay.style.display = 'none';
        modals.forEach(m => m.style.display = 'none');
    }

    /* ---------- START FREE BUTTON ---------- */
    document.querySelectorAll('[data-action="start-free"]').forEach(btn => {
        btn.addEventListener('click', () => {
            window.location.href = 'vm.php';
        });
    });

    /* ---------- OS SELECTION ---------- */
    document.querySelectorAll('.os-box').forEach(box => {
        box.addEventListener('click', () => {

            document.querySelectorAll('.os-box')
                .forEach(b => b.classList.remove('active'));

            box.classList.add('active');

            if (box.dataset.os === 'ubuntu') {
                window.location.href = 'create_vm_ubuntu.php';
            }
        });
    });

});
