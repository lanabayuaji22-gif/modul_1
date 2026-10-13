<?php
include 'koneksi.php'; 
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

if (isset($_SESSION['username'])) {
    if ($_SESSION['jabatan'] == 'atasan') {
        header("Location: Website/index.php");
    } elseif ($_SESSION['jabatan'] == 'Karyawan') {
        header("Location: adminitrasi/index.php");
    }
    exit();
}

$redirect_url = '';
$show_confirm = false;
$error = '';
$jabatan = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    if (isset($_POST['username']) && !isset($_POST['confirm_login'])) {
        $username = $_POST['username'];
        $password = md5($_POST['password']); 
        $jabatan  = $_POST['jabatan'];

        $query = "SELECT * FROM user WHERE username = ? AND password = ? AND jabatan = ?";
        $stmt = $koneksi->prepare($query);
        $stmt->bind_param("sss", $username, $password, $jabatan);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $data = $result->fetch_assoc();
            
            $_SESSION['username'] = $data['username'];
            $_SESSION['jabatan']  = $data['jabatan'];

            if ($data['jabatan'] == 'atasan') {
                $redirect_url = "Website/index.php";
            } elseif ($data['jabatan'] == 'Karyawan') {
                $redirect_url = "adminitrasi/index.php";
            }
            
            $show_confirm = true;
        } else {
            $error = "Username, Password, atau Jabatan salah!";
        }
    }
    
    if (isset($_POST['confirm_login'])) {
        if ($_POST['confirm_login'] == 'yes') {
            $target = $_POST['redirect_url'];
            if (!empty($target)) {
                header("Location: " . $target);
                exit();
            } else {
                $error = "Gagal mengalihkan halaman, URL kosong.";
            }
        } else {
            session_unset();
            session_destroy();
            header("Location: login.php");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="disc/css/style.css">
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
        <form method="POST" action="">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <select name="jabatan">
                <option value="">Pilih Jabatan</option>
                <option value="atasan">Atasan</option>
                <option value="Karyawan">Karyawan</option>
            </select>
            <button type="submit">Login</button>
        </form>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        
        <!-- Jam Digital -->
        <div class="clock-container">
            <div class="clock" id="clock"></div>
            <div class="date" id="date"></div>
        </div>
    </div>

    <?php if ($show_confirm): ?>
    <div class="modal show" id="confirmModal">
        <div class="modal-content">
            <h3>Konfirmasi Login</h3>
            <p>Apakah Anda yakin ingin login sebagai <span class="jabatan-text"><?php echo ucfirst($jabatan); ?></span>?</p>
            <div class="redirect-text"><?php echo $redirect_url; ?></div>
            <form method="POST" action="">
                <input type="hidden" name="redirect_url" value="<?php echo $redirect_url; ?>">
                <input type="hidden" name="jabatan" value="<?php echo $jabatan; ?>">
                <div class="btn-group">
                    <button type="submit" name="confirm_login" value="yes" class="btn-yes">Ya, Login</button>
                    <button type="submit" name="confirm_login" value="no" class="btn-no">Batal</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <script src="disc/js/jam.js"></script>
</body>
</html>
