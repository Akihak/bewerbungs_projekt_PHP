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

if ( isset($_POST['first_name']) && isset($_POST['last_name']) && isset($_POST['email']) && isset($_POST['headline']) && isset($_POST['summary'])){

    $msg = validateProfile();
    if ( is_string($msg)) {
        $_SESSION['error'] = $msg;
        header('Location: add.php');
        return;
    }

    $msg = validateEdu();
    if ( is_string($msg)) {
        $_SESSION['error'] = $msg;
        header('Location: add.php');
        return;
    }

    $msg = validatePos();
    if ( is_string($msg)) {
        $_SESSION['error'] = $msg;
        header('Location: add.php');
        return;
    }
    
    $stmt = $pdo->prepare('INSERT INTO Profile
    (user_id, first_name, last_name, email, headline, summary)
    VALUES ( :uid, :fn, :ln, :em, :he, :su)');
  
    $stmt->execute(array(
        ':uid' => $_SESSION['user_id'],
        ':fn' => $_POST['first_name'],
        ':ln' => $_POST['last_name'],
        ':em' => $_POST['email'],
        ':he' => $_POST['headline'],
        ':su' => $_POST['summary'])
    );

    $profile_id = $pdo->lastInsertId();

    insertEducation($pdo, $profile_id);

    $rank = 1;
    for ($i=1; $i<=9; $i++) {
        if ( ! isset($_POST['year'.$i])) continue;
        if ( ! isset($_POST['desc'.$i])) continue;
        $year = $_POST['year'.$i];
        $desc = $_POST['desc'.$i];

        $stmt = $pdo->prepare('INSERT INTO position ( profile_id, rank, year, description) VALUES ( :pid, :rank, :year, :desc)');
        $stmt->execute(array(
            ':pid' => $profile_id,
            ':rank' => $rank,
            ':year' => $year,
            ':desc' => $desc)
        );
        $rank++;
    }

    $_SESSION['success'] = "Record added";
    header("Location: index.php");
    return;
    }
?>
<!DOCTYPE html>
<html>
<head>
<title>Arthur Görzen</title>
</head>
<body>
<script src="jquery.js"></script>
<div class="container">
<h1>Adding Profile for <?php echo $_SESSION['name']; ?></h1>
<?php
if ( isset($_SESSION['error'])) {
    echo('<p style="color: red;">'.$_SESSION['error']."</p>\n");
    unset($_SESSION['error']);
} else if ( isset($_SESSION['success'])) {
    echo('<p style="color: green;">'.$_SESSION['success']."</p>\n");
    unset($_SESSION['success']);
}
?>
<form method="post">
<p>First Name:<input type="text" name="first_name"></p>
<p>Last Name:<input type="text" name="last_name"></p>
<p>Email:<input type="text" name="email"></p>
<p>Headline:</br><input type="text" name="headline"></p>
<p>Summary:</br><textarea name="summary" rows="8" cols="80"></textarea>
<p>Education: <input type="submit" id="addEdu" value="+">
<div id="edu_fields"></div>
<p>Position: <input type="submit" id="addPos" value="+">
<div id="position_fields"></div>
</p>
<p>
<input type="submit" name="Add" value="Add"/>
<input type="submit" name="cancel" value="Cancel"/>
</p>
</form>
<script src="https://code.jquery.com/jquery-3.6.0.js"></script>  
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script> 
<script>

countPos = 0;
countEdu = 0;

$(document).ready(function(){
    console.log('Document ready called');
    $('#addPos').click(function(event) {
        event.preventDefault();
        if ( countPos >= 9 ) {
            alert("Maximum of nine position entries exceeded");
            return;
        }
    countPos++;
    console.log("Adding position "+countPos);
    $('#position_fields').append(
        '<div id="position'+countPos+'"> \
        <p>Year: <input type="text" name="year'+countPos+'" value="" /> \
        <input type="button" value="-" \ onclick="$(\'#position'+countPos+'\').remove();return false;"></p> \
        <textarea name="desc'+countPos+'" rows="8" cols="80"></textarea> \
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

        $('#edu_fields').append(
            '<div id="edu'+countEdu+'"> \
            <p>Year: <input type="text" name="edu_year'+countEdu+'" value="" /> \
            <input type="button" value="-" onclick="$(\'#edu'+countEdu+'\').remove();return false;"><br>\
            <p>School: <input type="text" size="80" name="edu_school'+countEdu+'" class="school" value="" />\
            </p></div>'
        );

        $('.school').autocomplete({
            source: "school.php"
        });

    });
});

</script>
</div>
</body>
</html>