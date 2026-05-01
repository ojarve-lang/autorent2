<?php
include("../inc/db.php");

$message = "";
$car_id = $_GET['car_id'] ?? null;

if (!$car_id) {
    die("Auto ID puudub.");
}

$stmt = $conn->prepare("SELECT * FROM cars WHERE id = ?");
$stmt->bind_param("i", $car_id);
$stmt->execute();
$car = $stmt->get_result()->fetch_assoc();

if (!$car) {
    die("Autot ei leitud.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $customer_name = trim($_POST["customer_name"]);
    $customer_email = trim($_POST["customer_email"]);
    $start_date = $_POST["start_date"];
    $end_date = $_POST["end_date"];

if (!filter_var($customer_email, FILTER_VALIDATE_EMAIL)) {
    $message = "Palun sisesta korrektne email.";
} elseif ($start_date > $end_date) {
    $message = "Alguskuupäev ei saa olla hilisem kui lõppkuupäev.";
} else {
        $check = $conn->prepare("
            SELECT id FROM reservations
            WHERE car_id = ?
            AND NOT (end_date < ? OR start_date > ?)
        ");
        $check->bind_param("iss", $car_id, $start_date, $end_date);
        $check->execute();
        $existing = $check->get_result();

        if ($existing->num_rows > 0) {
            $message = "See auto on valitud perioodil juba renditud.";
        } else {
            $days = max(1, (strtotime($end_date) - strtotime($start_date)) / 86400 + 1);
            $total_price = $days * (float)$car["price"];

            $insert = $conn->prepare("
                INSERT INTO reservations 
                (car_id, start_date, end_date, total_price, customer_name, customer_email)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $insert->bind_param("issdss", $car_id, $start_date, $end_date, $total_price, $customer_name, $customer_email);

            if ($insert->execute()) {
                $message = "Broneering lisatud! Kokku: " . number_format($total_price, 2) . " €";
            } else {
                $message = "Broneeringu lisamine ebaõnnestus.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="et">
<head>
    <meta charset="UTF-8">
    <title>Auto rentimine</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <a href="index.php" class="btn btn-secondary mb-4">Tagasi avalehele</a>

    <div class="card shadow">
        <div class="card-body">
            <h1><?= htmlspecialchars($car["mark"]) ?> <?= htmlspecialchars($car["model"]) ?></h1>
            <p><strong>Aasta:</strong> <?= htmlspecialchars($car["year"] ?? "") ?></p>
            <p><strong>Kütus:</strong> <?= htmlspecialchars($car["fuel"] ?? "") ?></p>
            <p><strong>Käigukast:</strong> <?= htmlspecialchars($car["transmission"] ?? "") ?></p>
            <p><strong>Hind:</strong> <?= htmlspecialchars($car["price"] ?? "0") ?> € / päev</p>

            <?php if ($message): ?>
                <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <h3 class="mt-4">Rendi auto</h3>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Nimi</label>
                    <input type="text" name="customer_name" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="customer_email" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Alguskuupäev</label>
                    <input type="date" name="start_date" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Lõppkuupäev</label>
                    <input type="date" name="end_date" class="form-control" required>
                </div>

                <button class="btn btn-primary">Kinnita rent</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>
