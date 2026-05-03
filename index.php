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
$totalEnrollments = $pdo->query("SELECT COUNT(*) FROM student_class_enrollment WHERE status = 'enrolled'")->fetchColumn();
$paidFees = $pdo->query("SELECT SUM(amount) FROM fees WHERE status = 'paid'")->fetchColumn() ?? 0;
$pendingFees = $pdo->query("SELECT SUM(amount) FROM fees WHERE status = 'pending'")->fetchColumn() ?? 0;

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

<section class="relative h-80 rounded-3xl overflow-hidden shadow-premium group mb-10 bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-800">
    <div class="absolute inset-0 bg-black/20 z-10"></div>
    <img src="<?php echo BASE_URL; ?>/assets/images/campus1.png" alt="Campus" class="absolute inset-0 w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110 z-0" onerror="this.style.display='none'">
    <div class="absolute inset-0 flex flex-col justify-center px-12 z-20">
        <span class="text-blue-100 font-bold uppercase tracking-[0.3em] text-xs mb-3">System Administration</span>
        <h2 class="text-white text-4xl font-black mb-6 max-w-lg leading-tight font-serif">Welcome back,<br/><span class="text-blue-200"><?php echo htmlspecialchars($_SESSION['username']); ?>!</span></h2>
        <div class="flex gap-4">
            <a href="pages/students.php" class="bg-white text-blue-600 px-8 py-3 rounded-xl font-bold shadow-lg shadow-black/20 hover:bg-blue-50 transition-all flex items-center gap-2 group/btn">
                <span>Manage Students</span>
                <span class="material-symbols-outlined text-xl group-hover/btn:translate-x-1 transition-transform">arrow_forward</span>
            </a>
            <a href="pages/reports.php" class="bg-blue-500/20 backdrop-blur-md text-white border border-white/20 px-8 py-3 rounded-xl font-bold hover:bg-blue-500/30 transition-all">System Reports</a>
        </div>
    </div>
</section>

<section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
    <!-- Stats Cards -->
    <div class="glass-card p-6 rounded-2xl">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center">
                <span class="material-symbols-outlined">group</span>
            </div>
            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Students</span>
        </div>
        <h3 class="text-3xl font-black text-secondary-dark"><?php echo number_format($totalStudents); ?></h3>
        <p class="text-xs text-slate-500 mt-1">Total enrolled</p>
    </div>
    
    <div class="glass-card p-6 rounded-2xl">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center">
                <span class="material-symbols-outlined">school</span>
            </div>
            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Courses</span>
        </div>
        <h3 class="text-3xl font-black text-secondary-dark"><?php echo number_format($totalCourses); ?></h3>
        <p class="text-xs text-slate-500 mt-1">Active courses</p>
    </div>

    <div class="glass-card p-6 rounded-2xl">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center">
                <span class="material-symbols-outlined">how_to_reg</span>
            </div>
            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Enrollments</span>
        </div>
        <h3 class="text-3xl font-black text-secondary-dark"><?php echo number_format($totalEnrollments); ?></h3>
        <p class="text-xs text-slate-500 mt-1">Active enrollments</p>
    </div>

    <div class="glass-card p-6 rounded-2xl">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center">
                <span class="material-symbols-outlined">payments</span>
            </div>
            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Revenue</span>
        </div>
        <h3 class="text-3xl font-black text-secondary-dark">PKR <?php echo number_format($paidFees); ?></h3>
        <p class="text-xs text-slate-500 mt-1">Fees collected</p>
    </div>
</section>

