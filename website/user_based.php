<?php

require_once __DIR__ . '/inc/core.php';

if (!isset($_GET['id']))
    exit("500");

$success = false;

$json = $_GET['id'] == 0 ? false : @file_get_contents('http://recommender-service/user?user=' . $_GET['id']);
if ($json !== false)
{
    $recommendations = explode(',' , json_decode($json)->recipe);
    array_pop($recommendations);
    $id = $recommendations[rand(0, count($recommendations) - 1)];
    $recipe = Recipe::Fetch((int) $id);
    $success = true;
}
else
{
    // Emergency-Backup:
    $recipe = Favorite::RecommendRandom();
}

?>

<div class="card h-100">
    <div class="card-header bg-dark text-white text-center"><?= $success ? 'Based on your likes:' : 'You might like this one:'; ?></div>
    <a href="details.php?id=<?=$recipe->id;?>"><img class="card-img-top" src="<?=$recipe->thumbnail;?>" alt=""></a>
    <div class="card-body">
        <h5 style="margin-bottom: 5px;">
            <a href="details.php?id=<?=$recipe->id;?>"><?=$recipe->title;?></a>
        </h5>
        <p style="margin: 0;">
            <?php
            echo !empty($recipe->cuisine) ? implode(', ', $recipe->cuisine) : '';
            if (!empty($recipe->cuisine) && !empty($recipe->course)) echo ' - ';
            echo !empty($recipe->course) ? implode(', ', $recipe->course) : '';
            ?>
        </p>
    </div>
</div>