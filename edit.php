<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "config.php";
require_once "csrf.php";

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    die("ID produk tidak valid.");
}

/* Ambil data produk */
$stmt = $conn->prepare(
    "SELECT id, name, category, price, stock
     FROM products
     WHERE id = ?"
);

$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();
$product = $result->fetch_assoc();

$stmt->close();

if (!$product) {
    die("Produk tidak ditemukan.");
}

$errors = [];

/* Proses UPDATE */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    verify_csrf();

    $name = trim($_POST["name"] ?? "");
    $category = trim($_POST["category"] ?? "");
    $price = $_POST["price"] ?? "";
    $stock = $_POST["stock"] ?? "";

    /* Validasi */
    if (strlen($name) < 3) {
        $errors[] = "Nama produk minimal 3 karakter.";
    }

    if ($category === "") {
        $errors[] = "Kategori wajib diisi.";
    }

    if ($price === "" || !is_numeric($price) || $price < 0) {
        $errors[] = "Harga harus berupa angka dan minimal 0.";
    }

    if ($stock === "" || !ctype_digit((string)$stock)) {
        $errors[] = "Stok harus berupa angka dan tidak boleh negatif.";
    }

    /* Cek nama unik */
    if (empty($errors)) {

        $check = $conn->prepare(
            "SELECT id
             FROM products
             WHERE name = ? AND id != ?"
        );

        $check->bind_param("si", $name, $id);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $errors[] = "Nama produk sudah digunakan.";
        }

        $check->close();
    }

    /* Update database */
    if (empty($errors)) {

        $price = (float) $price;
        $stock = (int) $stock;

        $update = $conn->prepare(
            "UPDATE products
             SET name = ?, category = ?, price = ?, stock = ?
             WHERE id = ?"
        );

        $update->bind_param(
            "ssdii",
            $name,
            $category,
            $price,
            $stock,
            $id
        );

        if ($update->execute()) {

            header("Location: index.php");
            exit;

        } else {

            $errors[] = "Gagal mengubah produk: " . $update->error;
        }

        $update->close();
    }

    /* Tampilkan kembali data yang dikirim */
    $product['name'] = $name;
    $product['category'] = $category;
    $product['price'] = $price;
    $product['stock'] = $stock;
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Produk</title>

    <link rel="stylesheet" href="assets/style.css">

</head>

<body>

<div class="container">

    <h1>Edit Produk</h1>

    <?php if (!empty($errors)): ?>

        <div class="error">

            <?php foreach ($errors as $error): ?>

                <p>
                    <?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?>
                </p>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>


    <form method="POST">

        <?= csrf_field() ?>


        <div class="form-group">

            <label for="name">Nama Produk</label>

            <input
                type="text"
                id="name"
                name="name"
                value="<?= htmlspecialchars($product['name'], ENT_QUOTES, "UTF-8") ?>"
                required
            >

        </div>


        <div class="form-group">

            <label for="category">Kategori</label>

            <input
                type="text"
                id="category"
                name="category"
                value="<?= htmlspecialchars($product['category'], ENT_QUOTES, "UTF-8") ?>"
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
                value="<?= htmlspecialchars($product['price'], ENT_QUOTES, "UTF-8") ?>"
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
                value="<?= htmlspecialchars($product['stock'], ENT_QUOTES, "UTF-8") ?>"
                required
            >

        </div>


        <button type="submit" class="btn">
            Update Produk
        </button>


        <a href="index.php" class="btn">
            Kembali
        </a>

    </form>

</div>

</body>

</html>