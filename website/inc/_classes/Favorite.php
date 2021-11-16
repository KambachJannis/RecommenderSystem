<?php

class Favorite extends DatabaseAbstraction
{
    public static function Recommend($id)
    {
        $id = self::Filter($id);

        $query = "SELECT GROUP_CONCAT(h.userID) as users
                  FROM user_history h
                  WHERE h.recipeID = $id && h.rating >= 4
                  GROUP BY h.recipeID
                  LIMIT 1;";
        $result = self::$Database->get_row($query, true);
        if (empty($result) || empty($result->users)) return self::RecommendRandom();

        $ids = $result->users;
        $query = "SELECT h.recipeID, SUM(h.rating) as rating
                  FROM user_history h
                  WHERE h.userID IN ($ids) && h.recipeID != $id && h.rating >= 3
                  GROUP BY h.recipeID
                  ORDER BY rating DESC;";
        $results = self::$Database->get_results($query, true);
        if (empty($results)) return self::RecommendRandom();

        $max[] = $results[0];
        foreach ($results as $result)
        {
            if ($result->rating != $results[0]->rating) break;
            $max[] = $result;
        }

        $random = $results[rand(0, count($max) - 1)]->recipeID;
        return Recipe::Fetch($random);
    }

    public static function RecommendRandom()
    {
        $query = "SELECT recipeID FROM all_merged_dataset_with_id_copy_and_priority ORDER BY RAND() LIMIT 1;";
        $result = self::$Database->get_row($query, true);
        return Recipe::Fetch($result->recipeID);
    }

    public static function Rate($user, $recipe, $rating)
    {
        $user = self::Filter($user);
        $recipe = self::Filter($recipe);
        $rating = self::Filter($rating);

        $where = ['userID' => $user];
        if (self::$Database->exists('user_history', 'userID', $where))
        {
            $update = ['rating' => $rating];
            self::$Database->update('user_history', $update, $where, 1);
        }
        else
        {
            $insert = [
                'userID' => $user,
                'recipeID' => $recipe,
                'rating' => $rating
            ];
            self::$Database->insert('user_history', $insert);
        }

        curl_post_async('http://recommender-service/usersave?recipe=' . $recipe . '&user=' . $user . '&rating=' . $rating);
    }

    public static function GetRating($user, $recipe)
    {
        $user = self::Filter($user);
        $recipe = self::Filter($recipe);

        $query = "SELECT * FROM user_history WHERE userID = $user && recipeID = $recipe;";
        $result = self::$Database->get_row($query, true);

        return $result;
    }
}