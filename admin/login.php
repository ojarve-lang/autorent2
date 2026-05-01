<?php
session_start();
require_once('../inc/db.php');

$error = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user && $username === "admin" && password_verify($password, $user['password'])) {
    session_regenerate_id(true);
    $_SESSION['admin'] = true;
        $_SESSION['admin_name'] = $username;
        header("Location: index.php");
        exit;
    } else {
        $error = "Vale kasutajanimi või parool.";
    }
}
?>

<!DOCTYPE html>
<html lang="et">
<head>
    <meta charset="UTF-8">
    <title>Admin login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-4">

            <div class="card shadow">
                <div class="card-body">
                    <h2 class="mb-4 text-center">Admin login</h2>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Kasutajanimi</label>
                            <input name="username" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Parool</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>

                        <button class="btn btn-dark w-100">Logi sisse</button>
                    </form>

                    <a href="../public/index.php" class="d-block text-center mt-3">Tagasi avalehele</a>
                </div>
            </div>

        </div>
    </div>
</div>

</body>
</html>
