<?php

/**
 * Class User
 *
 * NOTE:
 * THIS IS NOT A SECURE LOGIN SYSTEM!
 * DO NOT USE IT IN PRODUCTION!
 */
class User extends DatabaseAbstraction
{
    public static function Login($mail, $pass)
    {
        $mail = self::Filter($mail);
        $pass = self::Filter($pass);

        $query = "SELECT * FROM user WHERE mail_address LIKE '$mail' LIMIT 1;";
        $result = self::$Database->get_row($query, true);
        if (empty($result)) return null;

        $hash = crypt($pass, $result->password_hash);
        if ($hash != $result->password_hash) return null;

        return $result->userID;
    }

    public static function Register($mail, $pass1, $pass2)
    {
        $mail = self::Filter($mail);
        $pass1 = self::Filter($pass1);
        $pass2 = self::Filter($pass2);

        if ($pass1 != $pass2) return 'Not matching passwords!';

        $where = [ 'mail_address' => $mail ];
        if (self::$Database->exists('user', 'userID', $where))
            return 'Already registered!';

        $options = [ 'cost' => 12 ];
        $pass = password_hash($pass1, PASSWORD_BCRYPT, $options);

        $insert = [
            'mail_address' => $mail,
            'password_hash' => $pass
        ];
        self::$Database->insert('user', $insert);

        return true;
    }
}