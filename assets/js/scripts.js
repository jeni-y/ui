document.addEventListener("DOMContentLoaded", () => {
  const profileBtn = document.querySelector(".profile-btn");
  const profileMenu = document.getElementById("profileMenu");
  const darkToggle = document.getElementById("darkToggle");

  // Profile dropdown toggle
  if (profileBtn && profileMenu) {
    profileBtn.addEventListener("click", (e) => {
      e.stopPropagation();
      profileMenu.classList.toggle("active");
    });

    // Close dropdown if clicking outside
    document.addEventListener("click", () => {
      profileMenu.classList.remove("active");
    });
  }

  // Dark mode toggle
  if (darkToggle) {
    if (localStorage.getItem("darkMode") === "true") {
      document.body.classList.add("dark");
      darkToggle.classList.add("active");
    }

    darkToggle.addEventListener("click", (e) => {
      e.stopPropagation();
      document.body.classList.toggle("dark");
      darkToggle.classList.toggle("active");
      localStorage.setItem("darkMode", document.body.classList.contains("dark"));
    });
  }
});