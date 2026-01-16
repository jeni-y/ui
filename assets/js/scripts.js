document.addEventListener('DOMContentLoaded', function () {

  const profileBtn = document.getElementById('profileBtn');
  const profileMenu = document.getElementById('profileMenu');
  const darkToggle = document.getElementById('darkToggle');

  if (profileBtn && profileMenu) {
    profileBtn.addEventListener('click', function (e) {
      e.stopPropagation();
      profileMenu.classList.toggle('active');
    });
  }

  if (darkToggle) {
    darkToggle.addEventListener('click', function (e) {
      e.stopPropagation();
      document.body.classList.toggle('dark');
      darkToggle.classList.toggle('active');
    });
  }

  document.addEventListener('click', function (e) {
    if (profileMenu && !e.target.closest('.profile-wrapper')) {
      profileMenu.classList.remove('active');
    }
  });

});
