<?php
session_start();

// Required includes
require_once 'includes/php/dc_connect.php';
require_once '_classes/class.database.php';
$objDB = new DB();
require_once 'includes/php/dc_config.php';

// Page specific includes
require_once '_classes/class.cart.php';
require_once 'includes/php/dc_functions.php';
require_once '_classes/class.password.php'; // Password compatibility library with PHP 5.5

// Start API
require_once 'libraries/Api_Inktweb/API.class.php';

if (!empty($_SESSION['customerId'])) {
    header('Location: ' . SITE_URL . '/dc_profile.php');
}

$_POST = sanitize($_POST);
$_GET = sanitize($_GET);

$strEmail = strtolower($_POST['email']);
$strPassword = $_POST['password'];

if (!empty($strEmail) AND !empty($strPassword)) {

    $strSQL = "SELECT password, id FROM " . DB_PREFIX . "customers WHERE LOWER(email) = '" . $strEmail . "' ";
    $result = $objDB->sqlExecute($strSQL);
    list($strPassHash, $userId) = $objDB->getRow($result);

    if (!empty($strPassHash)) {

        if (password_verify($strPassword, $strPassHash)) {
            // start session and redirect user
            $_SESSION['customerId'] = $userId;
            header('Location: ' . SITE_URL . '/dc_profile.php');

        } else {
            header('Location: ?fail=' . urlencode($text['WRONG_PASSWORD']));
        }
    } else {
        // email not found
        header('Location: ?fail=' . urlencode($text['UNKOWN_MAIL']));
    }
}

// Start displaying HTML
require_once 'includes/php/dc_header.php';
?>

<div class="row" style="margin-top:40px">
<div class="col-xs-12 col-sm-8 col-md-6 col-sm-offset-2 col-md-offset-3">

    <?php
if (!empty($_GET['fail'])) {
    echo '<div class="alert alert-warning"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong>'. $text['ERROR'] . '</strong> ' . $_GET['fail'] . '</div>';
}
?>

    <form role="form" method="post">
        <fieldset>
        <h2><?php echo $text['LOGIN_SUBTITLE']; ?></h2>

        <hr class="colorgraph">

        <div class="form-group">
            <input type="email" name="email" id="email" class="form-control input-lg" placeholder="<?php echo $text['INPUT_EMAIL']; ?>">
        </div><!-- /form-group -->

        <div class="form-group">
            <input type="password" name="password" id="password" class="form-control input-lg" placeholder="<?php echo $text['INPUT_PASSWORD']; ?>">
        </div><!-- /form-group -->

        <div class="row">
            <div class="col-xs-6 col-sm-6 col-md-6">
                <input type="submit" class="btn btn-lg btn-success btn-block" value="<?php echo $text['LOGIN_BUTTON']; ?>">
            </div><!-- /col -->
        </div><!-- /row -->

        <span class="button-checkbox">
            <a href="<?php echo SITE_URL?>/dc_password_reset.php" class="btn btn-link pull-right"><?php echo $text['FORGOT_PASSWORD']; ?></a>
        </span><!-- /button-checkbox -->

        </fieldset>
    </form>
</div><!-- /col -->
</div><!-- /row -->

<?php
require 'includes/php/dc_footer.php';
?>