<?php

require_once "config.php";
require_once "csrf.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

verify_csrf();

$id = isset($_POST["id"]) ? (int) $_POST["id"] : 0;

if ($id <= 0) {
    die("ID produk tidak valid.");
}

$stmt = $conn->prepare(
    "DELETE FROM products WHERE id = ?"
);

$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: index.php");
    exit;
}

die("Produk gagal dihapus.");

$stmt->close();