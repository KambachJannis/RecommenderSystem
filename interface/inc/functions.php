<?php

/**
 * Alternate print_r command.
 *
 * @param mixed $data
 */
function pre($data)
{
    echo '<pre>';
    print_r($data);
    echo '</pre>';
}

/**
 * Redirects the user to the specified url.
 *
 * @param string $url
 * @param int $time
 */
function redirect($url, $time = 0)
{
    // Ensure the url.
    $url = $url? $url : $_SERVER['HTTP_REFERER'];

    // Check whether headers were sent.
    if(!headers_sent()){

        // Redirect via headers.
        if(!$time){
            header("Location: {$url}");
        }else{
            header("refresh: $time; {$url}");
        }

    }else{

        // Redirect via javascript.
        echo "<script> setTimeout(function(){ window.location = '{$url}' },". ($time*1000) . ")</script>";
    }
}

/**
 * Requires all files in the specific subdirectory.
 *
 * @param string $dir
 */
function _require_all($dir)
{
    $_not_included = [
        '/Database.php$/',
        '/DatabaseAbstraction.php$/',
    ];

    $scan = glob("$dir/*");
    foreach ($scan as $path) {

        foreach ($_not_included as $ignore)
        {
            if (preg_match($ignore, $path)) continue;
        }

        if (preg_match('/\.php$/', $path))
        {
            require_once $path;
            continue;
        }

        if (is_dir($path))
        {
            _require_all($path);
        }
    }
}

/**
 * Alternative array intersect.
 *
 * @param array $needles
 * @param array $haystack
 *
 * @return bool
 */
function in_array_any($needles, $haystack)
{
    return !empty(array_intersect($needles, $haystack));
}

/**
 * Prevents the resubmission of the current form.
 *
 * @return void
 */
function no_refresh()
{
    echo '<script>window.history.replaceState( null, null, window.location.href );</script>';
}

/**
 * @param $instance
 * @param $className
 *
 * @return mixed
 */
function objectToObject($instance, $className) {
    return unserialize(sprintf(
        'O:%d:"%s"%s',
        strlen($className),
        $className,
        strstr(strstr(serialize($instance), '"'), ':')
    ));
}

function curl_post_async($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 1);
    $result = curl_exec($ch);
    curl_close($ch);
}