document.addEventListener("DOMContentLoaded", function () {
  const deployForm = document.getElementById("deployForm");
  const resultDiv = document.getElementById("result");
  const privateYes = document.getElementById("private_yes");
  const privateNo = document.getElementById("private_no");
  const tokenField = document.getElementById("token_field");
  const dbYes = document.getElementById("db_yes");
  const dbNo = document.getElementById("db_no");
  const dbOptions = document.getElementById("database_options");

  // Toggle GitHub token field
  function toggleTokenField() {
    tokenField.style.display = privateYes.checked ? "block" : "none";
  }
  privateYes.addEventListener("change", toggleTokenField);
  privateNo.addEventListener("change", toggleTokenField);

  // Toggle DB options field
  function toggleDBOptions() {
    dbOptions.style.display = dbYes.checked ? "block" : "none";
  }
  dbYes.addEventListener("change", toggleDBOptions);
  dbNo.addEventListener("change", toggleDBOptions);

  // Handle form submission
  deployForm.addEventListener("submit", function (e) {
    e.preventDefault();
    resultDiv.innerHTML = `<p>🚀 Triggering deployment, please wait...</p>`;

    const formData = new FormData(deployForm);

    fetch("/backend/deploy/deploy.php", {
      method: "POST",
      body: formData
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.status === "success" && data.status_page) {
          resultDiv.innerHTML = `
            <p>✅ Deployment workflow triggered!</p>
            <p>Redirecting to deployment status page...</p>
            <iframe src="${data.status_page}" style="width:100%; height:600px; border:1px solid #ccc;"></iframe>
          `;
        } else {
          resultDiv.innerHTML = `<p style="color:red;">❌ Error: ${data.message}</p>`;
        }
      })
      .catch((err) => {
        console.error(err);
        resultDiv.innerHTML = `<p style="color:red;">❌ Unexpected error occurred</p>`;
      });
  });
});