<?php
session_start();
require_once('../inc/db.php');

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

$message = "";

if (isset($_GET["logout"])) {
    session_destroy();
    header("Location: login.php");
    exit;
}

if (isset($_GET["delete_car"])) {
    $id = (int)$_GET["delete_car"];

    $stmt = $conn->prepare("DELETE FROM cars WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $message = "Auto kustutatud.";
    } else {
        $message = "Autot ei saanud kustutada. Võib-olla on sellega seotud broneering.";
    }
}

if (isset($_GET["delete_reservation"])) {
    $id = (int)$_GET["delete_reservation"];

    $stmt = $conn->prepare("DELETE FROM reservations WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $message = "Broneering kustutatud.";
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_car"])) {
    $mark = trim($_POST["mark"]);
    $model = trim($_POST["model"]);
    $engine = trim($_POST["engine"]);
    $fuel = trim($_POST["fuel"]);
    $price = (float)$_POST["price"];
    $image = trim($_POST["image"]);
    $year = (int)$_POST["year"];
    $transmission = trim($_POST["transmission"]);
    $seats = (int)$_POST["seats"];
    $description = trim($_POST["description"]);
    $status = trim($_POST["status"]);

    $stmt = $conn->prepare("
        INSERT INTO cars 
        (mark, model, engine, fuel, price, image, year, transmission, seats, description, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "ssssdsiisss",
        $mark,
        $model,
        $engine,
        $fuel,
        $price,
        $image,
        $year,
        $transmission,
        $seats,
        $description,
        $status
    );

    if ($stmt->execute()) {
        $message = "Auto lisatud.";
    } else {
        $message = "Auto lisamine ebaõnnestus.";
    }
}

$cars = $conn->query("SELECT * FROM cars ORDER BY id DESC");

$reservations = $conn->query("
    SELECT r.*, c.mark, c.model
    FROM reservations r
    LEFT JOIN cars c ON r.car_id = c.id
    ORDER BY r.id DESC
");
?>

<!DOCTYPE html>
<html lang="et">
<head>
    <meta charset="UTF-8">
    <title>Admin paneel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<nav class="navbar navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">Admin paneel</a>
        <div>
            <a href="../public/index.php" class="btn btn-outline-light btn-sm">Avaleht</a>
            <a href="?logout=1" class="btn btn-danger btn-sm">Logi välja</a>
        </div>
    </div>
</nav>

<div class="container py-5">

    <?php if ($message): ?>
        <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="card shadow-sm mb-5">
        <div class="card-body">
            <h2>Lisa uus auto</h2>

            <form method="POST" class="row g-3">
                <input type="hidden" name="add_car" value="1">

                <div class="col-md-3">
                    <input name="mark" class="form-control" placeholder="Mark" required>
                </div>

                <div class="col-md-3">
                    <input name="model" class="form-control" placeholder="Mudel" required>
                </div>

                <div class="col-md-2">
                    <input name="year" type="number" class="form-control" placeholder="Aasta">
                </div>

                <div class="col-md-2">
                    <input name="price" type="number" step="0.01" class="form-control" placeholder="Hind/päev">
                </div>

                <div class="col-md-2">
                    <input name="seats" type="number" class="form-control" placeholder="Kohti">
                </div>

                <div class="col-md-3">
                    <input name="engine" class="form-control" placeholder="Mootor">
                </div>

                <div class="col-md-3">
                    <input name="fuel" class="form-control" placeholder="Kütus">
                </div>

                <div class="col-md-3">
                    <input name="transmission" class="form-control" placeholder="Käigukast">
                </div>

                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="vaba">vaba</option>
                        <option value="hoolduses">hoolduses</option>
                    </select>
                </div>

                <div class="col-md-12">
                    <input name="image" class="form-control" placeholder="Pildi URL või failitee">
                </div>

                <div class="col-md-12">
                    <textarea name="description" class="form-control" placeholder="Kirjeldus"></textarea>
                </div>

                <div class="col-md-12">
                    <button class="btn btn-success">Lisa auto</button>
                </div>
            </form>
        </div>
    </div>

    <h2>Autod</h2>

    <div class="table-responsive mb-5">
        <table class="table table-bordered table-striped align-middle">
            <tr>
                <th>ID</th>
                <th>Auto</th>
                <th>Aasta</th>
                <th>Kütus</th>
                <th>Hind</th>
                <th>Staatus</th>
                <th>Tegevus</th>
            </tr>

            <?php while ($car = $cars->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($car["id"]) ?></td>
                    <td><?= htmlspecialchars($car["mark"] . " " . $car["model"]) ?></td>
                    <td><?= htmlspecialchars($car["year"] ?? "") ?></td>
                    <td><?= htmlspecialchars($car["fuel"] ?? "") ?></td>
                    <td><?= htmlspecialchars($car["price"] ?? "") ?> €</td>
                    <td><?= htmlspecialchars($car["status"] ?? "") ?></td>
                    <td>
                        <a href="?delete_car=<?= $car["id"] ?>" 
                           class="btn btn-danger btn-sm"
                           onclick="return confirm('Kas kustutan auto?')">
                           Kustuta
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <h2>Broneeringud</h2>

    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <tr>
                <th>ID</th>
                <th>Auto</th>
                <th>Nimi</th>
                <th>Email</th>
                <th>Algus</th>
                <th>Lõpp</th>
                <th>Summa</th>
                <th>Tegevus</th>
            </tr>

            <?php while ($row = $reservations->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row["id"]) ?></td>
                    <td><?= htmlspecialchars(($row["mark"] ?? "") . " " . ($row["model"] ?? "")) ?></td>
                    <td><?= htmlspecialchars($row["customer_name"] ?? "") ?></td>
                    <td><?= htmlspecialchars($row["customer_email"] ?? "") ?></td>
                    <td><?= htmlspecialchars($row["start_date"] ?? "") ?></td>
                    <td><?= htmlspecialchars($row["end_date"] ?? "") ?></td>
                    <td><?= htmlspecialchars($row["total_price"] ?? "") ?> €</td>
                    <td>
                        <a href="?delete_reservation=<?= $row["id"] ?>" 
                           class="btn btn-danger btn-sm"
                           onclick="return confirm('Kas kustutan broneeringu?')">
                           Kustuta
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>

</div>

</body>
</html>
