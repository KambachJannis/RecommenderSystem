<?php

require_once __DIR__ . '/inc/core.php';
require_once __DIR__ . '/inc/_template/head_start.php';

if (!isset($_GET['id']))
{
    redirect('random.php');
    exit;
}

$recipe = Recipe::Fetch($_GET['id']);
if ($recipe == null)
{
    redirect('random.php');
    exit;
}

$user_based = Favorite::Recommend($_GET['id']);

$rating = 0;
if (isset($_SESSION['user_id']))
{
    $result = Favorite::GetRating($_SESSION['user_id'], $_GET['id']);
    if ($result != null) $rating = $result->rating;
}

?>
<link href="css/base.css" rel="stylesheet">

<?php require_once __DIR__ . '/inc/_template/head_end.php'; ?>
<?php require_once __DIR__ . '/inc/_template/navigation.php'; ?>

<div class="container">

    <div class="text-center">
        <h2 class="mt-4 mb-0"><?=$recipe->title;?></h2>
        <h5 class="mt-0 mb-4"><?=!empty($recipe->cuisine) ? implode($recipe->cuisine) : '';?> - <?=!empty($recipe->course) ? implode($recipe->course) : '';;?></h5>
    </div>

    <div class="row">

        <div class="col-md-4">
            <div class="row">
                <div class="col-12 ">
                    <img class="img-fluid" src="<?=$recipe->thumbnail;?>" alt="">
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <h5 style="margin-top: 25px;">Rate this recipe:</h5>
                    <div style="margin-left: 50px;">
                        <form action="rate.php" method="post">
                            <input type="hidden" name="recipe" value="<?=$_GET['id'];?>">
                            <input type="image" src="img/star_<?=$rating >= 1 ? 'yellow' : 'gray';?>.png" name="star1" alt="" style="width: 20px;" />
                            <input type="image" src="img/star_<?=$rating >= 2 ? 'yellow' : 'gray';?>.png" name="star2" alt="" style="width: 20px;" />
                            <input type="image" src="img/star_<?=$rating >= 3 ? 'yellow' : 'gray';?>.png" name="star3" alt="" style="width: 20px;" />
                            <input type="image" src="img/star_<?=$rating >= 4 ? 'yellow' : 'gray';?>.png" name="star4" alt="" style="width: 20px;" />
                            <input type="image" src="img/star_<?=$rating >= 5 ? 'yellow' : 'gray';?>.png" name="star5" alt="" style="width: 20px;" />
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <h4 class="my-3">General</h4>
            <ul>
                <li>Number of Servings: <?= $recipe->number_of_servings != 0 ? $recipe->number_of_servings : "Unknown"; ?></li>
                <li>Time: <?= $recipe->time != '' ? $recipe->time  : "Unknown"; ?></li>
                <li>Rating: <?= $recipe->rating != 0 ? $recipe->rating . '/5' : "Unknown"; ?></li>
            </ul>

            <?php if ($recipe->instructions != "") { ?>
                <h4 class="my-3">Instruction</h4>
                <p><?=$recipe->instructions;?></p>
            <?php } ?>

            <?php
            if (!empty($recipe->flavors)) {
                echo '<h4 class="my-3">Flavors</h4>';
                echo '<ul>';
                foreach ($recipe->flavors as $key => $value)
                {
                    if ($value == 0) continue;
                    echo '<li>' . ($value == 1 ? $key : $key . ': ' . round($value * 100, 0) . '%') . '</li>';
                }
                echo '</ul>';
            }
            ?>

            <?= $recipe->source != '' ? '<h4>Source</h4><p><a href="'.$recipe->source.'">' . $recipe->source . '</a></p>' : ''; ?>

        </div>

        <div class="col-md-4">
            <h3 class="my-3">Ingredients</h3>
            <ul>
                <?php foreach ($recipe->ingredients as $ingredient) { ?>
                    <li><?=$ingredient;?></li>
                <?php } ?>
            </ul>
        </div>

    </div>

    <hr>
    <h3 class="my-3">Recommendations</h3>

    <div id="recommendations"  style="display: none;">

        <div class="row" id="content_based">
        </div>

        <hr>
        <div class="row">
            <div class="offset-2 col-4 portfolio-item">
                <div class="card h-100">
                    <div class="card-header bg-dark text-white text-center">Users who liked this, also liked:</div>
                    <a href="details.php?id=<?=$user_based->id;?>"><img class="card-img-top" src="<?=$user_based->thumbnail;?>" alt=""></a>
                    <div class="card-body">
                        <h5 style="margin-bottom: 5px;">
                            <a href="details.php?id=<?=$user_based->id;?>"><?=$user_based->title;?></a>
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
            </div>
            <div class="col-4 portfolio-item" id="user_based">
            </div>
        </div>
    </div>

    <div class="row" id="waiting_spinner">
        <div class="offset-4 col-4 text-center">
            <div class="spinner-border" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/inc/_template/footer_start.php'; ?>

<script>

    $.ajax
    ({
        type: "GET",
        url: "content_based.php?id=<?=$_GET['id']?>",
        success: function(html)
        {
            $('#content_based').html(html);
            $('#recommendations').css('display', 'block');
        }
    });

    $.ajax
    ({
        type: "GET",
        url: "user_based.php?id=<?=isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;?>",
        success: function(html)
        {
            $('#user_based').html(html);
            $('#waiting_spinner').css('display', 'none');
        }
    });

</script>

<?php
require_once __DIR__ . '/inc/_template/footer_end.php';