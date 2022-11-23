<?php
require_once "pdo.php";
require_once "head.php";

session_start();

if ( ! isset($_SESSION['name']) ) {
    die('ACCESS DENIED');
}

if ( isset($_POST['cancel'])) {
    header('Location: index.php');
    return;
}

if(! isset($_GET['profile_id'])) {
    $_SESSION['error'] = "Could not load profile";
    header('Location: index.php');
    return;
}

$stmt = $pdo->prepare("SELECT first_name, last_name, profile_id, user_id FROM profile WHERE profile_id =:xyz");
$stmt->execute(array(":xyz" => $_GET['profile_id']));
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if($row === false) {
    $_SESSION['error'] ="Could not load profile";
    header('Location: index.php');
    return;
}

if(isset($_POST['delete']) && isset($_POST['profile_id'])) {
    if ($_SESSION['user_id'] != $row['user_id']) {
        $_SESSION['error'] = 'Wrong User';
        header('Location: index.php');
        return;
    }
    else{
    $sql = "DELETE FROM profile where profile_id = :zip";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(':zip' => $_POST['profile_id']));
    $_SESSION['success'] = 'Profile deleted';
    header('Location: index.php');
    return;
    }
}

?>
<!DOCTYPE html>
<html>
<head>
<title>Arthur Görzen</title>
</head>
<body>
<div class="container">
<h1>Deleting Profile</h1>
<?php
echo("<div><p>First Name: ");
echo(htmlentities($row['first_name']));
echo("</p>\n");
echo("<p>Last Name: ");
echo(htmlentities($row['last_name']));
echo("</p>\n</div>");
?>
<form method="post">
    <input type="hidden" name="profile_id" value="<?= $row['profile_id'] ?>">
    <input type="submit" value="Delete" name="delete">
    <input type="submit" name ="cancel" value="Cancel"/>
</form>
</div>
</body>
</html>