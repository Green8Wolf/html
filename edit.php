<?php
	session_start();

	require_once("pdo.php");
	require_once("util.php");

	if ( ! isset($_SESSION['name']) || strlen($_SESSION['name']) < 1  ) {
		die('Not logged in');
	}

	if (isset($_POST['cancel'])) {
		header("Location: index.php");
		return;
	}

	if ( ! isset($_GET['profile_id']) ) {
	  $_SESSION['error'] = "Missing profile_id";
	  header('Location: index.php');
	  return;
	}
	
	if ( isset($_POST['first_name']) || isset($_POST['last_name']) || isset($_POST['email']) || isset($_POST['headline']) || isset($_POST['summary'])) {
		unset($_SESSION['first_name']);
		unset($_SESSION['last_name']);
		unset($_SESSION['email']);
		unset($_SESSION['headline']);
		unset($_SESSION['summary']);
		for($i=1; $i<=9; $i++) {
			unset($_SESSION['year'.$i]);
			unset($_SESSION['edu_year'.$i]);
			unset($_SESSION['desc'.$i]);
			unset($_SESSION['school'.$i]);
		}

		$edumsg = validateEdu();
		$posmsg = validatePos();
		
		$_SESSION['first_name'] = $_POST['first_name'];
		$_SESSION['last_name'] = $_POST['last_name'];
		$_SESSION['email'] = $_POST['email'];
		$_SESSION['headline'] = $_POST['headline'];
		$_SESSION['summary'] = $_POST['summary'];
		
		if ((strlen($_POST['first_name']) < 1) || (strlen($_POST['last_name']) < 1) || (strlen($_POST['email']) < 1)  || (strlen($_POST['headline']) < 1) || (strlen($_POST['summary']) < 1)) {
				$_SESSION['error'] = "All fields are required";
				header("Location: edit.php?profile_id=".$_GET['profile_id']);
				return;
		} else {
			if (strpos($_SESSION['email'], '@') === false){
				$_SESSION['error'] = "Email address must contain @";
				header("Location: edit.php?profile_id=".$_GET['profile_id']);
				return;
			}else {
				if (is_string($posmsg)){
					$_SESSION['error'] = $posmsg;
					header("Location: edit.php?profile_id=".$_GET['profile_id']);
					return;
				}else{
					if (is_string($edumsg)){
					$_SESSION['error'] = $edumsg;
					header("Location: edit.php?profile_id=".$_GET['profile_id']);
					return;
					}else{
						if (isset($_POST['first_name']) && isset($_POST['last_name']) && isset($_POST['email']) && isset($_POST['headline']) && isset($_POST['summary'])) {
						$sql = 'UPDATE profile SET first_name = :fn, last_name = :ln, email = :em, headline = :he, summary  = :su WHERE profile_id = :pid';
						$stmt = $pdo->prepare($sql);
						$stmt->execute(array(
						':pid' => $_GET['profile_id'],
						':fn' => $_POST['first_name'],
						':ln' => $_POST['last_name'],
						':em' => $_POST['email'],
						':he' => $_POST['headline'],
						':su' => $_POST['summary'])
						);
						$stmt = $pdo->prepare("DELETE FROM position WHERE profile_id=:pid");
						$stmt->execute(array(
							":pid" => $_GET['profile_id']
							)
						);
						$stmt = $pdo->prepare("DELETE FROM education WHERE profile_id=:pid");
						$stmt->execute(array(
							":pid" => $_GET['profile_id']
							)
						);
						$profile_id = $_GET['profile_id'];
						if ($posmsg === true) {
							$rank = 0;
							for($i=1; $i<=9; $i++) {
								if ( ! isset($_POST['year'.$i]) ) continue;
								if ( ! isset($_POST['desc'.$i]) ) continue;
								$year = $_POST['year'.$i];
								$desc = $_POST['desc'.$i];
								$stmt = $pdo->prepare('INSERT INTO Position (profile_id, rank, year, description) VALUES ( :pid, :rank, :year, :desc)');
								$stmt->execute(array(
								':pid' => $_GET['profile_id'],
								':rank' => $rank,
								':year' => $year,
								':desc' => $desc)
								);

								$rank++;
							}
						}
						if ($edumsg === true) {
							$rank = 0;
							for($i=1; $i<=9; $i++) {
								if ( ! isset($_POST['edu_year'.$i]) ) continue;
								if ( ! isset($_POST['edu_school'.$i]) ) continue;
								$year = $_POST['edu_year'.$i];
								$school = $_POST['edu_school'.$i];
								$stmt = $pdo->prepare('SELECT * FROM institution WHERE name = :school');
								$stmt->execute(array(':school' => $school));
								$schools = $stmt->fetch(PDO::FETCH_ASSOC);
								if (isset($schools['institution_id']) !== true) {
									$stmt = $pdo->prepare('INSERT INTO institution (name) VALUES (:name)');
									$stmt->execute(array(':name'=>$school));
									$school_id = $pdo->lastInsertId();
								}else {
									$school_id = $schools['institution_id'];
								}
								$stmt = $pdo->prepare('INSERT INTO education (profile_id, institution_id, rank, year) VALUES ( :pid, :iid, :rank, :year)');
								$stmt->execute(array(
								':pid' => $profile_id,
								':iid' => $school_id,
								':rank' => $rank,
								':year' => $year)
								);

								$rank++;
							}
						}
						header("Location: index.php");
						$_SESSION['success'] = "Record Updated";
						return;
						}
					}
				}
			}
		}
	}

	$stmt = $pdo->prepare("SELECT * FROM profile where profile_id = :id");
	$stmt->execute(array(":id" => $_GET['profile_id']));
	$row = $stmt->fetch(PDO::FETCH_ASSOC);
	if ( $row === false ) {
			$_SESSION['error'] = 'Bad value for profile_id';
			header( 'Location: index.php' ) ;
			return;
	}
	$stmt = $pdo->prepare("SELECT * FROM position where profile_id = :id");
	$stmt->execute(array(":id" => $_GET['profile_id']));
	$posRow = $stmt->fetchALL(PDO::FETCH_ASSOC);
	$stmt = $pdo->prepare("SELECT * FROM education where profile_id = :id");
	$stmt->execute(array(":id" => $_GET['profile_id']));
	$eduRow = $stmt->fetchALL(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<?php require_once("head.php") ?>
<body>
	<div style="margin-left: 60px; margin-top: 30px;">
		<?php
		if (isset($_SESSION['error'])){
    		echo('<p style="color: red;">'.htmlentities($_SESSION['error'])."</p>\n");
			unset($_SESSION['error']);
		}
		?>
		<h1>Editing Profile for <?= $_SESSION['name'] ?></h1>
		<form method="post">
			<p>First Name:
			<input type="text" name="first_name" size="60" value="<?php
			$value = $row['first_name'] ?? '';
			echo $value;
			?>"/></p>
			<p>Last Name:
			<input type="text" name="last_name" size="60" value="<?php
			$value = $row['last_name'] ?? '';
			echo $value;
			?>"/></p>
			<p>Email:
			<input type="text" name="email" size="30" value="<?php
			$value = $row['email'] ?? '';
			echo $value;
			?>"/></p>
			<p>Headline:<br/>
			<input type="text" name="headline" size="80" value="<?php
			$value = $row['headline'] ?? '';
			echo $value;
			?>"/></p>
			<p>Summary:<br/>
			<textarea name="summary" rows="8" cols="80"><?php
			$value = $row['summary'] ?? 'Hello';
			echo $value;
			?></textarea>
			<p>Education: <input type="submit" id="addEdu" value="+"></p>
			<div id="edu_fields">
			<?php
				$edurank = 0;
				if($eduRow) {
					foreach($eduRow as $edu) {
						$edurank ++;
						$stmt = $pdo->prepare("SELECT * FROM institution where institution_id = :id");
						$stmt->execute(array(":id" => $edu['institution_id']));
						$school = $stmt->fetch(PDO::FETCH_ASSOC);
						echo('<div id="edu'.$edurank.'">');
						echo('<p>Year: <input type="text" name="edu_year'.$edurank.'" value="'.htmlentities($edu['year']).'" />');
						echo('<input type="button" value="-" onclick="$(\'#edu'.$edurank.'\').remove();return false;">');
						echo('</p>');
						echo('<p>School: <input type="text" size="80" name="edu_school'.$edurank.'" class="school" value="'.htmlentities($school['name']).'">');
						echo('</div>');
					}
				}
				?>
			</div>
			<p>Position: <input type="submit" id="addPos" value="+">
			<div id="position_fields">
				<?php
				$posrank = 0;
				if($posRow) {
					foreach($posRow as $pos) {
						$posrank ++;
						echo('<div id="position'.$posrank.'">');
						echo('<p>Year: <input type="text" name="year'.$posrank.'" value="'.htmlentities($pos['year']).'" />');
						echo('<input type="button" value="-" onclick="$(\'#position'.$posrank.'\').remove();return false;">');
						echo('</p>');
						echo('<textarea name="desc'.$posrank.'" rows="8" cols="80">');
						echo($pos['description']);
						echo('</textarea>');
						echo('</div>');
					}
				}
				?>
			</div>
			</p>
			<p>
			<input type="submit" value="Save">
			<input type="submit" name="cancel" value="Cancel">
			</p>
		</form>
		<script>
countPos = <?php echo($posrank) ?>;
countEdu = <?php echo($edurank) ?>;
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
            <input type="button" value="-" \
                onclick="$(\'#position'+countPos+'\').remove();return false;"></p> \
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
</body>
</html>