<?php

session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin' && $_SESSION['user_role'] != 'master' && $_SESSION['user_role'] != 'user') {
    header("Location: ../index.php");
    exit();
}



include "../../modelo/conexion.php";

$enrolledUsersSql = "SELECT users.id, users.name, user_courses.enrollment_role FROM users JOIN user_courses ON users.id = user_courses.user JOIN courses ON user_courses.course = courses.course_id WHERE course_id = ?";
$enrolledUsersStmt = $conn->prepare($enrolledUsersSql);
$enrolledUsersStmt->bind_param('i', $_GET['cid']);
$enrolledUsersStmt->execute();
$enrolledUsersResult = $enrolledUsersStmt->get_result();
$enrolledUsers = [];

while ($row = $enrolledUsersResult->fetch_assoc()) {
    $enrolledUsers[] = $row;
}

?>

<h4 class="mt-2">Docentes</h4>
<div class="container border-top border-1"></div>
<ul class="list-group list-group-flush mt-1">
    <?php $teachersFound = false; ?>
    <?php foreach ($enrolledUsers as $row): ?>
        <?php if ($row['enrollment_role'] == 'TEACHER'): ?>
            <?php if ($row['id'] == $_SESSION['user_id']): ?>
                <li class="list-group-item">Tu</li>
            <?php else: ?>
                <li class="list-group-item"><?= $row['name'] ?></li>
            <?php endif; ?>

            <?php $teachersFound = true; ?>
        <?php endif; ?>
    <?php endforeach; ?>
    <?php if (!$teachersFound): ?>
        <li class="list-group-item">No hay docentes inscritos</li>
    <?php endif; ?>
</ul>
<h4 class="mt-3">Estudiantes</h4>
<div class="container border-top border-1"></div>
<ul class="list-group list-group-flush mt-1">
    <?php $studentsFound = false; ?>
    <?php foreach ($enrolledUsers as $row): ?>
        <?php if ($row['enrollment_role'] == 'STUDENT'): ?>
            <?php if ($row['id'] == $_SESSION['user_id']): ?>
                <li class="list-group-item">Tu</li>
            <?php else: ?>
                <li class="list-group-item"><?= $row['name'] ?></li>
            <?php endif; ?>
            <?php $studentsFound = true; ?>
        <?php endif; ?>
    <?php endforeach; ?>
    <?php if (!$studentsFound): ?>
        <li class="list-group-item">No hay estudiantes inscritos</li>
    <?php endif; ?>

</ul>