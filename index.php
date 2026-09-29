<?php

require_once "config.php";
require_once "csrf.php";

$stmt = $conn->prepare(
    "SELECT id, name, category, price, stock
     FROM products
     ORDER BY id DESC"
);

$stmt->execute();

$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Product Manager</title>

    <link rel="stylesheet" href="assets/style.css">
</head>

<body>

<div class="container">

    <h1>Product Manager</h1>

    <p>Daftar Produk</p>

    <a href="create.php" class="btn">
        + Tambah Produk
    </a>

    <div class="product-grid">

        <?php if ($result && $result->num_rows > 0): ?>

            <?php while ($product = $result->fetch_assoc()): ?>

                <div class="product-card">

                    <h3>
                        <?= htmlspecialchars(
                            $product["name"],
                            ENT_QUOTES,
                            "UTF-8"
                        ) ?>
                    </h3>

                    <p>
                        Kategori:
                        <?= htmlspecialchars(
                            $product["category"],
                            ENT_QUOTES,
                            "UTF-8"
                        ) ?>
                    </p>

                    <p class="price">
                        Rp <?= number_format(
                            $product["price"],
                            0,
                            ",",
                            "."
                        ) ?>
                    </p>

                    <p class="stock">
                        Stok:
                        <?= (int) $product["stock"] ?>
                    </p>

                    <div class="actions">

                        <a
                            href="edit.php?id=<?= (int) $product["id"] ?>"
                            class="btn">
                            Edit
                        </a>

                        <form
                            action="delete.php"
                            method="POST"
                            style="display:inline;"
                            onsubmit="return confirm('Yakin ingin menghapus produk ini?');"
                        >

                            <?= csrf_field() ?>

                            <input
                                type="hidden"
                                name="id"
                                value="<?= (int) $product["id"] ?>"
                            >

                            <button
                                type="submit"
                                class="btn"
                            >
                                Hapus
                            </button>

                        </form>

                    </div>

                </div>

            <?php endwhile; ?>

        <?php else: ?>

            <p>Belum ada produk.</p>

        <?php endif; ?>

    </div>

</div>

</body>
</html>