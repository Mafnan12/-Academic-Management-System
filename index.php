<?php
require_once 'includes/config.php';
require_once 'includes/db.php';

check_login();

// Redirect based on role
if (is_instructor()) {
    header("Location: pages/instructor_dashboard.php");
    exit();
} elseif (is_student()) {
    header("Location: pages/student_dashboard.php");
    exit();
} elseif (is_parent()) {
    header("Location: pages/parent_dashboard.php");
    exit();
}

// Admin dashboard continues below
// Fetch summary data for dashboard
$totalStudents = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
$totalCourses = $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();
$totalInstructors = $pdo->query("SELECT COUNT(*) FROM instructors")->fetchColumn();
$totalClasses = $pdo->query("SELECT COUNT(*) FROM classes")->fetchColumn();
$totalExams = $pdo->query("SELECT COUNT(*) FROM exams WHERE status = 'scheduled'")->fetchColumn();

// Fetch recent enrollments
$recentEnrollments = $pdo->query("
    SELECT sce.enrollment_date, s.first_name, s.last_name, c.course_name, cl.section
    FROM student_class_enrollment sce
    JOIN students s ON sce.student_id = s.id
    JOIN classes cl ON sce.class_id = cl.id
    JOIN courses c ON cl.course_id = c.id
    ORDER BY sce.enrollment_date DESC
    LIMIT 5
")->fetchAll();

// Fetch latest added students
$latestStudents = $pdo->query("
    SELECT first_name, last_name, class, section, email, created_at
    FROM students
    ORDER BY created_at DESC
    LIMIT 5
")->fetchAll();

require_once 'includes/header.php';
?>

<section class="relative h-64 rounded-xl overflow-hidden shadow-2xl group mb-8">
    <div class="absolute inset-0 bg-gradient-to-r from-[#1a237e]/90 via-[#1a237e]/60 to-[#1a237e]/30 z-10"></div>
    <img src="<?php echo BASE_URL; ?>/assets/images/campus2.jpg" alt="Campus" class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105 z-0" onerror="this.style.display='none'">
    <div class="absolute inset-0 flex flex-col justify-center px-12 z-20">
        <span class="text-white/80 font-label-sm uppercase tracking-[0.2em] mb-2">Academic Portal</span>
        <h2 class="text-white text-3xl font-bold mb-4 max-w-lg leading-tight">Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
        <div class="flex gap-4">
            <a href="pages/students.php" class="bg-white text-[#1a237e] px-6 py-2.5 rounded-lg font-bold shadow-xl hover:bg-gray-100 transition-all">Manage Students</a>
        </div>
    </div>
</section>

<section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Stats Cards -->
    <div class="glass-card p-6 rounded-xl shadow-sm hover:-translate-y-1 transition-all duration-300">
        <div class="flex items-start justify-between mb-4">
            <div class="p-3 bg-indigo-50 text-[#1a237e] rounded-lg">
                <span class="material-symbols-outlined">group</span>
            </div>
        </div>
        <p class="text-gray-500 text-xs font-bold uppercase tracking-wider mb-1">Total Students</p>
        <h3 class="text-3xl font-black text-[#1a237e]"><?php echo number_format($totalStudents); ?></h3>
    </div>
    
    <div class="glass-card p-6 rounded-xl shadow-sm hover:-translate-y-1 transition-all duration-300">
        <div class="flex items-start justify-between mb-4">
            <div class="p-3 bg-indigo-50 text-[#1a237e] rounded-lg">
                <span class="material-symbols-outlined">school</span>
            </div>
        </div>
        <p class="text-gray-500 text-xs font-bold uppercase tracking-wider mb-1">Total Courses</p>
        <h3 class="text-3xl font-black text-[#1a237e]"><?php echo number_format($totalCourses); ?></h3>
    </div>

    <div class="glass-card p-6 rounded-xl shadow-sm hover:-translate-y-1 transition-all duration-300">
        <div class="flex items-start justify-between mb-4">
            <div class="p-3 bg-indigo-50 text-[#1a237e] rounded-lg">
                <span class="material-symbols-outlined">class</span>
            </div>
        </div>
        <p class="text-gray-500 text-xs font-bold uppercase tracking-wider mb-1">Total Classes</p>
        <h3 class="text-3xl font-black text-[#1a237e]"><?php echo number_format($totalClasses); ?></h3>
    </div>

    <div class="glass-card p-6 rounded-xl shadow-sm hover:-translate-y-1 transition-all duration-300">
        <div class="flex items-start justify-between mb-4">
            <div class="p-3 bg-indigo-50 text-[#1a237e] rounded-lg">
                <span class="material-symbols-outlined">quiz</span>
            </div>
        </div>
        <p class="text-gray-500 text-xs font-bold uppercase tracking-wider mb-1">Scheduled Exams</p>
        <h3 class="text-3xl font-black text-[#1a237e]"><?php echo number_format($totalExams); ?></h3>
    </div>

    <div class="glass-card p-6 rounded-xl shadow-sm hover:-translate-y-1 transition-all duration-300">
        <div class="flex items-start justify-between mb-4">
            <div class="p-3 bg-indigo-50 text-[#1a237e] rounded-lg">
                <span class="material-symbols-outlined">how_to_reg</span>
            </div>
        </div>
        <p class="text-gray-500 text-xs font-bold uppercase tracking-wider mb-1">Enrollments</p>
        <h3 class="text-3xl font-black text-[#1a237e]"><?php echo number_format($totalStudents); ?></h3>
    </div>
</section>

<section class="glass-card rounded-xl shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between bg-white/50">
        <div>
            <h2 class="text-xl text-[#1a237e] font-bold">Recent Enrollments</h2>
            <p class="text-sm text-gray-500">Latest student course registrations</p>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                    <th class="px-6 py-4">Student Name</th>
                    <th class="px-6 py-4">Course</th>
                    <th class="px-6 py-4">Section</th>
                    <th class="px-6 py-4">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if(empty($recentEnrollments)): ?>
                    <tr><td colspan="4" class="px-6 py-4 text-center text-gray-500">No recent enrollments found.</td></tr>
                <?php else: ?>
                    <?php foreach($recentEnrollments as $enr): ?>
                    <tr class="hover:bg-indigo-50/30 transition-colors">
                        <td class="px-6 py-4 font-semibold text-[#1a237e]"><?php echo htmlspecialchars($enr['first_name'] . ' ' . $enr['last_name']); ?></td>
                        <td class="px-6 py-4 text-gray-700"><?php echo htmlspecialchars($enr['course_name']); ?></td>
                        <td class="px-6 py-4">
                            <span class="bg-indigo-100 text-indigo-800 px-3 py-1 rounded-full text-xs font-bold"><?php echo htmlspecialchars($enr['section']); ?></span>
                        </td>
                        <td class="px-6 py-4 text-gray-500 text-sm"><?php echo date('d-m-Y', strtotime($enr['enrollment_date'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="glass-card rounded-xl shadow-sm overflow-hidden mt-8">
    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between bg-white/50">
        <div>
            <h2 class="text-xl text-[#1a237e] font-bold">Latest Added Students</h2>
            <p class="text-sm text-gray-500">Newly registered students in the system</p>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                    <th class="px-6 py-4">Student Name</th>
                    <th class="px-6 py-4">Class</th>
                    <th class="px-6 py-4">Section</th>
                    <th class="px-6 py-4">Email</th>
                    <th class="px-6 py-4">Added On</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if(empty($latestStudents)): ?>
                    <tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">No students found.</td></tr>
                <?php else: ?>
                    <?php foreach($latestStudents as $student): ?>
                    <tr class="hover:bg-indigo-50/30 transition-colors">
                        <td class="px-6 py-4 font-semibold text-[#1a237e]"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                        <td class="px-6 py-4 text-gray-700"><?php echo htmlspecialchars($student['class']); ?></td>
                        <td class="px-6 py-4">
                            <span class="bg-indigo-100 text-indigo-800 px-3 py-1 rounded-full text-xs font-bold"><?php echo htmlspecialchars($student['section']); ?></span>
                        </td>
                        <td class="px-6 py-4 text-gray-500 text-sm"><?php echo htmlspecialchars($student['email']); ?></td>
                        <td class="px-6 py-4 text-gray-500 text-sm"><?php echo date('d-m-Y', strtotime($student['created_at'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
