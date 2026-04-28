<?php
require_once '../includes/config.php';
require_once '../includes/db.php';

if (isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/index.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password.";
    } else {
        $stmt = $pdo->prepare("SELECT id, username, password_hash, role FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            header("Location: " . BASE_URL . "/index.php");
            exit();
        } else {
            $error = "Invalid username or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - FAST SMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .glass-panel {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
        }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen relative">
    
    <!-- Background Image with Overlay -->
    <div class="absolute inset-0 z-0">
        <!-- Will try to load campus1.jpg, fallback to dark blue gradient -->
        <div class="absolute inset-0 bg-gradient-to-br from-[#1a237e]/90 to-[#000666]/90 z-10"></div>
        <img src="<?php echo BASE_URL; ?>/assets/images/campus1.jpg" alt="Campus" class="w-full h-full object-cover z-0" onerror="this.style.display='none'">
    </div>

    <div class="z-20 w-full max-w-md p-8 glass-panel rounded-2xl shadow-2xl border border-white/50">
        <div class="text-center mb-8">
            <img src="<?php echo BASE_URL; ?>/assets/images/logo.png" alt="FAST Logo" class="w-16 h-16 mx-auto mb-4 object-contain" onerror="this.outerHTML='<div class=\'w-16 h-16 mx-auto mb-4 bg-indigo-900 rounded-full flex items-center justify-center text-white font-bold text-2xl\'>F</div>'">
            <h1 class="text-2xl font-bold text-[#1a237e]">FAST University</h1>
            <p class="text-sm text-gray-500 font-medium tracking-wide uppercase mt-1">Student Management System</p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
                <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-5">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Username</label>
                <input type="text" name="username" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#1a237e] focus:border-[#1a237e] transition-colors" placeholder="Enter username (e.g. admin)" required>
            </div>
            
            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Password</label>
                <input type="password" name="password" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#1a237e] focus:border-[#1a237e] transition-colors" placeholder="Enter password" required>
                <p class="text-xs text-gray-500 mt-2">Hint: Use admin / password123</p>
            </div>

            <button type="submit" class="w-full bg-[#1a237e] text-white font-bold py-3 px-4 rounded-lg hover:bg-[#000666] transition-colors shadow-lg">
                Sign In
            </button>
        </form>
    </div>
</body>
</html>
