<?php
require_once '../includes/config.php';
require_once '../includes/db.php';

check_login();
if (!is_instructor()) {
    header("Location: " . BASE_URL . "/index.php");
    exit();
}

// Get instructor info
$instructor_id = $pdo->query("SELECT id FROM instructors WHERE user_id = {$_SESSION['user_id']}")->fetchColumn();

// Fetch instructor's classes
$classes = $pdo->query("
    SELECT c.*, co.course_name, co.course_code,
           COUNT(sce.student_id) as enrolled_students
    FROM classes c
    JOIN courses co ON c.course_id = co.id
    LEFT JOIN student_class_enrollment sce ON c.id = sce.class_id
    WHERE c.instructor_id = $instructor_id
    GROUP BY c.id
")->fetchAll();

// Fetch recent results entered
$recent_results = $pdo->query("
    SELECT er.*, s.first_name, s.last_name, e.exam_name, e.exam_type, co.course_name
    FROM exam_results er
    JOIN students s ON er.student_id = s.id
    JOIN exams e ON er.exam_id = e.id
    JOIN classes cl ON e.class_id = cl.id
    JOIN courses co ON cl.course_id = co.id
    WHERE er.entered_by = {$_SESSION['user_id']}
    ORDER BY er.created_at DESC
    LIMIT 5
")->fetchAll();

// Fetch upcoming assignments due
$upcoming_assignments = $pdo->query("
    SELECT a.*, c.course_name, cl.section
    FROM assignments a
    JOIN classes cl ON a.class_id = cl.id
    JOIN courses c ON cl.course_id = c.id
    WHERE cl.instructor_id = $instructor_id AND a.due_date >= CURDATE()
    ORDER BY a.due_date ASC
    LIMIT 5
")->fetchAll();

require_once '../includes/header.php';
?>

<section class="relative h-72 rounded-3xl overflow-hidden shadow-premium group mb-10">
    <div class="absolute inset-0 bg-gradient-to-r from-secondary-dark via-secondary-dark/60 to-transparent z-10"></div>
    <img src="<?php echo BASE_URL; ?>/assets/images/campus2.png" alt="Campus" class="absolute inset-0 w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110 z-0" onerror="this.style.display='none'">
    <div class="absolute inset-0 flex flex-col justify-center px-12 z-20">
        <span class="text-accent font-bold uppercase tracking-[0.3em] text-xs mb-3">Faculty Portal</span>
        <h2 class="text-white text-4xl font-black mb-6 max-w-lg leading-tight font-serif">Welcome back,<br/><span class="text-primary-light"><?php echo htmlspecialchars($_SESSION['username']); ?>!</span></h2>
        <div class="flex gap-4">
            <a href="courses.php" class="bg-primary text-white px-8 py-3 rounded-xl font-bold shadow-lg shadow-primary/20 hover:bg-primary-dark transition-all flex items-center gap-2 group/btn">
                <span>Manage My Classes</span>
                <span class="material-symbols-outlined text-xl group-hover/btn:translate-x-1 transition-transform">arrow_forward</span>
            </a>
            <a href="reports.php" class="bg-white/10 backdrop-blur-md text-white border border-white/20 px-8 py-3 rounded-xl font-bold hover:bg-white/20 transition-all">Teaching Reports</a>
        </div>
    </div>
</section>

<section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-10">
    <!-- Stats Cards -->
    <div class="glass-card p-8 rounded-2xl">
        <div class="flex items-center justify-between mb-6">
            <div class="w-12 h-12 bg-primary/10 text-primary rounded-xl flex items-center justify-center">
                <span class="material-symbols-outlined text-2xl">class</span>
            </div>
            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Teaching</span>
        </div>
        <p class="text-slate-500 text-xs font-bold uppercase tracking-wider mb-1">My Classes</p>
        <h3 class="text-3xl font-black text-secondary-dark"><?php echo count($classes); ?></h3>
    </div>

    <div class="glass-card p-8 rounded-2xl">
        <div class="flex items-center justify-between mb-6">
            <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center">
                <span class="material-symbols-outlined text-2xl">group</span>
            </div>
            <span class="text-[10px] font-black text-blue-500 uppercase tracking-widest">Active</span>
        </div>
        <p class="text-slate-500 text-xs font-bold uppercase tracking-wider mb-1">Total Students</p>
        <h3 class="text-3xl font-black text-secondary-dark">
            <?php echo array_sum(array_column($classes, 'enrolled_students')); ?>
        </h3>
    </div>

    <div class="glass-card p-8 rounded-2xl">
        <div class="flex items-center justify-between mb-6">
            <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center">
                <span class="material-symbols-outlined text-2xl">assignment</span>
            </div>
            <span class="text-[10px] font-black text-amber-500 uppercase tracking-widest">Pending</span>
        </div>
        <p class="text-slate-500 text-xs font-bold uppercase tracking-wider mb-1">Assignments</p>
        <h3 class="text-3xl font-black text-secondary-dark">
            <?php echo count($upcoming_assignments); ?>
        </h3>
    </div>

    <div class="glass-card p-8 rounded-2xl">
        <div class="flex items-center justify-between mb-6">
            <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center">
                <span class="material-symbols-outlined text-2xl">grade</span>
            </div>
            <span class="text-[10px] font-black text-emerald-500 uppercase tracking-widest">Recorded</span>
        </div>
        <p class="text-slate-500 text-xs font-bold uppercase tracking-wider mb-1">Results Entered</p>
        <h3 class="text-3xl font-black text-secondary-dark">
            <?php echo count($recent_results); ?>
        </h3>
    </div>
</section>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-10">
    <!-- My Classes -->
    <div class="lg:col-span-2 glass-card rounded-2xl overflow-hidden">
        <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between bg-white/50">
            <div>
                <h2 class="text-xl text-secondary-dark font-black font-serif">Teaching Schedule</h2>
                <p class="text-xs text-slate-400 font-bold uppercase tracking-wider mt-1">Courses assigned this semester</p>
            </div>
            <button class="text-primary text-xs font-black uppercase tracking-widest hover:underline">Manage All</button>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 text-slate-400 text-[10px] font-black uppercase tracking-[0.15em]">
                        <th class="px-8 py-5">Course Details</th>
                        <th class="px-8 py-5">Section</th>
                        <th class="px-8 py-5">Strength</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php if(empty($classes)): ?>
                        <tr><td colspan="3" class="px-8 py-10 text-center text-slate-400 font-medium italic">No classes assigned this semester.</td></tr>
                    <?php else: ?>
                        <?php foreach($classes as $class): ?>
                        <tr class="hover:bg-slate-50/80 transition-colors group">
                            <td class="px-8 py-5">
                                <div class="font-bold text-secondary-dark group-hover:text-primary transition-colors"><?php echo htmlspecialchars($class['course_code']); ?></div>
                                <div class="text-sm text-slate-500"><?php echo htmlspecialchars($class['course_name']); ?></div>
                            </td>
                            <td class="px-8 py-5">
                                <span class="bg-primary/5 text-primary px-3 py-1 rounded-lg text-xs font-black"><?php echo htmlspecialchars($class['section']); ?></span>
                            </td>
                            <td class="px-8 py-5">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-bold text-secondary-dark"><?php echo $class['enrolled_students']; ?></span>
                                    <span class="text-xs text-slate-400 font-medium uppercase tracking-tighter">Students</span>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="glass-card rounded-2xl overflow-hidden flex flex-col">
        <div class="px-8 py-6 border-b border-slate-100 bg-white/50">
            <h2 class="text-xl text-secondary-dark font-black font-serif">Recent Activity</h2>
            <p class="text-xs text-slate-400 font-bold uppercase tracking-wider mt-1">Latest updates from your classes</p>
        </div>
        <div class="p-6 space-y-4 flex-1">
            <?php if(empty($recent_results) && empty($upcoming_assignments)): ?>
                <div class="h-full flex flex-col items-center justify-center text-center p-8">
                    <span class="material-symbols-outlined text-4xl text-slate-200 mb-2">history</span>
                    <p class="text-slate-400 text-sm font-medium italic">No recent activity to show.</p>
                </div>
            <?php else: ?>
                <?php foreach($recent_results as $result): ?>
                <div class="flex items-start gap-4 p-4 rounded-xl hover:bg-slate-50 transition-colors group">
                    <div class="w-10 h-10 bg-emerald-50 text-emerald-600 rounded-xl flex-shrink-0 flex items-center justify-center">
                        <span class="material-symbols-outlined text-xl">grade</span>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-secondary-dark group-hover:text-primary transition-colors">Result Recorded</p>
                        <p class="text-[11px] text-slate-500 line-clamp-1"><?php echo htmlspecialchars($result['first_name'] . ' ' . $result['last_name']); ?> - <?php echo htmlspecialchars($result['course_name']); ?></p>
                        <p class="text-[9px] text-slate-400 font-black uppercase tracking-widest mt-1"><?php echo date('M d, h:i A', strtotime($result['created_at'])); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>

                <?php foreach($upcoming_assignments as $assignment): ?>
                <div class="flex items-start gap-4 p-4 rounded-xl hover:bg-slate-50 transition-colors group">
                    <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-xl flex-shrink-0 flex items-center justify-center">
                        <span class="material-symbols-outlined text-xl">assignment</span>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-secondary-dark group-hover:text-primary transition-colors">Assignment Due</p>
                        <p class="text-[11px] text-slate-500 line-clamp-1"><?php echo htmlspecialchars($assignment['title']); ?> (<?php echo htmlspecialchars($assignment['section']); ?>)</p>
                        <p class="text-[9px] text-primary font-black uppercase tracking-widest mt-1">DUE: <?php echo date('M d', strtotime($assignment['due_date'])); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="p-6 pt-0">
            <a href="reports.php" class="block w-full text-center py-3 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-black uppercase tracking-widest transition-colors">Activity History</a>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>