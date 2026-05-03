<?php
require_once '../includes/config.php';
require_once '../includes/db.php';

check_login();

// Handle Add Student
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_student'])) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $dob = $_POST['dob'];
    $bform_cnic = trim($_POST['bform_cnic']);
    $address = trim($_POST['address']);
    $parent_info = trim($_POST['parent_info']);
    $admission_year = trim($_POST['admission_year']);
    $class = trim($_POST['class']);
    $section = trim($_POST['section']);
    $roll_number = trim($_POST['roll_number']);
    $batch_year = trim($_POST['batch_year']);

    $errors = [];

    if (empty($first_name) || strlen($first_name) < 2) {
        $errors[] = "First name must be at least 2 characters.";
    }
    if (empty($last_name) || strlen($last_name) < 2) {
        $errors[] = "Last name must be at least 2 characters.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please provide a valid email address.";
    }
    if (!empty($phone) && !preg_match('/^\+?[\d\s\-\(\)]+$/', $phone)) {
        $errors[] = "Please provide a valid phone number.";
    }
    if (!empty($dob) && !strtotime($dob)) {
        $errors[] = "Please provide a valid date of birth.";
    }
    if (empty($class)) {
        $errors[] = "Please select a class.";
    }
    if (empty($section)) {
        $errors[] = "Please select a section.";
    }
    if (empty($roll_number)) {
        $errors[] = "Roll number is required.";
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO students (first_name, last_name, email, phone, date_of_birth, bform_cnic, address, parent_info, admission_year, class, section, roll_number, batch_year, enrollment_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURDATE())");
            $stmt->execute([$first_name, $last_name, $email, $phone, $dob, $bform_cnic, $address, $parent_info, $admission_year, $class, $section, $roll_number, $batch_year]);
            set_flash_message('success', 'Student added successfully.');
            header("Location: students.php");
            exit();
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Duplicate entry
                set_flash_message('error', 'A student with this email or roll number already exists.');
            } else {
                set_flash_message('error', 'Error adding student: ' . $e->getMessage());
            }
        }
    } else {
        set_flash_message('error', implode('<br>', $errors));
    }
    header("Location: students.php");
    exit();
}

// Handle Delete Student
if (isset($_GET['delete']) && is_admin()) {
    $id = (int)$_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
        $stmt->execute([$id]);
        set_flash_message('success', 'Student deleted successfully.');
    } catch (PDOException $e) {
        set_flash_message('error', 'Error deleting student.');
    }
    header("Location: students.php");
    exit();
}

// Search and Pagination
$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Build Query
$query = "FROM students WHERE 1=1";
$params = [];
if (!empty($search)) {
    $query .= " AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ?)";
    $searchTerm = "%$search%";
    $params = [$searchTerm, $searchTerm, $searchTerm];
}

// Total count for pagination
$totalStmt = $pdo->prepare("SELECT COUNT(*) $query");
$totalStmt->execute($params);
$totalStudents = $totalStmt->fetchColumn();
$totalPages = ceil($totalStudents / $limit);

// Fetch students
$stmt = $pdo->prepare("SELECT * $query ORDER BY id DESC LIMIT $limit OFFSET $offset");
$stmt->execute($params);
$students = $stmt->fetchAll();

require_once '../includes/header.php';
?>

<div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-10">
    <div>
        <h1 class="text-3xl font-black text-secondary-dark font-serif tracking-tight">Students Directory</h1>
        <p class="text-xs text-slate-400 font-bold uppercase tracking-widest mt-1">Manage university student records & academic profiles</p>
    </div>
    <?php if(is_admin()): ?>
    <button onclick="openModal()" class="bg-primary text-white px-8 py-3.5 rounded-2xl font-bold shadow-lg shadow-primary/20 hover:bg-primary-dark transition-all flex items-center gap-2 group hover:-translate-y-1">
        <span class="material-symbols-outlined transition-transform group-hover:rotate-90">add</span> 
        <span>Add New Student</span>
    </button>
    <?php endif; ?>
</div>

<!-- Search & Filters -->
<div class="glass-card p-6 rounded-2xl mb-10 border border-slate-100">
    <form method="GET" class="flex flex-col md:flex-row gap-4">
        <div class="relative flex-1 group">
            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary transition-colors">search</span>
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search by name, email, or roll number..." class="w-full pl-12 pr-5 py-3.5 bg-slate-50 rounded-xl border border-slate-100 focus:bg-white focus:ring-4 focus:ring-primary/5 focus:border-primary transition-all text-sm font-medium outline-none">
        </div>
        <button type="submit" class="bg-secondary text-white px-8 py-3.5 rounded-xl font-bold hover:bg-secondary-dark transition-all shadow-md shadow-secondary/10">Search Records</button>
        <?php if($search): ?>
            <a href="students.php" class="bg-slate-100 text-slate-600 px-6 py-3.5 rounded-xl font-bold hover:bg-slate-200 transition-all flex items-center justify-center">Clear</a>
        <?php endif; ?>
    </form>
