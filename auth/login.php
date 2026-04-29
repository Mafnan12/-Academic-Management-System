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
        $stmt = $pdo->prepare("SELECT id, username, password_hash, role, email FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['email'] = $user['email'];

            // Redirect based on role
            switch ($user['role']) {
                case 'admin':
                    header("Location: " . BASE_URL . "/index.php");
                    break;
                case 'instructor':
                    header("Location: " . BASE_URL . "/pages/instructor_dashboard.php");
                    break;
                case 'student':
                    header("Location: " . BASE_URL . "/pages/student_dashboard.php");
                    break;
                case 'parent':
                    header("Location: " . BASE_URL . "/pages/parent_dashboard.php");
                    break;
                default:
                    header("Location: " . BASE_URL . "/index.php");
            }
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700;800;900&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
          theme: {
            extend: {
              colors: {
                  primary: "#A51C30",
                  primaryDark: "#750F1D"
              },
              fontFamily: {
                  sans: ["Inter", "sans-serif"],
                  serif: ["Playfair Display", "serif"]
              }
            }
          }
        }
    </script>
</head>
<body class="bg-gray-900 flex items-center justify-center min-h-screen relative font-sans">
    
    <!-- Premium Campus Background -->
    <div class="absolute inset-0 z-0">
        <div class="absolute inset-0 bg-black/50 z-10 backdrop-blur-[2px]"></div>
        <img src="<?php echo BASE_URL; ?>/assets/images/campus1.jpg" alt="FAST University Campus" class="w-full h-full object-cover z-0" onerror="this.style.display='none'">
    </div>

    <!-- Elegant Login Card -->
    <div class="z-20 w-full max-w-[420px] p-10 bg-white/95 backdrop-blur-3xl rounded-3xl shadow-[0_30px_60px_-15px_rgba(0,0,0,0.5)] border border-white/40">
        
        <div class="text-center mb-10">
            <div class="w-16 h-16 mx-auto mb-5 bg-primary rounded-2xl flex items-center justify-center shadow-lg shadow-primary/30 text-white font-serif font-black text-3xl">F</div>
            <h1 class="text-3xl font-black text-gray-900 font-serif tracking-tight mb-1">FAST University</h1>
            <p class="text-[11px] text-primary font-bold tracking-[0.2em] uppercase">Management Platform</p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-50 border border-red-100 text-red-600 px-4 py-3 rounded-xl mb-6 text-sm font-medium flex items-center gap-2">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-5">
                <label class="block text-xs font-bold text-gray-500 mb-2 uppercase tracking-wider">Username</label>
                <input type="text" name="username" class="w-full px-5 py-3.5 bg-gray-50 rounded-xl border border-gray-200 focus:bg-white focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all text-gray-800 font-medium placeholder-gray-400 outline-none" placeholder="e.g. admin" required>
            </div>
            
            <div class="mb-8">
                <label class="block text-xs font-bold text-gray-500 mb-2 uppercase tracking-wider">Password</label>
                <input type="password" name="password" class="w-full px-5 py-3.5 bg-gray-50 rounded-xl border border-gray-200 focus:bg-white focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all text-gray-800 font-medium placeholder-gray-400 outline-none" placeholder="••••••••" required>
                <p class="text-[11px] text-gray-400 mt-2 font-medium">Hint: admin / password123</p>
            </div>

            <button type="submit" class="w-full bg-primary text-white font-bold py-4 px-4 rounded-xl hover:bg-primaryDark transition-all duration-300 shadow-[0_10px_20px_-10px_rgba(165,28,48,0.5)] hover:shadow-[0_10px_20px_-10px_rgba(165,28,48,0.8)] font-serif tracking-widest text-lg hover:-translate-y-0.5">
                Sign In
            </button>
        </form>
    </div>
</body>
</html>
