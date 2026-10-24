<?php
    $host = "localhost";
    $user = "root";
    $password = "";
    $database = "perpus_webpro";

    try {
        $conn = new PDO("mysql:host=$host;dbname=$database", $user, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        echo "Koneksi gagal: " . $e->getMessage();
        exit;
    }

    $pesan = '';
    $jenisPesan = '';
    $error = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hapus'])) {
        $stmt = $conn->prepare("DELETE FROM buku WHERE id = :id");
        $stmt->bindParam(':id', $_POST['hapus']);
        $stmt->execute();

        header('Location: index.php?status=deleted');
        exit;
    }

    $edit = ['id' => '', 'judul' => '', 'penulis' => '', 'tahun' => '', 'stok' => ''];

    if (isset($_GET['edit'])) {
        $stmt = $conn->prepare("SELECT * FROM buku WHERE id = :id");
        $stmt->bindParam(':id', $_GET['edit']);
        $stmt->execute();
        $edit = $stmt->fetch(PDO::FETCH_ASSOC) ?: $edit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = $_POST['id'] ?? '';
        $judul = $_POST['judul'] ?? '';
        $penulis = $_POST['penulis'] ?? '';
        $tahun = $_POST['tahun'] ?? '';
        $stok = $_POST['stok'] ?? '';

        if (empty($judul)) {
            $error[] = 'Judul buku harus diisi';
        }
        if (empty($penulis)) {
            $error[] = 'Penulis harus diisi';
        }
        if (empty($tahun)) {
            $error[] = 'Tahun terbit harus diisi';
        }
        if (empty($stok)) {
            $error[] = 'Stok harus diisi';
        }

        if (empty($error) && $id !== '') {
            $stmt = $conn->prepare("UPDATE buku SET judul = :judul, penulis = :penulis, tahun = :tahun, stok = :stok WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':judul', $judul);
            $stmt->bindParam(':penulis', $penulis);
            $stmt->bindParam(':tahun', $tahun);
            $stmt->bindParam(':stok', $stok);
            $stmt->execute();

            header('Location: index.php?status=updated');
            exit;
        } elseif (empty($error)) {
            $stmt = $conn->prepare("INSERT INTO buku (judul, penulis, tahun, stok, created_at) VALUES (:judul, :penulis, :tahun, :stok, CURDATE())");
            $stmt->bindParam(':judul', $judul);
            $stmt->bindParam(':penulis', $penulis);
            $stmt->bindParam(':tahun', $tahun);
            $stmt->bindParam(':stok', $stok);
            $stmt->execute();

            header('Location: index.php?status=success');
            exit;
        } else {
            $pesan = implode('<br>', $error);
            $jenisPesan = 'danger';
            $edit = ['id' => $id, 'judul' => $judul, 'penulis' => $penulis, 'tahun' => $tahun, 'stok' => $stok];
        }
    }

    if (($_GET['status'] ?? '') === 'success') {
        $pesan = 'Data berhasil disimpan';
        $jenisPesan = 'success';
    } elseif (($_GET['status'] ?? '') === 'updated') {
        $pesan = 'Data berhasil diubah';
        $jenisPesan = 'success';
    } elseif (($_GET['status'] ?? '') === 'deleted') {
        $pesan = 'Data berhasil dihapus';
        $jenisPesan = 'success';
    }

    $query = $conn->query("SELECT * FROM buku order by created_at desc");
    $bukus = $query->fetchAll(PDO::FETCH_ASSOC);
    
?>


<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Praktik Bootstrap</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --hijau: #1e4d3a;
            --hijau-tua: #143628;
            --hijau-muda: #e4eee7;
            --kertas: #fbfaf6;
            --putih: #ffffff;
            --tinta: #1c2a22;
            --tinta-pudar: #4f6157;
            --garis: #d9e2db;
            --kuningan: #b8891c;
            --bata: #a63d32;
            --ease: cubic-bezier(0.23, 1, 0.32, 1);
            --bs-body-font-family: "Plus Jakarta Sans", system-ui, sans-serif;
            --bs-body-color: var(--tinta);
            --bs-body-bg: var(--kertas);
            --bs-border-color: var(--garis);
        }

        body {
            background: var(--kertas);
            color: var(--tinta);
            font-size: 0.95rem;
        }

        ::selection {
            background: var(--hijau);
            color: var(--putih);
        }

        .kepala {
            background: var(--hijau-tua);
            color: var(--putih);
        }

        .kepala h1 {
            font-weight: 800;
            letter-spacing: -0.02em;
        }

        .navigasi {
            background: var(--hijau);
            color: #cfe0d5;
            font-weight: 500;
            border-bottom: 3px solid var(--kuningan);
        }

        .panel {
            background: var(--putih);
            border: 1px solid var(--garis);
            border-radius: 14px;
            box-shadow: 0 1px 2px rgba(20, 54, 40, 0.06), 0 8px 24px -12px rgba(20, 54, 40, 0.12);
        }

        .panel h2 {
            font-size: 1.35rem;
            font-weight: 700;
            letter-spacing: -0.015em;
            margin-bottom: 1.25rem;
            text-wrap: balance;
        }

        .menu .list-group-item {
            border: 0;
            border-radius: 8px;
            padding: 0.6rem 0.85rem;
            margin-bottom: 2px;
            background: transparent;
            transition: background-color 150ms ease;
        }

        .menu .list-group-item a {
            color: var(--tinta);
            display: block;
        }

        .menu .list-group-item.aktif {
            background: var(--hijau-muda);
        }

        .menu .list-group-item.aktif a {
            color: var(--hijau);
            font-weight: 700;
        }

        @media (hover: hover) and (pointer: fine) {
            .menu .list-group-item:hover {
                background: var(--hijau-muda);
            }
        }

        .form-label {
            font-weight: 600;
            font-size: 0.85rem;
            color: var(--tinta-pudar);
            margin-bottom: 0.35rem;
        }

        .form-control {
            border-color: var(--garis);
            border-radius: 8px;
            padding: 0.6rem 0.8rem;
            background: var(--kertas);
            caret-color: var(--hijau);
            transition: border-color 150ms ease, box-shadow 150ms ease, background-color 150ms ease;
        }

        .form-control:focus {
            border-color: var(--hijau);
            background: var(--putih);
            box-shadow: 0 0 0 3px rgba(30, 77, 58, 0.18);
        }

        .btn {
            font-weight: 600;
            border-radius: 8px;
            transition: transform 160ms var(--ease), background-color 150ms ease, border-color 150ms ease;
        }

        .btn:active {
            transform: scale(0.97);
        }

        .btn + .btn,
        .btn + form {
            margin-left: 0.4rem;
        }

        .btn:focus-visible {
            outline: 2px solid var(--kuningan);
            outline-offset: 2px;
            box-shadow: none;
        }

        .btn-primary {
            --bs-btn-bg: var(--hijau);
            --bs-btn-border-color: var(--hijau);
            --bs-btn-hover-bg: var(--hijau-tua);
            --bs-btn-hover-border-color: var(--hijau-tua);
            --bs-btn-active-bg: var(--hijau-tua);
            --bs-btn-active-border-color: var(--hijau-tua);
            padding: 0.6rem 1.4rem;
        }

        .btn-secondary {
            --bs-btn-color: var(--tinta);
            --bs-btn-bg: transparent;
            --bs-btn-border-color: var(--garis);
            --bs-btn-hover-color: var(--tinta);
            --bs-btn-hover-bg: var(--hijau-muda);
            --bs-btn-hover-border-color: var(--garis);
            --bs-btn-active-color: var(--tinta);
            --bs-btn-active-bg: var(--hijau-muda);
            --bs-btn-active-border-color: var(--garis);
            padding: 0.6rem 1.2rem;
        }

        .btn-warning {
            --bs-btn-color: #5a4209;
            --bs-btn-bg: #f6ecd2;
            --bs-btn-border-color: #ead9a8;
            --bs-btn-hover-color: #4a3607;
            --bs-btn-hover-bg: #efdfb4;
            --bs-btn-hover-border-color: #e0ca8a;
            --bs-btn-active-bg: #efdfb4;
            --bs-btn-active-border-color: #e0ca8a;
        }

        .btn-danger {
            --bs-btn-color: var(--bata);
            --bs-btn-bg: #f8e7e4;
            --bs-btn-border-color: #efcdc8;
            --bs-btn-hover-color: var(--putih);
            --bs-btn-hover-bg: var(--bata);
            --bs-btn-hover-border-color: var(--bata);
            --bs-btn-active-color: var(--putih);
            --bs-btn-active-bg: #8c3229;
            --bs-btn-active-border-color: #8c3229;
        }

        .panel.mode-edit {
            border-color: #e0ca8a;
            animation: sorot 900ms var(--ease);
        }

        @keyframes sorot {
            from {
                box-shadow: 0 0 0 6px rgba(184, 137, 28, 0.25), 0 8px 24px -12px rgba(20, 54, 40, 0.12);
            }
        }

        .table {
            --bs-table-striped-bg: #f4f7f4;
            --bs-table-border-color: var(--garis);
            font-variant-numeric: tabular-nums;
            margin-bottom: 0;
            border-radius: 10px;
            overflow: hidden;
        }

        .table thead th {
            background: var(--hijau-muda);
            color: var(--hijau-tua);
            font-weight: 700;
            font-size: 0.82rem;
            border-bottom-color: #c8d6cc;
        }

        .table td,
        .table th {
            padding: 0.7rem 0.85rem;
            vertical-align: middle;
        }

        .alert {
            border-radius: 10px;
            font-weight: 500;
        }

        .alert-success {
            --bs-alert-bg: var(--hijau-muda);
            --bs-alert-border-color: #c8d6cc;
            --bs-alert-color: var(--hijau-tua);
        }

        .alert-danger {
            --bs-alert-bg: #f8e7e4;
            --bs-alert-border-color: #efcdc8;
            --bs-alert-color: #7a2c24;
        }

        .kaki {
            background: transparent;
            color: var(--tinta-pudar);
            border-top: 1px solid var(--garis);
            font-size: 0.85rem;
        }

        @media (prefers-reduced-motion: reduce) {
            .panel.mode-edit {
                animation: none;
            }

            .btn:active {
                transform: none;
            }
        }
    </style>