<section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
    <div class="glass-card p-6 rounded-2xl">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-purple-50 text-purple-600 rounded-xl flex items-center justify-center">
                <span class="material-symbols-outlined">person</span>
            </div>
            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Instructors</span>
        </div>
        <h3 class="text-3xl font-black text-secondary-dark"><?php echo number_format($totalInstructors); ?></h3>
        <p class="text-xs text-slate-500 mt-1">Teaching staff</p>
    </div>

    <div class="glass-card p-6 rounded-2xl">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-rose-50 text-rose-600 rounded-xl flex items-center justify-center">
                <span class="material-symbols-outlined">meeting_room</span>
            </div>
            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Classes</span>
        </div>
        <h3 class="text-3xl font-black text-secondary-dark"><?php echo number_format($totalClasses); ?></h3>
        <p class="text-xs text-slate-500 mt-1">Total sections</p>
    </div>

    <div class="glass-card p-6 rounded-2xl">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-cyan-50 text-cyan-600 rounded-xl flex items-center justify-center">
                <span class="material-symbols-outlined">quiz</span>
            </div>
            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Exams</span>
        </div>
        <h3 class="text-3xl font-black text-secondary-dark"><?php echo number_format($totalExams); ?></h3>
        <p class="text-xs text-slate-500 mt-1">Scheduled exams</p>
    </div>

    <div class="glass-card p-6 rounded-2xl">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-orange-50 text-orange-600 rounded-xl flex items-center justify-center">
                <span class="material-symbols-outlined">pending</span>
            </div>
            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Pending</span>
        </div>
        <h3 class="text-3xl font-black text-secondary-dark">PKR <?php echo number_format($pendingFees); ?></h3>
        <p class="text-xs text-slate-500 mt-1">Outstanding fees</p>
    </div>
</section>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-10">
    <section class="glass-card rounded-2xl overflow-hidden">
        <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between bg-white/50">
            <div>
                <h2 class="text-xl text-secondary-dark font-black font-serif">Recent Enrollments</h2>
                <p class="text-xs text-slate-400 font-bold uppercase tracking-wider mt-1">Latest course registrations</p>
            </div>
            <a href="pages/enrollments.php" class="text-primary text-xs font-black uppercase tracking-widest hover:underline">View All</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 text-slate-400 text-[10px] font-black uppercase tracking-[0.15em]">
                        <th class="px-8 py-5">Student</th>
                        <th class="px-8 py-5">Course</th>
                        <th class="px-8 py-5">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php if(empty($recentEnrollments)): ?>
                        <tr><td colspan="3" class="px-8 py-10 text-center text-slate-400 font-medium italic">No recent enrollments.</td></tr>
                    <?php else: ?>
                        <?php foreach($recentEnrollments as $enr): ?>
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-8 py-5">
                                <div class="font-bold text-secondary-dark"><?php echo htmlspecialchars($enr['first_name'] . ' ' . $enr['last_name']); ?></div>
                                <div class="text-[10px] text-slate-400 font-bold uppercase tracking-wider"><?php echo htmlspecialchars($enr['section']); ?></div>
                            </td>
                            <td class="px-8 py-5 text-sm text-slate-600 font-medium"><?php echo htmlspecialchars($enr['course_name']); ?></td>
                            <td class="px-8 py-5 text-xs text-slate-400 font-bold"><?php echo date('M d, Y', strtotime($enr['enrollment_date'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="glass-card rounded-2xl overflow-hidden">
        <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between bg-white/50">
            <div>
                <h2 class="text-xl text-secondary-dark font-black font-serif">New Students</h2>
                <p class="text-xs text-slate-400 font-bold uppercase tracking-wider mt-1">Recently added to system</p>
            </div>
            <a href="pages/students.php" class="text-primary text-xs font-black uppercase tracking-widest hover:underline">View All</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 text-slate-400 text-[10px] font-black uppercase tracking-[0.15em]">
                        <th class="px-8 py-5">Student Details</th>
                        <th class="px-8 py-5">Class Info</th>
                        <th class="px-8 py-5">Added</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php if(empty($latestStudents)): ?>
                        <tr><td colspan="3" class="px-8 py-10 text-center text-slate-400 font-medium italic">No students found.</td></tr>
                    <?php else: ?>
                        <?php foreach($latestStudents as $student): ?>
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-8 py-5">
                                <div class="font-bold text-secondary-dark"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></div>
                                <div class="text-[10px] text-slate-400 font-bold lowercase"><?php echo htmlspecialchars($student['email']); ?></div>
                            </td>
                            <td class="px-8 py-5">
                                <span class="bg-primary/5 text-primary px-3 py-1 rounded-lg text-xs font-black"><?php echo htmlspecialchars($student['class'] . ' - ' . $student['section']); ?></span>
                            </td>
                            <td class="px-8 py-5 text-xs text-slate-400 font-bold"><?php echo date('M d, Y', strtotime($student['created_at'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>

<?php require_once 'includes/footer.php'; ?>
