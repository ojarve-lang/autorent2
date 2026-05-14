<?php
include("../inc/db.php");

$message = "";
$messageType = "info";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["register"])) {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    if ($username === "" || $email === "" || $password === "") {
        $message = "Palun täida kõik väljad: kasutajanimi, e-post ja parool.";
        $messageType = "danger";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Palun sisesta kehtiv e-posti aadress.";
        $messageType = "danger";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $email, $hashed_password);

        if ($stmt->execute()) {
            $message = "Registreerimine õnnestus! Nüüd saad rentida autosid.";
            $messageType = "success";
        } else {
            if ($stmt->errno === 1062) {
                $message = "Kasutajanimi või e-post on juba kasutusel.";
            } else {
                $message = "Registreerimine ebaõnnestus. Proovi hiljem uuesti.";
            }
            $messageType = "danger";
        }
    }
}

$cars = $conn->query("SELECT * FROM cars ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="et">
<head>
    <meta charset="UTF-8">
    <title>Autorent Premium</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #0f1115;
            color: #f8fafc;
        }

        .navbar {
            background: rgba(17, 24, 39, 0.95) !important;
            border-bottom: 1px solid #374151;
        }

        .hero {
            background: linear-gradient(135deg, #111827, #1f2937);
            border-radius: 24px;
            padding: 70px 25px;
            text-align: center;
            margin-bottom: 40px;
            box-shadow: 0 25px 60px rgba(0,0,0,0.45);
        }

        .hero h1 {
            font-size: 48px;
            font-weight: 800;
        }

        .hero p {
            color: #cbd5e1;
            font-size: 18px;
        }

        .register-card, .car-card {
            background: #1f2937;
            border: 1px solid #374151;
            border-radius: 20px;
            color: #f8fafc;
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
        }

        .car-card {
            overflow: hidden;
            transition: 0.25s;
        }

        .car-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 25px 50px rgba(0,0,0,0.5);
        }

        .car-card img {
            height: 220px;
            object-fit: cover;
        }

        .price {
            color: #facc15;
            font-size: 22px;
            font-weight: 800;
        }

        .info-text {
            color: #cbd5e1;
            font-size: 14px;
        }

        .btn-gold {
            background: #facc15;
            color: #111827;
            border: none;
            font-weight: 700;
            border-radius: 12px;
        }

        .btn-gold:hover {
            background: #eab308;
            color: #111827;
        }

        .form-control {
            background: #111827;
            color: #f8fafc;
            border: 1px solid #4b5563;
        }

        .form-control::placeholder {
            color: #9ca3af;
        }

        footer {
            background: #111827;
            border-top: 1px solid #374151;
        }
    </style>
</head>

<body>

<nav class="navbar navbar-dark">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">🚗 Autorent Premium</a>
        <a class="btn btn-outline-light btn-sm" href="/admin/login.php">Admin</a>
    </div>
</nav>

<div class="container py-5">

    <section class="hero">
        <h1>Vali endale sobiv rendiauto</h1>
        <p>Kiire, mugav ja kaasaegne autorendi lahendus Bootstrapi ja Dockeriga.</p>
        <a href="#autod" class="btn btn-gold mt-3 px-4 py-2">Vaata autosid</a>
    </section>

    <?php if ($message): ?>
        <div class="alert alert-<?= htmlspecialchars($messageType) ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <div class="card register-card mb-5">
        <div class="card-body p-4">
            <h3 class="mb-3">Registreeru kliendiks</h3>

            <form method="POST" class="row g-3">
                <input type="hidden" name="register" value="1">

                <div class="col-md-4">
                    <input type="text" name="username" class="form-control" placeholder="Kasutajanimi" required>
                </div>

                <div class="col-md-4">
                    <input type="email" name="email" class="form-control" placeholder="E-post" required>
                </div>

                <div class="col-md-3">
                    <input type="password" name="password" class="form-control" placeholder="Parool" required>
                </div>

                <div class="col-md-2">
                    <button class="btn btn-gold w-100">Registreeru</button>
                </div>
            </form>
        </div>
    </div>

    <h2 id="autod" class="mb-4">Saadaval autod</h2>

    <div class="row g-4">
        <?php while ($car = $cars->fetch_assoc()): ?>
            <div class="col-md-4">
                <div class="card car-card h-100">

                    <?php if (!empty($car['image'])): ?>
                        <img src="<?= htmlspecialchars($car['image']) ?>" class="card-img-top">
                    <?php endif; ?>

                    <div class="card-body p-4">
                        <h5 class="card-title fw-bold">
                            <?= htmlspecialchars($car['mark'] . ' ' . $car['model']) ?>
                        </h5>

                        <p class="info-text mb-1">Aasta: <?= htmlspecialchars($car['year'] ?? '') ?></p>
                        <p class="info-text mb-1">Kütus: <?= htmlspecialchars($car['fuel'] ?? '') ?></p>
                        <p class="info-text mb-1">Käigukast: <?= htmlspecialchars($car['transmission'] ?? '') ?></p>

                        <p class="price mt-3 mb-3">
                            <?= number_format((float)$car['price'], 2) ?> € / päev
                        </p>

                        <a href="rent.php?car_id=<?= $car['id'] ?>" class="btn btn-gold w-100">Rendi auto</a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

</div>

<footer class="text-white text-center py-4 mt-5">
    <div class="container">
        <p class="mb-0 fw-bold">🍄 Küsimuste korral aitab Mario 👨‍🔧</p>
    </div>
</footer>

</body>
</html>
