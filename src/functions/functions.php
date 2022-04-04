<?php declare(strict_types = 1);
/**
 * @author    Luis Arturo Rodríguez
 * @copyright Copyright (c) 2021 Luis Arturo Rodríguez <rguezque@gmail.com>
 * @license   https://opensource.org/licenses/MIT    MIT License
 */

namespace Forge\functions;

use Forge\Exceptions\FileNotFoundException;
use Generator;

/**
 * Add a trailing slash
 * 
 * @param string $str A string
 * @return string
 */
function add_trailing_slash(string $str): string {
    return sprintf('%s/', remove_trailing_slash($str));
}

/**
 * Remove trailing slashes
 * 
 * @param string $str A string
 * @return string
 */
function remove_trailing_slash(string $str): string {
    return rtrim($str, '/\\');
}

/**
 * Add a leading slash
 * 
 * @param string $str A string
 * @return string
 */
function add_leading_slash(string $str): string {
    return sprintf('/%s', remove_leading_slash($str));
}

/**
 * Remove leading slashes
 * 
 * @param string $str A string
 * @return string
 */
function remove_leading_slash(string $str): string {
    return ltrim($str, '/\\');
}

/**
 * Return a string like namespace format slashes
 * 
 * @param string $namespace String namespace
 * @return string
 */
function namespace_format(string $namespace): string {
    return trim($namespace, '\\').'\\';
}

/**
 * Return true if a string has a specific prefix
 * 
 * @param string $haystack String to evaluate
 * @param string $needle Prefix to search
 * @return bool
 */
function str_starts_with(string $haystack, string $needle): bool {
    return $needle === substr($haystack, 0, strlen($needle));
}

/**
 * Return true if a string has a specific suffix
 * 
 * @param string $haystack String to evaluate
 * @param string $needle Suffix to search
 * @return bool
 */
function str_ends_with(string $haystack, string $needle): bool {
    return $needle === substr($haystack, -strlen($needle));
}

/**
 * Prepend strings to subject string
 * 
 * @param string $subject String subject
 * @param string $prepend String to prepend (first declared, first prepended)
 */
function str_prepend(string $subject, string ...$prepend): string {
    return implode('', array_reverse($prepend)).$subject;
}

/**
 * Append strings to subject string
 * 
 * @param string $subject String subject
 * @param string $append String to append
 */
function str_append(string $subject, string ...$append): string {
    return $subject.implode('', $append);
}

/**
 * Clean and prepare a string path
 * 
 * @param string $path String path
 * @return string
 */
function str_path(string $path): string {
    return add_leading_slash(remove_trailing_slash($path));
}

/**
 * Return true if the evaluated array is associative
 * 
 * @param mixed $value Value to evaluate
 * @return bool
 */
function is_assoc_array($value): bool {
    if(!is_array($value)) return false;
    if (array() === $value) return false;
    
    return array_keys($value) !== range(0, count($value) - 1);
}

/**
 * Reads entire json file into an associative array
 * 
 * @param string $file Json file path
 * @return array
 * @throws FileNotFoundException
 */
function json_file_get_contents(string $file): array {
    if(!file_exists($file)) {
        throw new FileNotFoundException(sprintf('The file %s wasn\'t found.', $file));
    }

    $contents = file_get_contents($file);
    return json_decode($contents, true);
}

/**
 * A generator function
 * 
 * If a string parameter 'stop' is sent through Generator::send() method, the generator stops.
 * 
 * @param array $haystack An array
 * @return Generator
 */
function generator(array $haystack): Generator {
    foreach($haystack as $key => $item) {
        $flag = @yield $key => $item;

        if('stop' === $flag) return;
    }
}

/**
 * Delete a cookie
 * 
 * @param string $name Cookie name
 * @return bool True on success, otherwise false
 */
function unsetcookie(string $name): bool {
    return setcookie($name, '', time()-3600);
}

/**
 * Returns true if two strings are equals, otherwise false
 * 
 * @param string $strone First string
 * @param string $strtwo Second string
 * @return bool
 */
function equals(string $strone, string $strtwo): bool {
    return strcmp($strone, $strtwo) === 0;
}

/**
 * Convert a string to PascalCase
 * 
 * @param string $str String to convert
 * @return string
 */
function str_to_pascalcase(string $str): string {
    $str = str_replace(['-','_'], ' ', $str);
    return implode('', array_map('ucfirst', explode(' ', strtolower($str))));
}

/**
 * Return true if a URl exists, otherwise false
 * 
 * @param string $url URL string
 * @return bool
 */
function url_exists(string $url): bool {
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_NOBODY, true);
    $result = curl_exec($curl);

    return $result !== false 
        ? (curl_getinfo($curl, CURLINFO_HTTP_CODE) !== 404) 
        : false;
}

/**
 * Generate a random string with a specific length
 * 
 * @param int $length Length for random string
 * @param bool $special_chars Include special characters
 * @param int $entropy Add entropy only for special characters
 * @return string
 */
function str_random($length = 20, $special_chars = true, int $entropy = 0): string {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $specials = '#$%&.@+~-^_';

    if(0 < $entropy) {
        $specials = str_shuffle(str_repeat($specials, $entropy));
    }
    if($special_chars) {
        $characters .= $specials;
    }

    return substr(str_shuffle($characters), 0, $length);
}

/**
 * Dump information from a variable into preformatted text for a 
 * better reading of its content and finish the current script.
 * 
 * @param mixed $var Variable to dump
 * @param bool $die If true terminate the script after dump
 * @return void
 */
function dd($var, bool $die = true): void {
    echo '<pre>';
    var_dump($var);
    echo '</pre>';
    
    if($die) {
        exit;
    }
}

/**
 * Generate URL-encoded query string
 * 
 * @param string $url URI to construct query
 * @param array $params Params to construct query
 * @return string
 */
function build_query(string $url, array $params): string {
    if(!strpos($url, 'https://') && !strpos($url, 'http://') && !strpos($url, 'www')) {
        $url = rtrim($url, '/\\').'/';
    }
    return $url.'?'.http_build_query($params);
}