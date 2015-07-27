<?php
// Required includes
require_once __DIR__ . '/../includes/php/dc_connect.php';
require_once __DIR__ . '/../_classes/class.database.php';
$objDB = new DB();
require_once __DIR__ . '/../beheer/includes/php/dc_config.php';

// Page specific includes
require_once __DIR__ . '/../beheer/includes/php/dc_functions.php';
require_once __DIR__ . '/../_classes/class.password.php'; // Password compatibility library with PHP 5.5

$objDB = new DB();

$_POST = sanitize($_POST);
$_GET = sanitize($_GET);

$intId = (isset($_GET['id'])) ? $_GET['id'] : null;
$strAction = (isset($_GET['action'])) ? $_GET['action'] : null;

if ($strAction == "remove" AND !empty($intId)) {
    $objDB->sqlDelete('admin_users', 'id', $intId);
    header('Location: ' . SITE_URL . '/beheer/dc_user_admin.php?succes=' . urlencode('De gebruiker is verwijderd.'));
}

$strSQL = "SELECT au.name, au.email, au.username, au.password FROM " . DB_PREFIX . "admin_users au WHERE au.id = '" . $intId . "' ";
$result = $objDB->sqlExecute($strSQL);
$objUser = $objDB->getObject($result);

if ($_SERVER['REQUEST_METHOD'] == "POST") {

    $strName = (isset($_POST['name'])) ? $_POST['name'] : null;
    $strEmail = (isset($_POST['email'])) ? $_POST['email'] : null;
    $strUsername = (isset($_POST['username'])) ? $_POST['username'] : null;
    $strPassword1 = (isset($_POST['password1'])) ? $_POST['password1'] : null;
    $strPassword2 = (isset($_POST['password2'])) ? $_POST['password2'] : null;
    $strPassword = (isset($objUser->password)) ? $objUser->password : null; // gets overwritten if user wants to change

    if (!empty($strPassword1) AND !empty($strPassword2)) {

        if ($strPassword1 == $strPassword2) {

            // Hash password with bcrypt and default cost
            $strPassword = password_hash($strPassword1, PASSWORD_BCRYPT);

        } else {
            header('Location: ?id=' . $intId . '&action=' . $strAction . '&fail=' . urlencode('Wachtwoorden komen niet overeen.'));
        }

    }

    if (empty($intId)) {
        $strSQL = "INSERT INTO " . DB_PREFIX . "admin_users
                (name, email, username, password)
                VALUES
                ( '" . $strName . "',  '" . $strEmail . "',  '" . $strUsername . "',  '" . $strPassword . "')
                ";
        $result = $objDB->sqlExecute($strSQL);
        $intId = $objDB->getInsertedId($result);

        if ($result === true) {
            header('Location: ?id=' . $intId . '&action=' . $strAction . '&succes=' . urlencode('De gebruiker is aangemaakt.'));
        } else {
            header('Location: ?id=' . $intId . '&action=' . $strAction . '&fail=' . urlencode('Er is iets fout gegaan.'));
        }
    } else {
        $strSQL = "UPDATE " . DB_PREFIX . "admin_users
                SET name = '" . $strName . "',
                email = '" . $strEmail . "',
                username = '" . $strUsername . "',
                password = '" . $strPassword . "'
                WHERE id = '" . $intId . "' ";
        $result = $objDB->sqlExecute($strSQL);

        if ($result === true) {
            header('Location: ?id=' . $intId . '&action=' . $strAction . '&succes=' . urlencode('De gebruiker is bijgewerkt.'));
        } else {
            header('Location: ?id=' . $intId . '&action=' . $strAction . '&fail=' . urlencode('Er is iets fout gegaan.'));
        }
    }

}

require 'includes/php/dc_header.php';

if (!empty($_GET['succes'])) {
    echo '<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong>Gelukt!</strong> ' . $_GET['succes'] . '</div>';
}

if (!empty($_GET['fail'])) {
    echo '<div class="alert alert-warning"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong>Fout!</strong> ' . $_GET['fail'] . '</div>';
}

?>

<h1>Gebruiker beheren <small><?php echo (isset($objUser->name)) ? $objUser->name : null;?></small></h1>

<hr />

<form role="form" class="form-horizontal" method="POST">

    <div class="form-group">
        <label for="name" class="col-sm-2 control-label">Naam</label>
        <div class="col-sm-10">
            <input type="text" class="form-control" id="name" name="name" value="<?php echo (isset($objUser->name)) ? $objUser->name : null;?>" autocomplete="off" <?php if (empty($intId)) {echo 'required';}
?>>
            <p class="help-block">Voor intern gebruik</p>
        </div><!-- /col -->
    </div><!-- /form group -->

    <div class="form-group">
        <label for="username" class="col-sm-2 control-label">Gebruikersnaam</label>
        <div class="col-sm-10">
            <input type="text" class="form-control" id="username" name="username" value="<?php echo (isset($objUser->username)) ? $objUser->username : null;?>" autocomplete="off" <?php if (empty($intId)) {echo 'required';}
?>>
            <p class="help-block">Wordt gebruikt om mee in te loggen, niet hoofdletterrgevoelig.</p>
        </div><!-- /col -->
    </div><!-- /form group -->

    <div class="form-group">
        <label for="email" class="col-sm-2 control-label">Email</label>
        <div class="col-sm-10">
            <input type="email" class="form-control" id="email" name="email" value="<?php echo (isset($objUser->email)) ? $objUser->email : null;?>" autocomplete="off" <?php if (empty($intId)) {echo 'required';}
?>>
        </div><!-- /col -->
    </div><!-- /form group -->

    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10">
        <button type="submit" class="btn btn-primary">Gebruiker aanpassen</button>
        </div><!-- /col -->
    </div><!-- /form group -->

    <hr />

    <div class="form-group">
        <label for="password1" class="col-sm-2 control-label">Wachtwoord resetten</label>
        <div class="col-sm-10">
            <input type="password" class="form-control" id="password1" name="password1" placeholder="Wachtwoord.." autocomplete="off" <?php if (empty($intId)) {echo 'required';}
?>>
            <br />
            <input type="password" class="form-control" id="password2" name="password2" placeholder="Wachtwoord herhalen..." autocomplete="off" <?php if (empty($intId)) {echo 'required';}
?>>
            <p class="help-block">Leeg laten indien u het wachtwoord niet wenst te resetten</p>
        </div><!-- /col -->
    </div><!-- /form group -->



    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10">
        <button type="submit" class="btn btn-primary">Gebruiker aanpassen</button>
        </div><!-- /col -->
    </div><!-- /form group -->

<hr />


<script type="text/javascript" src="<?php echo SITE_URL?>/beheer/includes/script/jquery.pagedown-bootstrap.combined.min.js"></script>
<script type="text/javascript">
(function () {

    $("textarea#pagedownMe").pagedownBootstrap();

})();
</script>
<?php require 'includes/php/dc_footer.php';?>