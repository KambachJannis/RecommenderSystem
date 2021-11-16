<?php

class Recipe extends DatabaseAbstraction
{
    public static function Fetch($id)
    {
        $id = self::filter($id);

        $query = "SELECT 
                    recipeID, title, instructions, ingredients, source, du_reference,
                    categories, rating, cuisine, flavors, duplicate, course, numberOfservings,
                    yield, thumbnailUrl, picture_link, totalTime, totalTimeInSeconds
                  FROM all_merged_dataset_with_id_copy_and_priority 
                  WHERE recipeID = '$id'
                  LIMIT 1;";

        $result = self::$Database->get_row($query, true);
        if (empty($result)) return null;
        if ($result->duplicate != 0) return self::Fetch($result->du_reference);

        $recipe = new Objects\Recipe();

        $recipe->id = $result->recipeID;
        $recipe->title = $result->title;
        $recipe->instructions = $result->instructions;
        $recipe->source = $result->source;
        $recipe->categories = json_decode($result->categories);
        $recipe->flavors = (array)json_decode($result->flavors);
        $recipe->cuisine = json_decode($result->cuisine);
        $recipe->rating = $result->rating;
        $recipe->course = json_decode($result->course);
        $recipe->number_of_servings = $result->numberOfservings;
        $recipe->yield = $result->yield;
        $recipe->thumbnail = $result->thumbnailUrl != '' ? $result->thumbnailUrl : 'img/no_image.png';
        $recipe->picture = $result->picture_link;

        $recipe->ingredients = [];
        $ingredients = json_decode($result->ingredients);
        foreach ($ingredients as $ingredient)
        {
            if (in_array($ingredient, $recipe->ingredients)) continue;
            $recipe->ingredients[] = $ingredient;
        }


        if (!empty($result->totalTimeInSeconds))
        {
            $hours = floor($result->totalTimeInSeconds / 3600);
            $mins = floor($result->totalTimeInSeconds / 60 % 60);
            $recipe->time = sprintf('%02d:%02d:00', $hours, $mins);
        }

        return $recipe;
    }
}