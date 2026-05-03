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
                  primary: "#1e40af",
                  primaryDark: "#1e3a8a"
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
        <img src="<?php echo BASE_URL; ?>/assets/images/campus1.png" alt="FAST University Campus" class="w-full h-full object-cover z-0" onerror="this.style.display='none'">
    </div>

    <!-- Elegant Login Card -->
    <div class="z-20 w-full max-w-[440px] p-12 bg-white/90 backdrop-blur-2xl rounded-3xl shadow-premium border border-white/50">
        
        <div class="text-center mb-12">
            <div class="w-20 h-20 mx-auto mb-6 bg-gradient-to-br from-blue-500 to-blue-600 rounded-3xl flex items-center justify-center shadow-2xl">
                <span class="material-symbols-outlined text-white text-5xl">school</span>
            </div>
            <h1 class="text-3xl font-black text-slate-900 font-serif tracking-tight mb-2">FAST University</h1>
            <p class="text-xs text-primary font-bold tracking-[0.25em] uppercase">Management Platform</p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-50 border border-red-100 text-red-600 px-5 py-4 rounded-2xl mb-8 text-sm font-medium flex items-center gap-3 animate-pulse">
                <span class="material-symbols-outlined text-xl">error</span>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-6">
                <label class="block text-[10px] font-black text-slate-400 mb-2 uppercase tracking-[0.15em] ml-1">Username</label>
                <div class="relative group">
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary transition-colors">person</span>
                    <input type="text" name="username" class="w-full pl-12 pr-5 py-4 bg-slate-50 rounded-2xl border border-slate-100 focus:bg-white focus:ring-4 focus:ring-primary/5 focus:border-primary transition-all text-slate-800 font-medium placeholder-slate-400 outline-none" placeholder="Enter your username" required>
                </div>
            </div>
            
            <div class="mb-10">
                <label class="block text-[10px] font-black text-slate-400 mb-2 uppercase tracking-[0.15em] ml-1">Password</label>
                <div class="relative group">
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary transition-colors">lock</span>
                    <input type="password" name="password" class="w-full pl-12 pr-5 py-4 bg-slate-50 rounded-2xl border border-slate-100 focus:bg-white focus:ring-4 focus:ring-primary/5 focus:border-primary transition-all text-slate-800 font-medium placeholder-slate-400 outline-none" placeholder="••••••••" required>
                </div>
                <div class="flex items-center justify-between mt-3 px-1">
                    <p class="text-[11px] text-slate-400 font-medium italic">Hint: admin / password123</p>
                    <a href="#" class="text-[11px] text-primary font-bold hover:underline">Forgot?</a>
                </div>
            </div>

            <button type="submit" class="w-full bg-primary text-white font-bold py-4 px-6 rounded-2xl hover:bg-primary-dark transition-all duration-300 shadow-lg shadow-primary/20 hover:shadow-xl hover:shadow-primary/30 font-serif tracking-widest text-lg hover:-translate-y-1 active:translate-y-0 flex items-center justify-center gap-2">
                <span>SIGN IN</span>
                <span class="material-symbols-outlined text-xl">arrow_forward</span>
            </button>
        </form>
    </div>
</body>
<style>
    .material-symbols-outlined {
        font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
    }
</style>
</html>