</head>

<body>
    <header class="kepala p-4">
        <h1 class="h3 m-0">Website Praktik</h1>
    </header>

    <nav class="navigasi p-3">Navigasi</nav>
    <div class="container-fluid my-4">
        <div class="row min-vh-100">
            <aside class="col-md-3 mb-4">
                <div class="panel menu p-3">
                    <h2>Menu</h2>
                    <ul class="list-group">
                        <li class="list-group-item"><a href="#"
                                class="text-decoration-none">Dashboard</a></li>
                        <li class="list-group-item aktif"><a href="index.php" class="text-decoration-none" aria-current="page">Daftar Buku</a>
                        </li>
                        <li class="list-group-item"><a href="#" class="text-decoration-none">Form
                                Peminjam</a></li>
                    </ul>
                </div>
            </aside>

            <main class="col-md-9">
                <section class="panel p-4<?= $edit['id'] !== '' ? ' mode-edit' : '' ?>">
                    <h2><?= $edit['id'] !== '' ? 'Form Edit Buku' : 'Form Tambah Buku' ?></h2>
                    <form action="index.php" method="POST" id="formBuku" novalidate>
                        <input type="hidden" name="id" value="<?= $edit['id'] ?>" />
                        <div class="row g-3">
                            <div class="col-md-6 mb-3">
                                <label for="judul" class="form-label">Judul Buku</label>
                                <input type="text" class="form-control" id="judul" name="judul" value="<?= htmlspecialchars($edit['judul']) ?>" required minlength="3">
                                <div class="invalid-feedback">
                                    Judul buku harus diisi dan minimal 3 karakter.
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="penulis" class="form-label">Penulis</label>
                                <input type="text" class="form-control" id="penulis" name="penulis" value="<?= htmlspecialchars($edit['penulis']) ?>" required
                                    minlength="3">
                                <div class="invalid-feedback">
                                    Penulis buku harus diisi dan minimal 3 karakter.
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="tahun" class="form-label">Tahun terbit</label>
                                <input type="number" class="form-control" id="tahun" name="tahun" value="<?= htmlspecialchars($edit['tahun']) ?>" required min="1900"
                                    max="2026">
                                <div class="invalid-feedback">
                                    Tahun terbit harus diisi antara 1900 dan 2026.
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="stok" class="form-label">Stok</label>
                                <input type="number" class="form-control" id="stok" name="stok" value="<?= htmlspecialchars($edit['stok']) ?>" required min="0"
                                    step="1">
                                <div class="invalid-feedback">
                                    Stok harus diisi dan minimal 0.
                                </div>
                            </div>

                        </div>

                        <button type="submit" class="btn btn-primary"><?= $edit['id'] !== '' ? 'Update' : 'Submit' ?></button>
                        <?php if ($edit['id'] !== ''): ?>
                            <a href="index.php" class="btn btn-secondary">Batal</a>
                        <?php endif; ?>
                    </form>
                </section>

                <section class="panel p-4 mt-4">
                    <h2>Daftar Buku</h2>

                    <?php if ($pesan !== ''): ?>
                        <div class="alert alert-<?= $jenisPesan ?>" role="alert">
                            <?= $pesan ?>
                        </div>
                    <?php endif; ?>

                    <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Judul</th>
                                <th>Penulis</th>
                                <th>Tahun Terbit</th>
                                <th>Stok</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($bukus)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4" style="color: var(--tinta-pudar)">Tidak ada data buku.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($bukus as $index => $buku): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td><?= $buku['judul'] ?></td>
                                        <td><?= $buku['penulis'] ?></td> 
                                        <td><?= $buku['tahun'] ?></td>
                                        <td><?= $buku['stok'] ?></td>
                                        <td class="text-nowrap">
                                            <a href="index.php?edit=<?= $buku['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                            <form action="index.php" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus buku ini?')">
                                                <input type="hidden" name="hapus" value="<?= $buku['id'] ?>">
                                                <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    </div>
                </section>
            </main>
        </div>
    </div>

    <footer class="kaki text-center p-3 mx-4 mb-3">
        <p class="mb-0">Copyright &copy; 2023 Website Praktik. All rights reserved.</p>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const form = document.getElementById("formBuku");

        form.addEventListener("submit", function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                form.classList.add("was-validated");
            }
        });
    </script>
</body>
</html>