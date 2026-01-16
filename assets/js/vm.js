document.addEventListener('DOMContentLoaded', () => {

    const modal = document.getElementById('launchModal');
    const modalText = document.getElementById('modalText');
    const launchBtn = document.getElementById('launchBtn');

    if (!window.VM_DATA.hasKey) {
        launchBtn.disabled = true;
    }

    launchBtn.addEventListener('click', () => {
        modal.style.display = 'flex';
        modalText.textContent = 'Launching VM... ⏳';
    });

    if (window.VM_DATA.launchResult) {
        modal.style.display = 'flex';
        modalText.innerHTML =
            window.VM_DATA.launchResult === 'success'
            ? '✅ VM Launched Successfully'
            : '❌ VM Launch Failed';

        setTimeout(() => {
            modal.style.display = 'none';
            location.reload();
        }, 3000);
    }
});
