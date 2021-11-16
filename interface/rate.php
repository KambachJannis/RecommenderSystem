<?php

require_once __DIR__ . '/inc/core.php';

if (!isset($_POST) || !isset($_POST['recipe']) || !isset($_SESSION['user_id']))
{
    echo 'You need to be logged in.<br/><a href="random.php">Explore further.</a>';
    exit;
}

$recipe = $_POST['recipe'];
$rating = 0;

if (isset($_POST['star1_x']))
    $rating = 1;
else if (isset($_POST['star2_x']))
    $rating = 2;
else if (isset($_POST['star3_x']))
    $rating = 3;
else if (isset($_POST['star4_x']))
    $rating = 4;
else if (isset($_POST['star5_x']))
    $rating = 5;

Favorite::Rate($_SESSION['user_id'], $recipe, $rating);

redirect('details.php?id=' . $recipe);
exit;