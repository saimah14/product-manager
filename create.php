<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "config.php";
require_once "csrf.php";

$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    verify_csrf();

    $name = trim($_POST["name"] ?? "");
    $category = trim($_POST["category"] ?? "");
    $price = $_POST["price"] ?? "";
    $stock = $_POST["stock"] ?? "";

    if (strlen($name) < 3) {
        $errors[] = "Nama produk minimal 3 karakter.";
    }

    if ($category === "") {
        $errors[] = "Kategori wajib diisi.";
    }

    if ($price === "" || !is_numeric($price) || $price < 0) {
        $errors[] = "Harga harus angka dan minimal 0.";
    }

    if ($stock === "" || !ctype_digit((string)$stock)) {
        $errors[] = "Stok harus berupa angka.";
    }

    if (empty($errors)) {

        $check = $conn->prepare(
            "SELECT id FROM products WHERE name = ?"
        );

        $check->bind_param("s", $name);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $errors[] = "Nama produk sudah digunakan.";
        }

        $check->close();
    }

    if (empty($errors)) {

        $stmt = $conn->prepare(
            "INSERT INTO products (name, category, price, stock)
             VALUES (?, ?, ?, ?)"
        );

        $price = (float)$price;
        $stock = (int)$stock;

        $stmt->bind_param(
            "ssdi",
            $name,
            $category,
            $price,
            $stock
        );

        if ($stmt->execute()) {
            header("Location: index.php");
            exit;
        } else {
            $errors[] = "Gagal menyimpan produk: " . $stmt->error;
        }

        $stmt->close();
    }
}

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Tambah Produk</title>

    <link rel="stylesheet" href="assets/style.css">
</head>

<body>

<div class="container">

    <h1>Tambah Produk</h1>

    <?php if (!empty($errors)): ?>

        <div class="error">

            <?php foreach ($errors as $error): ?>

                <p>
                    <?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?>
                </p>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>


    <form method="POST" action="">

        <?= csrf_field() ?>

        <div class="form-group">

            <label for="name">Nama Produk</label>

            <input
                type="text"
                id="name"
                name="name"
                required
            >

        </div>


        <div class="form-group">

            <label for="category">Kategori</label>

            <input
                type="text"
                id="category"
                name="category"
                required
            >

        </div>


        <div class="form-group">

            <label for="price">Harga</label>

            <input
                type="number"
                id="price"
                name="price"
                min="0"
                step="0.01"
                required
            >

        </div>


        <div class="form-group">

            <label for="stock">Stok</label>

            <input
                type="number"
                id="stock"
                name="stock"
                min="0"
                required
            >

        </div>


        <button type="submit" class="btn">
            Simpan Produk
        </button>

        <a href="index.php" class="btn">
            Kembali
        </a>

    </form>

</div>

</body>

</html>