<?php

function createCourseCode($length)
{
    $randomBytes = random_bytes($length / 2);
    $courseCode = bin2hex($randomBytes);
    return substr($courseCode, 0, $length);
}
