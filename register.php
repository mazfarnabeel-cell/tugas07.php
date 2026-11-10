
<?php
// Koneksi database
$host = "localhost";
$dbname = "db_registrasi";
$username_db = "root";
$password_db = "";

try {
    $pdo = new PDO(
        "mysql:host=$host;port=3306;dbname=$dbname;charset=utf8mb4",
        $username_db,
        $password_db
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}

$pesan = "";
$error = "";

$nama = "";
$email = "";
$username = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nama = trim($_POST["nama"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";

    // Validasi nama
    if (empty($nama)) {
        $error = "Nama lengkap wajib diisi.";
    }

    // Validasi email
    elseif (empty($email)) {
        $error = "Email wajib diisi.";
    }

    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid.";
    }

    // Validasi username
    elseif (empty($username)) {
        $error = "Username wajib diisi.";
    }

    elseif (strlen($username) < 4) {
        $error = "Username minimal 4 karakter.";
    }

    // Validasi password
    elseif (empty($password)) {
        $error = "Password wajib diisi.";
    }

    elseif (strlen($password) < 6) {
        $error = "Password minimal 6 karakter.";
    }

    else {
        try {
            // Cek email dan username
            $cek = $pdo->prepare(
                "SELECT id FROM users
                 WHERE email = ? OR username = ?"
            );

            $cek->execute([$email, $username]);

            if ($cek->rowCount() > 0) {
                $error = "Email atau username sudah terdaftar.";
            } else {

                // Enkripsi password
                $password_hash = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                // Simpan data pengguna
                $query = $pdo->prepare(
                    "INSERT INTO users
                    (nama, email, username, password)
                    VALUES (?, ?, ?, ?)"
                );

                $query->execute([
                    $nama,
                    $email,
                    $username,
                    $password_hash
                ]);

                $pesan = "Registrasi berhasil! Data berhasil disimpan.";

                $nama = "";
                $email = "";
                $username = "";
            }

        } catch (PDOException $e) {
            $error = "Gagal menyimpan data: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Registrasi Pengguna</title>

    <style>
        * {
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            margin: 0;
            background: #f2f4f7;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            width: 540px;
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 8px;
            margin-top: 20px;
            font-size: 18px;
        }

        input {
            width: 100%;
            padding: 14px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
        }

        input:focus {
            outline: none;
            border-color: #007bff;
        }

        button {
            width: 100%;
            padding: 15px;
            margin-top: 30px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            cursor: pointer;
        }

        button:hover {
            background: #0056b3;
        }

        .pesan {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 7px;
            margin-bottom: 15px;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 7px;
            margin-bottom: 15px;
        }
    </style>
</head>

<body>

<div class="container">

    <h2>Registrasi Pengguna Baru</h2>

    <?php if ($pesan): ?>
        <div class="pesan">
            <?= htmlspecialchars($pesan) ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="error">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST">

        <label>Nama Lengkap</label>
        <input type="text" name="nama"
            value="<?= htmlspecialchars($nama) ?>">

        <label>Alamat Email</label>
        <input type="email" name="email"
            value="<?= htmlspecialchars($email) ?>">

        <label>Username</label>
        <input type="text" name="username"
            value="<?= htmlspecialchars($username) ?>">

        <label>Password</label>
        <input type="password" name="password">

        <button type="submit">Daftar</button>

    </form>

</div>

</body>
</html>
