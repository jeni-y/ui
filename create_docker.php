<?php
session_start();
if (empty($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $projectName = $_POST['project_name'];
    $runtime     = $_POST['runtime'];

    // Handle file upload
    if (!empty($_FILES['code_file']['name'])) {
        $uploadDir = "uploads/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $filePath = $uploadDir . basename($_FILES['code_file']['name']);
        if (move_uploaded_file($_FILES['code_file']['tmp_name'], $filePath)) {
            echo "<h2>Thank you, {$_SESSION['username']}!</h2>";
            echo "<p>Your Free Tier PaaS environment has been configured:</p>";
            echo "<ul>
                    <li>Project: $projectName</li>
                    <li>Runtime: $runtime</li>
                    <li>Uploaded File: $filePath</li>
                  </ul>";
            exit;
        } else {
            echo "<p style='color:red;'>File upload failed.</p>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Free Tier Upload - CloudApp</title>
<style>
.form-container {
  max-width: 600px;
  margin: 40px auto;
  background: #fff;
  padding: 25px;
  border-radius: 8px;
  box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}
.form-container h2 {
  margin-bottom: 20px;
  color: #0072ff;
}
.form-group {
  margin-bottom: 15px;
}
.form-group label {
  display: block;
  font-weight: 600;
  margin-bottom: 5px;
}
.form-group input,
.form-group select {
  width: 100%;
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 6px;
}
button {
  padding: 12px 20px;
  background: #0072ff;
  color: #fff;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
}
button:hover {
  background: #005bb5;
}
</style>
</head>
<body>

<div class="form-container">
  <h2>Deploy Your Free Tier PaaS</h2>
  <form method="POST" enctype="multipart/form-data">
    <div class="form-group">
      <label for="project_name">Project Name</label>
      <input type="text" id="project_name" name="project_name" required>
    </div>

    <div class="form-group">
      <label for="runtime">Runtime / Framework</label>
      <select id="runtime" name="runtime" required>
        <option value="Node.js">Node.js</option>
        <option value="Python">Python</option>
        <option value="Java">Java</option>
        <option value="PHP">PHP</option>
      </select>
    </div>

    <div class="form-group">
      <label for="code_file">Upload Your Code</label>
      <input type="file" id="code_file" name="code_file" required>
    </div>

    <button type="submit">Deploy Free Tier</button>
  </form>
</div>

</body>
</html>