</div>

<div class="glass-card rounded-2xl overflow-hidden border border-slate-100 shadow-soft">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50/50 text-slate-400 text-[10px] font-black uppercase tracking-[0.15em]">
                    <th class="px-8 py-5">Student Information</th>
                    <th class="px-8 py-5">Academic Info</th>
                    <th class="px-8 py-5">Roll Number</th>
                    <th class="px-8 py-5">Status</th>
                    <?php if(is_admin()): ?><th class="px-8 py-5 text-right">Actions</th><?php endif; ?>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                <?php if(empty($students)): ?>
                    <tr><td colspan="5" class="px-8 py-12 text-center text-slate-400 font-medium italic">No student records match your search criteria.</td></tr>
                <?php else: ?>
                    <?php foreach($students as $student): ?>
                    <tr class="hover:bg-slate-50/80 transition-colors group">
                        <td class="px-8 py-5">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-xl bg-primary-soft text-primary flex items-center justify-center font-black text-sm">
                                    <?php echo strtoupper(substr($student['first_name'], 0, 1)); ?>
                                </div>
                                <div>
                                    <div class="font-bold text-secondary-dark group-hover:text-primary transition-colors"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></div>
                                    <div class="text-[11px] text-slate-400 font-medium"><?php echo htmlspecialchars($student['email']); ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-8 py-5">
                            <div class="flex flex-col gap-1">
                                <span class="bg-primary/5 text-primary px-2.5 py-0.5 rounded-lg text-[10px] font-black w-fit"><?php echo htmlspecialchars($student['class']); ?></span>
                                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider pl-1">Section <?php echo htmlspecialchars($student['section']); ?></span>
                            </div>
                        </td>
                        <td class="px-8 py-5 text-sm font-bold text-secondary-dark"><?php echo htmlspecialchars($student['roll_number']); ?></td>
                        <td class="px-8 py-5">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider
                                <?php echo $student['status'] === 'active' ? 'bg-emerald-50 text-emerald-600' :
                                         ($student['status'] === 'transferred' ? 'bg-amber-50 text-amber-600' :
                                         ($student['status'] === 'left' ? 'bg-rose-50 text-rose-600' : 'bg-blue-50 text-blue-600')); ?>">
                                <span class="w-1.5 h-1.5 rounded-full mr-2 <?php echo $student['status'] === 'active' ? 'bg-emerald-500' : ($student['status'] === 'left' ? 'bg-rose-500' : 'bg-slate-400'); ?>"></span>
                                <?php echo htmlspecialchars($student['status']); ?>
                            </span>
                        </td>
                        <?php if(is_admin()): ?>
                        <td class="px-8 py-5 text-right">
                            <div class="flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all" title="Edit Profile">
                                    <span class="material-symbols-outlined text-xl">edit</span>
                                </button>
                                <a href="?delete=<?php echo $student['id']; ?>" onclick="return confirm('Are you sure you want to delete this student record?');" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-all" title="Delete Student">
                                    <span class="material-symbols-outlined text-xl">delete</span>
                                </a>
                            </div>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
