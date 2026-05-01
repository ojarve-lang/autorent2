<?php
include("../inc/db.php");

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["register"])) {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    if ($username === "" || $password === "") {
        $message = "Kasutajanimi ja parool on kohustuslikud.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $username, $hashed_password);

        if ($stmt->execute()) {
            $message = "Registreerimine õnnestus!";
        } else {
            $message = "See kasutajanimi on juba olemas.";
        }
    }
}

$cars = $conn->query("SELECT * FROM cars ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="et">
<head>
    <meta charset="UTF-8">
    <title>Autorent</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<nav class="navbar navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">🚗 Autorent</a>
        <a class="btn btn-outline-light btn-sm" href="/admin/login.php">Admin</a>
    </div>
</nav>

<div class="container py-5">

    <h1 class="mb-4 text-center">Vali endale sobiv rendiauto</h1>

    <?php if ($message): ?>
        <div class="alert alert-info">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm mb-5">
        <div class="card-body">
            <h3>Registreeru kliendiks</h3>

            <form method="POST" class="row g-3">
                <input type="hidden" name="register" value="1">

                <div class="col-md-5">
                    <input type="text" name="username" class="form-control" placeholder="Kasutajanimi" required>
                </div>

                <div class="col-md-5">
                    <input type="password" name="password" class="form-control" placeholder="Parool" required>
                </div>

                <div class="col-md-2">
                    <button class="btn btn-success w-100">Registreeru</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-4">
        <?php while ($car = $cars->fetch_assoc()): ?>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm">

                    <?php if (!empty($car['image'])): ?>
                        <img src="<?= htmlspecialchars($car['image']) ?>" class="card-img-top" style="height:220px; object-fit:cover;">
                    <?php endif; ?>

                    <div class="card-body">
                        <h5 class="card-title">
                            <?= htmlspecialchars($car['mark'] . ' ' . $car['model']) ?>
                        </h5>

                        <p class="mb-1">Aasta: <?= htmlspecialchars($car['year'] ?? '') ?></p>
                        <p class="mb-1">Kütus: <?= htmlspecialchars($car['fuel'] ?? '') ?></p>
                        <p class="mb-1">Käigukast: <?= htmlspecialchars($car['transmission'] ?? '') ?></p>
                        <p class="fw-bold mt-2"><?= number_format((float)$car['price'], 2) ?> € / päev</p>

                        <a href="rent.php?car_id=<?= $car['id'] ?>" class="btn btn-primary">Rendi</a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

</div>
<footer class="bg-dark text-white text-center py-3 mt-5">
    <div class="container">
<p class="mb-0 fw-bold">
    🍄 Küsimuste korral aitab Mario 👨‍🔧
</p>
    </div>
</footer>
</body>
</html>
