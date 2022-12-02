<?php
namespace Phppot;

use Phppot\Captcha;
session_start();
require_once "./Model/Captcha.php";
$captcha = new Captcha();
$captcha_code = $captcha->getCaptchaCode(6);
$captcha->putSession('captcha_code', $captcha_code);
$imageData = $captcha->createCaptchaImage($captcha_code);
$captcha->renderCaptchaImage($imageData);
?>

<?php
namespace Phppot;

class Captcha
{

    function getCaptchaCode($length)
    {
        $random_alpha = md5(random_bytes(64));
        $captcha_code = substr($random_alpha, 0, $length);
        return $captcha_code;
    }

    function setSession($key, $value)
    {
        $_SESSION["$key"] = $value;
    }

    function getSession($key)
    {
        @session_start();
        $value = "";
        if (! empty($key) && ! empty($_SESSION["$key"])) {
            $value = $_SESSION["$key"];
        }
        return $value;
    }

    function createCaptchaImage($captcha_code)
    {
        $target_layer = imagecreatetruecolor(72, 28);
        $captcha_background = imagecolorallocate($target_layer, 204, 204, 204);
        imagefill($target_layer, 0, 0, $captcha_background);
        $captcha_text_color = imagecolorallocate($target_layer, 0, 0, 0);
        imagestring($target_layer, 5, 10, 5, $captcha_code, $captcha_text_color);
        return $target_layer;
    }

    function renderCaptchaImage($imageData)
    {
        header("Content-type: image/jpeg");
        imagejpeg($imageData);
    }

    function validateCaptcha($formData)
    {
        $isValid = false;
        $capchaSessionData = $this->getSession("captcha_code");

        if ($capchaSessionData == $formData) {
            $isValid = true;
        }
        return $isValid;
    }
}
?>
<?php
use Phppot\Captcha;
use Phppot\Contact;

require_once "./Model/Captcha.php";
$captcha = new Captcha();
if (count($_POST) > 0) {
    $userCaptcha = filter_var($_POST["captcha_code"], FILTER_SANITIZE_STRING);
    $isValidCaptcha = $captcha->validateCaptcha($userCaptcha);
    if ($isValidCaptcha) {
        
        $userName = filter_var($_POST["userName"], FILTER_SANITIZE_STRING);
        $userEmail = filter_var($_POST["userEmail"], FILTER_SANITIZE_EMAIL);
        $subject = filter_var($_POST["subject"], FILTER_SANITIZE_STRING);
        $content = filter_var($_POST["content"], FILTER_SANITIZE_STRING);
        
        require_once "./Model/Contact.php";
        $contact = new Contact();
        $insertId = $contact->addToContacts($userName, $userEmail, $subject, $content);
        if (! empty($insertId)) {
            $success_message = "Your message received successfully";
        }
    } else {
        $error_message = "Incorrect Captcha Code";
    }
}
?>