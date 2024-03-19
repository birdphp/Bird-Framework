<?php
// Waning Framework Configuration Edit with caution 

//###############################################$###$################$$$$$$$$3333333#######################################
//###############################################$###$################$$$$$$$$3333333#######################################
//###############################################$###$################$$$$$$$$3333333#######################################

error_reporting(0);

// secure header

// Usage:
// url::redirect('http://example.com');


class url {
    public static function redirect($url, $statusCode = 303) {
        $url = filter_var($url, FILTER_SANITIZE_URL);

        if ($statusCode != 302 && $statusCode != 303) {
            $statusCode = 303;
        }

        header('Location: ' . $url, true, $statusCode);
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('X-XSS-Protection: 1; mode=block');
        header('Content-Security-Policy: default-src \'self\'; script-src \'self\' https:; style-src \'self\' https:;');

        exit;
    }
}

// set cookie part edit with caution

/* Example usage:
cookie::setCookie('user_id', '123', time() + 3600); // Set a cookie with expiration time 1 hour from now
$userID = cookie::getCookie('user_id');
cookie::deleteCookie('user_id', '/', 'example.com', time() - 3600); // Delete the cookie with custom expiration time*/

class cookie {
    public static function setCookie($name, $value, $expiration = null, $path = '/', $domain = null, $secure = true, $httpOnly = true) {
        if ($expiration === null) {
            $expiration = time() + 3600; // Default expiration time: 1 hour
        }
        setcookie($name, $value, $expiration, $path, $domain, $secure, $httpOnly);
    }

    public static function getCookie($name) {
        if (isset($_COOKIE[$name])) {
            return $_COOKIE[$name];
        }
        return null;
    }

    public static function deleteCookie($name, $path = '/', $domain = null, $expiration = null) {
        if (isset($_COOKIE[$name])) {
            if ($expiration === null) {
                $expiration = time() - 3600; // Default expiration time: 1 hour ago
            }
            setcookie($name, '', $expiration, $path, $domain);
            unset($_COOKIE[$name]);
            return true;
        }
        return false;
    }
}

// Simple Template Engine edit with caution 

function templates($templateFile, $variables) {
    $templateDirectory = 'templates/'; // Default template directory

    // Append default template directory if no directory is specified in $templateFile
    if (strpos($templateFile, '/') === false) {
        $templateFile = $templateDirectory . $templateFile;
    }

    if (file_exists($templateFile)) {
        $templateContent = file_get_contents($templateFile);

        // Check if CSS and JS filenames are provided in the variables array
        $cssFile = isset($variables['css']) ? $variables['css'] : '';
        $jsFile = isset($variables['js']) ? $variables['js'] : '';

        // Replace CSS and JavaScript placeholders with actual file references
        $templateContent = str_replace("{css}", '<link rel="stylesheet" href="' . htmlspecialchars($cssFile) . '">', $templateContent);
        $templateContent = str_replace("{js}", '<script src="' . htmlspecialchars($jsFile) . '"></script>', $templateContent);

        foreach ($variables as $key => $value) {
            // Replace other placeholders with their corresponding values
            $templateContent = preg_replace("/\{\s*($key)\s*\}/", htmlspecialchars($value), $templateContent);
        }
        return $templateContent;
    } else {
        return "Error: Template file '$templateFile' could not be loaded. the default folder is templates/ check for templates folder ";
    }
}


//get and post

class input {
    public static function secureInput($input) {
        if (is_array($input)) {
            return array_map(array(__CLASS__, 'secureInput'), $input);
        }

        $input = trim($input);
        $input = htmlspecialchars($input, ENT_QUOTES | ENT_HTML5);

        return $input;
    }

    public static function post($key) {
        if (isset($_POST[$key])) {
            return self::secureInput($_POST[$key]);
        } else {
            return null;
        }
    }

    public static function get($key) {
        if (isset($_GET[$key])) {
            return self::secureInput($_GET[$key]);
        } else {
            return null;
        }
    }
}
// example usage
//$name = input::post("txt");
//$name2 = input::get("txt");


class session {
    public static function start_session($sessionName = 'MY_SESSION', $sessionLifetime = 3600, $sessionPath = '/', $sessionDomain = '', $sessionSecure = true, $sessionHttpOnly = true, $sessionRegenerate = true) {
        session_name($sessionName);
        session_set_cookie_params(
            $sessionLifetime,
            $sessionPath,
            $sessionDomain,
            $sessionSecure,
            $sessionHttpOnly
        );

        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        if ($sessionRegenerate) {
            session_regenerate_id(true);
        }
    }

    public static function delete_session() {
        session_destroy();
    }
}

// Usage:
// session::start_session();
// session::delete_session();



function uploadFile($file, $uploadDir, $allowedExtensions = [], $maxFileSize = 10485760) {
    $fileName = basename($file['name']);
    $fileSize = $file['size'];
    $fileTmp = $file['tmp_name'];
    $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    // Check file size
    if ($fileSize > $maxFileSize) {
        return false;
    }

    // Check file extension
    if (!empty($allowedExtensions) && !in_array($fileType, $allowedExtensions)) {
        return false;
    }

    // Generate unique filename
    $uniqueFileName = uniqid() . '.' . $fileType;

    // Move uploaded file to destination directory
    $destination = $uploadDir . '/' . $uniqueFileName;
    if (!@move_uploaded_file($fileTmp, $destination)) {
        return false;
    }

    return true;
}

class MaintenancePage {
    public function display() {
        // HTML content for the maintenance page
        echo "<!DOCTYPE html>
              <html lang='en'>
              <head>
                  <meta charset='UTF-8'>
                  <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                  <title>Site Under Maintenance</title>
                  <style>
                      body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
                      h1 { color: #333; }
                  </style>
              </head>
              <body>
                  <h1>page is Under Maintenance</h1>
                  <p>We apologize for any inconvenience. Please check back later.</p>
              </body>
              </html>";
        exit();
    }
}

function mode($enabled) {
    if ($enabled) {
        $maintenancePage = new MaintenancePage();
        $maintenancePage->display();
    }
}


class Img {
    public static function display($image) {
        if (is_file($image)) {
            $imageData = file_get_contents($image);
            $mimeType = mime_content_type($image);
        } else {
            // Assuming $image is blob data
            $imageData = $image;
            $mimeType = "image/png"; // Adjust MIME type as needed
        }
        $base64 = base64_encode($imageData);
        echo "<img src='data:$mimeType;base64,$base64' />";
    }
}

// audio part

class audio {
    public static function play($audios) {
        if (is_file($audios)) {
            $audioData = file_get_contents($audios);
            $mimeType2 = mime_content_type($audios);
        } else {
            // Assuming $image is blob data
            $audioData = $audios;
            $mimeType2 = "mp3/wav"; // Adjust MIME type as needed
        }
        $base64 = base64_encode($audioData);
        echo "<audio src='data:$mimeType2;base64,$base64' controls></audio>";
    }
}

// video part bird

class video {
    public static function play($videoss) {
        if (is_file($videoss)) {
            $videoData = file_get_contents($videoss);
            $mimeType3 = mime_content_type($videoss);
        } else {
            // Assuming $image is blob data
            $videoData = $videoss;
            $mimeType3 = "mp4/3gp"; // Adjust MIME type as needed
        }
        $base64 = base64_encode($videoData);
        echo "<video src='data:$mimeType3;base64,$base64' controls></video>";
    }
}

?>