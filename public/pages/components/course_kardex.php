<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin' && $_SESSION['user_role'] != 'master' && $_SESSION['user_role'] != 'user') {
    header("Location: ../index.php");
    exit();
}

include "../../modelo/conexion.php";

$grades = [];
$students = [];

if ($_SESSION['user_role'] == "user") {
    $sql = "SELECT
        a.assignment_name,
        MAX(saf.score) AS score,
        a.max_score,
        MAX(saf.status) AS status,
        MAX(saf.submitted_at) AS submitted_at
    FROM
        submissions_assignments_files saf
    JOIN
        assignments a ON saf.assignment = a.assignment_id
    JOIN topics t ON a.topic = t.topic_id
    WHERE
        saf.student = ? AND t.course = ?
    GROUP BY
        a.assignment_name, a.max_score;";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $_SESSION['user_id'], $_GET['cid']);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $grades[] = $row;
    }
} else if ($_SESSION['user_role'] == "master") {
    $sql = "SELECT
    u.name AS student_name,
    a.assignment_name,
    MAX(saf.score) AS score,
    a.max_score,
    MAX(saf.status) AS status,
    MAX(saf.submitted_at) AS submitted_at
    FROM
        submissions_assignments_files saf
    JOIN
        assignments a ON saf.assignment = a.assignment_id
    JOIN
        users u ON saf.student = u.id
    JOIN
        topics t ON a.topic = t.topic_id
    WHERE
        t.course = ?
    GROUP BY
        u.name, a.assignment_name, a.max_score;";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $_GET['cid']);
    $stmt->execute();
    $result = $stmt->get_result();
    $grades = [];
    while ($row = $result->fetch_assoc()) {
        $grades[] = $row;
    }

    $students = [];
    foreach ($grades as $grade) {
        $studentName = $grade['student_name'];
        if (!isset($students[$studentName])) {
            $students[$studentName] = [];
        }
        $students[$studentName][] = $grade;
    }
}
?>

<?php if ($_SESSION['user_role'] == "user"): ?>
    <div class="py-2">
        <h2>Calificaciones por tarea</h2>
        <div class="border-bottom border-1 mb-2"></div>
        <?php if (count($grades) > 0): ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">Tarea</th>
                            <th scope="col">Estudiante</th>
                            <th scope="col">Calificación</th>
                            <th scope="col">Entregado el</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($grades as $grade): ?>
                            <tr>
                                <td><strong><?= $grade['assignment_name'] ?></strong></td>

                                <td><?php echo ($grade['score'] == -1) ?  "-" : $grade['score'] ?> / <?= $grade['max_score'] ?></td>
                                <?php if ($grade['status'] == 'SCORED'): ?>
                                    <td class="text-success"><?= "Calificado" ?></td>
                                <?php elseif ($grade['status'] == 'SUBMITTED'): ?>
                                    <td><?= "Entregado" ?></td>
                                <?php elseif ($grade['status'] == 'SUBMITTED_LATE'): ?>
                                    <td><?= "Entregado Tarde" ?></td>
                                <?php endif; ?>
                                <td><?= $grade['submitted_at'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <h5>Aun no hay entregas</h5>
        <?php endif; ?>
    </div>
<?php elseif ($_SESSION['user_role'] == "master"): ?>
    <div class="py-2">
        <h2>Calificaciones por Estudiante</h2>
        <div class="border-bottom border-1 mb-2"></div>
        <?php if (count($students) > 0): ?>
            <div class="accordion accordion-flush" id="studentsAccordion">
                <?php $accordionIndex = 1;
                foreach ($students as $studentName => $studentGrades): ?>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading<?= $accordionIndex ?>">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $accordionIndex ?>" aria-expanded="false" aria-controls="collapse<?= $accordionIndex ?>">
                                <?= $studentName ?>
                            </button>
                        </h2>
                        <div id="collapse<?= $accordionIndex ?>" class="accordion-collapse collapse" aria-labelledby="heading<?= $accordionIndex ?>" data-bs-parent="#studentsAccordion">
                            <div class="accordion-body">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th scope="col">Tarea</th>
                                                <th scope="col">Estudiante</th>
                                                <th scope="col">Calificación</th>
                                                <th scope="col">Entregado el</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($studentGrades as $grade): ?>
                                                <tr>
                                                    <td><?= $grade['assignment_name'] ?></td>

                                                    <td><?php echo ($grade['score'] == -1) ?  "-" : $grade['score'] ?> / <?= $grade['max_score'] ?></td>
                                                    <?php if ($grade['status'] == 'SCORED'): ?>
                                                        <td><?= "Calificado" ?></td>
                                                    <?php elseif ($grade['status'] == 'SUBMITTED'): ?>
                                                        <td><?= "Entregado" ?></td>
                                                    <?php elseif ($grade['status'] == 'SUBMITTED_LATE'): ?>
                                                        <td><?= "Entregado Tarde" ?></td>
                                                    <?php endif; ?>
                                                    <td><?= $grade['submitted_at'] ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php $accordionIndex++;
                endforeach; ?>
            </div>
        <?php else: ?>
            <h5>Aún no hay envíos</h5>
        <?php endif; ?>
    </div>
<?php endif; ?>