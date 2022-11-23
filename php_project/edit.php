<?php
require_once "pdo.php";
require_once "util.php";
require_once "head.php";

session_start();

if ( ! isset($_SESSION['user_id']) ) {
    die('ACCESS DENIED');
}

if ( isset($_POST['cancel'])) {
    header('Location: index.php');
    return;
}

if ( ! isset($_REQUEST['profile_id'])) {
    $_SESSION['error'] = "Missing profile_id";
    header('Location: index.php');
    return;
}

$stmt = $pdo->prepare('SELECT * FROM profile WHERE profile_id = :prof AND user_id = :uid');
$stmt->execute(array(
    ':prof' => $_REQUEST['profile_id'],
    ':uid' => $_SESSION['user_id']));
$profile = $stmt->fetch(PDO::FETCH_ASSOC);


if( $profile === false) {
    $_SESSION['error'] = "Wrong User";
    header('Location: index.php');
    return;
}


if ( isset($_POST['first_name']) && isset($_POST['last_name']) && isset($_POST['email']) && isset($_POST['headline']) && isset($_POST['summary'])) {

    $msg = validateProfile();
    if( is_string($msg)) {   
        $_SESSION['error'] = $msg;
        header('Location: edit.php?profile_id='.$_REQUEST['profile_id'].'');
        return;
    }

    $msg = validateEdu();
    if( is_string($msg)) {
        $_SESSION['error'] = $msg;
        header('Location: edit.php?profile_id='.$_REQUEST['profile_id'].'');
        return;
    }

    $msg = validatePos();
    if( is_string($msg)) {
        $_SESSION['error'] = $msg;
        header('Location: edit.php?profile_id='.$_REQUEST['profile_id'].'');
        return;
    }


    $sql = "UPDATE profile SET first_name = :fn,
            last_name = :ln, email = :em,
            headline = :he, summary = :su
            WHERE profile_id = :pid AND user_id = :uid";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(
        ':pid' => $_REQUEST['profile_id'],
        ':uid' => $_SESSION['user_id'],
        ':fn' => $_POST['first_name'],
        ':ln' => $_POST['last_name'],
        ':em' => $_POST['email'],
        ':he' => $_POST['headline'],
        ':su' => $_POST['summary']));
    
    $stmt = $pdo->prepare('DELETE FROM position WHERE profile_id = :pid');
    $stmt->execute(array( ':pid' => $_REQUEST['profile_id']));
    
    insertPositions($pdo, $_REQUEST['profile_id']);
    
    $stmt = $pdo->prepare('DELETE FROM education WHERE profile_id = :pid');
    $stmt->execute(array( ':pid' => $_REQUEST['profile_id']));
    
    insertEducation($pdo, $_REQUEST['profile_id']);




    $_SESSION['success'] = 'Profile updated';
    header( 'Location: index.php' ) ;
    return;
}

$positions = loadPos($pdo, $_REQUEST['profile_id']);
$schools = loadEdu($pdo, $_REQUEST['profile_id']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Arthur Görzen</title>
    <link rel="stylesheet" href="//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-3.6.0.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
</head>
<body>
<div class="container">
<h1>Editing Profile for <?php echo $_SESSION['name']; ?></h1>
<?php flashMessages(); ?>
<form method="post" action="edit.php">
<input type="hidden" name="profile_id" value="<?= htmlentities($_GET['profile_id']); ?>"/>
<p>First Name: <input type="text" name="first_name" value="<?= htmlentities($profile['first_name']) ?>"></p>
<p>Last Name: <input type="text" name="last_name" value="<?= htmlentities($profile['last_name']) ?>"></p>
<p>Email: <input type="text" name="email" value="<?= htmlentities($profile['email']) ?>"></p>
<p>Headline: <input type="text" name="headline" value="<?= htmlentities($profile['headline']) ?>"></p>
<p>Summary:</br><textarea name="summary" rows="8" cols="80"><?= htmlentities($profile['summary']) ?></textarea>

<?php
$countEdu =0;
echo('<p>Education: <input type="submit" id="addEdu" value="+">'."\n");
echo('<div id="edu_fields">'."\n");
if (count($schools) > 0) {
    foreach( $schools as $schools) {
        $countEdu++;
        echo('<div id="edu'.$countEdu.'">'."\n");
        echo(
        '<p>Year: <input type="text" name="edu_year'.$countEdu.'" value="'.$schools['year'].'" /><input type="button" value="-" onclick="$(\'#edu'.$countEdu.'\').remove();return false;"></p>
        <p>School: <input type="text" size="80" name="edu_school'.$countEdu.'" class="school"
        value="'.htmlentities($schools['name']).'" />');
        echo "\n</div>\n";
    }
}
echo("</div></p>\n");

$countPos =0;
echo('<p>Position: <input type="submit" id="addPos" value="+">'."\n");
echo('<div id="position_fields">'."\n");
if (count($positions) > 0) {
    foreach( $positions as $positions ) {
        $countPos++;
        echo('<div class="position" id="position'.$countPos.'">');
        echo('<p>Year: <input type="text" name="year'.$countPos.'" value="'.htmlentities($positions['year']).'" /><input type="button" value="-" onclick="$(\'#position'.$countPos.'\').remove();return false;"><br>');

            echo '<textarea name="desc'.$countPos.'" rows="8" cols="80">'."\n";
            echo htmlentities($positions['description'])."\n";
            echo "\n</textarea>\n</div>\n";
    }
}
echo("</div></p>\n");
?>

<p><input type="submit" name="save" value="Save"/><input type="submit" name ="cancel" value="Cancel"/></p>
</form>
<script src="https://code.jquery.com/jquery-3.6.0.js"></script>  
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script> 
<script>

countPos = <?= $countPos ?>;
countEdu = <?= $countEdu ?>;

$(document).ready(function(){
    window.console && console.log('Document ready called');

    $('#addPos').click(function(event){
        event.preventDefault();
        if ( countPos >= 9 ) {
            alert("Maximum of nine position entries exceeded");
            return;
        }
        countPos++;
        window.console && console.log("Adding position "+countPos);

        $('#position_fields').append(
            '<div id="position'+countPos+'"> \
            <p>Year: <input type="text" name="year'+countPos+'" value="" /> \
            <input type="button" value="-" onclick="$(\'#position'+countPos+'\').remove();return false;"></p> \
            <textarea name="desc'+countPos+'" rows="8" cols="80"></textarea>\
            </div>');
    });

    $('#addEdu').click(function(event){
        event.preventDefault();
        if ( countEdu >= 9 ) {
            alert("Maximum of nine education entries exceeded");
            return;
        }
        countEdu++;
        window.console && console.log("Adding education "+countEdu);

        // Grab some HTML with hot spots and insert into the DOM, /is/g --> global match
        var source  = $("#edu-template").html();
        $('#edu_fields').append(source.replace(/@COUNT@/g,countEdu));

        // Add the event handler to the new ones
        $('.school').autocomplete({
            source: "school.php"
        });

    });

    $('.school').autocomplete({
        source: "school.php"
    });

});
</script>
<!--HTML with Substition @COUNT@-->
<script id="edu-template" type="text">
  <div id="edu@COUNT@">
    <p>Year: <input type="text" name="edu_year@COUNT@" value="" /><input type="button" value="-" onclick="$('#edu@COUNT@').remove();return false;"><br>
    <p>School: <input type="text" size="80" name="edu_school@COUNT@" class="school" value="" />
    </p>
  </div>
</script>
</div>
</body>
</html>