<?php if($totalPages > 1): ?>
<div class="flex justify-center items-center gap-3 mt-10">
    <button class="w-10 h-10 flex items-center justify-center rounded-xl border border-slate-200 text-slate-400 hover:border-primary hover:text-primary transition-all disabled:opacity-30" <?php echo $page <= 1 ? 'disabled' : ''; ?> onclick="window.location.href='?page=<?php echo $page-1; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?>'">
        <span class="material-symbols-outlined">chevron_left</span>
    </button>
    
    <div class="flex gap-2">
        <?php for($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?php echo $i; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?>" class="w-10 h-10 flex items-center justify-center rounded-xl font-bold text-sm transition-all <?php echo $i === $page ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'bg-white border border-slate-200 text-slate-600 hover:border-primary hover:text-primary'; ?>">
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>
    </div>

    <button class="w-10 h-10 flex items-center justify-center rounded-xl border border-slate-200 text-slate-400 hover:border-primary hover:text-primary transition-all disabled:opacity-30" <?php echo $page >= $totalPages ? 'disabled' : ''; ?> onclick="window.location.href='?page=<?php echo $page+1; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?>'">
        <span class="material-symbols-outlined">chevron_right</span>
    </button>
</div>
<?php endif; ?>

<!-- Add Student Modal -->
<div id="addModal" class="fixed inset-0 bg-secondary-dark/60 hidden z-50 flex items-center justify-center backdrop-blur-md transition-all duration-300">
    <div class="bg-white rounded-3xl shadow-premium p-0 w-full max-w-2xl transform scale-95 opacity-0 transition-all duration-300 relative max-h-[90vh] flex flex-col" id="modalContent">
        <div class="px-10 py-8 border-b border-slate-100 flex justify-between items-center bg-slate-50/50 rounded-t-3xl">
            <div>
                <h2 class="text-2xl font-black text-secondary-dark font-serif flex items-center gap-3"><span class="material-symbols-outlined text-primary">person_add</span> Add New Student</h2>
                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-[0.2em] mt-1">Enroll a new student into the system</p>
            </div>
            <button type="button" onclick="closeModal()" class="w-10 h-10 flex items-center justify-center rounded-xl text-slate-400 hover:bg-white hover:text-rose-500 transition-all shadow-sm"><span class="material-symbols-outlined">close</span></button>
        </div>
        
        <form method="POST" action="" class="p-10 overflow-y-auto custom-scrollbar">
            <input type="hidden" name="add_student" value="1">
            
            <div class="space-y-8">
                <!-- Personal Info Section -->
                <div>
                    <h3 class="text-[10px] font-black text-primary uppercase tracking-[0.2em] mb-6 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-primary"></span> Personal Information
                    </h3>
                    <div class="grid grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-wider ml-1">First Name</label>
                            <input type="text" name="first_name" required class="w-full px-5 py-3.5 bg-slate-50 rounded-xl border border-slate-100 focus:bg-white focus:ring-4 focus:ring-primary/5 focus:border-primary transition-all text-sm font-medium outline-none">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-wider ml-1">Last Name</label>
                            <input type="text" name="last_name" required class="w-full px-5 py-3.5 bg-slate-50 rounded-xl border border-slate-100 focus:bg-white focus:ring-4 focus:ring-primary/5 focus:border-primary transition-all text-sm font-medium outline-none">
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-wider ml-1">Email Address</label>
                        <input type="email" name="email" required class="w-full px-5 py-3.5 bg-slate-50 rounded-xl border border-slate-100 focus:bg-white focus:ring-4 focus:ring-primary/5 focus:border-primary transition-all text-sm font-medium outline-none" placeholder="student@example.com">
                    </div>
                    <div class="space-y-2">
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-wider ml-1">Phone Number</label>
                        <input type="text" name="phone" class="w-full px-5 py-3.5 bg-slate-50 rounded-xl border border-slate-100 focus:bg-white focus:ring-4 focus:ring-primary/5 focus:border-primary transition-all text-sm font-medium outline-none" placeholder="+92 3XX XXXXXXX">
                    </div>
                </div>

                <!-- Academic Info Section -->
                <div>
                    <h3 class="text-[10px] font-black text-primary uppercase tracking-[0.2em] mb-6 pt-4 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-primary"></span> Academic Records
                    </h3>
                    <div class="grid grid-cols-3 gap-6">
                        <div class="space-y-2">
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-wider ml-1">Class</label>
                            <select name="class" class="w-full px-5 py-3.5 bg-slate-50 rounded-xl border border-slate-100 focus:bg-white focus:ring-4 focus:ring-primary/5 focus:border-primary transition-all text-sm font-black outline-none appearance-none">
                                <option value="">Select Class</option>
                                <?php for($i=1; $i<=8; $i++) echo "<option value='BCS-$i'>BCS-$i</option>"; ?>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-wider ml-1">Section</label>
                            <select name="section" class="w-full px-5 py-3.5 bg-slate-50 rounded-xl border border-slate-100 focus:bg-white focus:ring-4 focus:ring-primary/5 focus:border-primary transition-all text-sm font-black outline-none appearance-none">
                                <option value="">Select</option>
                                <?php foreach(['A','B','C','D'] as $sec) echo "<option value='$sec'>$sec</option>"; ?>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-wider ml-1">Roll Number</label>
                            <input type="text" name="roll_number" placeholder="2024-BCS-XXX" class="w-full px-5 py-3.5 bg-slate-50 rounded-xl border border-slate-100 focus:bg-white focus:ring-4 focus:ring-primary/5 focus:border-primary transition-all text-sm font-medium outline-none">
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-4 mt-12 pt-8 border-t border-slate-100">
                <button type="button" onclick="closeModal()" class="px-8 py-3.5 rounded-xl text-xs font-black uppercase tracking-widest text-slate-400 hover:text-slate-600 hover:bg-slate-50 transition-all">Discard</button>
                <button type="submit" class="bg-primary text-white px-10 py-3.5 rounded-xl text-xs font-black uppercase tracking-widest shadow-lg shadow-primary/20 hover:bg-primary-dark transition-all">Confirm Enrollment</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal() {
    const modal = document.getElementById('addModal');
    const content = document.getElementById('modalContent');
    modal.classList.remove('hidden');
    // Small delay to allow display:block to apply before animating opacity/transform
    setTimeout(() => {
        content.classList.remove('scale-95', 'opacity-0');
        content.classList.add('scale-100', 'opacity-100');
    }, 10);
}

function closeModal() {
    const modal = document.getElementById('addModal');
    const content = document.getElementById('modalContent');
    content.classList.remove('scale-100', 'opacity-100');
    content.classList.add('scale-95', 'opacity-0');
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 300); // Wait for transition to finish
}
</script>

<?php require_once '../includes/footer.php'; ?>
