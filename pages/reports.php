<?php
require_once '../includes/config.php';
require_once '../includes/db.php';

check_login();

// 1. Count students per department
$studentsPerDept = $pdo->query("
    SELECT department, COUNT(*) as count 
    FROM students 
    GROUP BY department 
    ORDER BY count DESC
")->fetchAll();

// 2. Count enrollments per course
$enrollmentsPerCourse = $pdo->query("
    SELECT c.course_code, c.course_name, COUNT(e.student_id) as enrolled_count
    FROM courses c
    LEFT JOIN enrollments e ON c.id = e.course_id
    GROUP BY c.id
    ORDER BY enrolled_count DESC
")->fetchAll();

// 3. Querying the SQL View
$viewData = $pdo->query("
    SELECT * FROM v_student_course_summary 
    ORDER BY total_courses_enrolled DESC 
    LIMIT 10
")->fetchAll();

// 4. Subquery: Students taking more credits than the average
$subqueryData = $pdo->query("
    SELECT * FROM v_student_course_summary
    WHERE total_credits > (
        SELECT AVG(total_credits) FROM v_student_course_summary
    )
    ORDER BY total_credits DESC
")->fetchAll();

require_once '../includes/header.php';
?>

<div class="mb-6">
    <h1 class="text-2xl font-bold text-[#1a237e]">System Reports</h1>
    <p class="text-sm text-gray-500">Analytics and statistical summaries</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Students per Department -->
    <div class="glass-card p-6 rounded-xl shadow-sm">
        <h2 class="text-lg font-bold text-[#1a237e] mb-4 flex items-center gap-2">
            <span class="material-symbols-outlined">pie_chart</span> Students per Department
        </h2>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                        <th class="px-4 py-2">Department</th>
                        <th class="px-4 py-2 text-right">Student Count</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach($studentsPerDept as $row): ?>
                    <tr>
                        <td class="px-4 py-3 font-semibold text-gray-800"><?php echo htmlspecialchars($row['department']); ?></td>
                        <td class="px-4 py-3 text-right">
                            <span class="bg-indigo-100 text-[#1a237e] px-3 py-1 rounded-full font-bold"><?php echo $row['count']; ?></span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Enrollments per Course -->
    <div class="glass-card p-6 rounded-xl shadow-sm">
        <h2 class="text-lg font-bold text-[#1a237e] mb-4 flex items-center gap-2">
            <span class="material-symbols-outlined">bar_chart</span> Enrollments per Course
        </h2>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                        <th class="px-4 py-2">Course Code</th>
                        <th class="px-4 py-2">Course Name</th>
                        <th class="px-4 py-2 text-right">Enrollments</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach($enrollmentsPerCourse as $row): ?>
                    <tr>
                        <td class="px-4 py-3 font-bold text-[#1a237e]"><?php echo htmlspecialchars($row['course_code']); ?></td>
                        <td class="px-4 py-3 text-sm text-gray-700"><?php echo htmlspecialchars($row['course_name']); ?></td>
                        <td class="px-4 py-3 text-right">
                            <span class="bg-emerald-100 text-emerald-800 px-3 py-1 rounded-full font-bold"><?php echo $row['enrolled_count']; ?></span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Table Results -->
<div class="glass-card p-6 rounded-xl shadow-sm mb-6">
    <div class="mb-4">
        <h2 class="text-lg font-bold text-[#1a237e] flex items-center gap-2">
            <span class="material-symbols-outlined">table_view</span> Student Workload Summary
        </h2>
        <p class="text-xs text-gray-500 mt-1">Shows top 10 students based on course load.</p>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                    <th class="px-6 py-4">Student Name</th>
                    <th class="px-6 py-4">Department</th>
                    <th class="px-6 py-4">Total Courses Enrolled</th>
                    <th class="px-6 py-4">Total Credits</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach($viewData as $view): ?>
                <tr class="hover:bg-indigo-50/30 transition-colors">
                    <td class="px-6 py-4 font-semibold text-[#1a237e]"><?php echo htmlspecialchars($view['student_name']); ?></td>
                    <td class="px-6 py-4 text-gray-600"><?php echo htmlspecialchars($view['department']); ?></td>
                    <td class="px-6 py-4">
                        <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs font-bold"><?php echo $view['total_courses_enrolled']; ?> Courses</span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="bg-amber-100 text-amber-800 px-3 py-1 rounded-full text-xs font-bold"><?php echo $view['total_credits'] ?? 0; ?> Cr</span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Subquery Results -->
<div class="glass-card p-6 rounded-xl shadow-sm mb-6 border-l-4 border-emerald-500">
    <div class="mb-4">
        <h2 class="text-lg font-bold text-[#1a237e] flex items-center gap-2">
            <span class="material-symbols-outlined">psychology</span> Above Average Workload
        </h2>
        <p class="text-xs text-gray-500 mt-1">Students taking more credits than the university average.</p>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                    <th class="px-6 py-4">Student Name</th>
                    <th class="px-6 py-4">Department</th>
                    <th class="px-6 py-4">Total Credits</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if(empty($subqueryData)): ?>
                    <tr><td colspan="3" class="px-6 py-4 text-center text-gray-500">No students found.</td></tr>
                <?php else: ?>
                    <?php foreach($subqueryData as $subData): ?>
                    <tr class="hover:bg-emerald-50/30 transition-colors">
                        <td class="px-6 py-4 font-semibold text-[#1a237e]"><?php echo htmlspecialchars($subData['student_name']); ?></td>
                        <td class="px-6 py-4 text-gray-600"><?php echo htmlspecialchars($subData['department']); ?></td>
                        <td class="px-6 py-4">
                            <span class="bg-emerald-100 text-emerald-800 px-3 py-1 rounded-full text-xs font-bold"><?php echo $subData['total_credits']; ?> Cr</span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
