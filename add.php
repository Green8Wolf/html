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
				header("Location: add.php");
				return;
		} else {
			if (strpos($_SESSION['email'], '@') === false){
				$_SESSION['error'] = "Email address must contain @";
				header("Location: add.php");
				return;
			}else {
				if (is_string($posmsg)){
					$_SESSION['error'] = $posmsg;
					header("Location: add.php");
					return;
				}else{
					if (is_string($edumsg)){
					$_SESSION['error'] = $edumsg;
					header("Location: add.php");
					return;
					}else{
						if (isset($_POST['first_name']) && isset($_POST['last_name']) && isset($_POST['email']) && isset($_POST['headline']) && isset($_POST['summary'])) {
							$sql = 'INSERT INTO Profile (user_id, first_name, last_name, email, headline, summary) VALUES ( :uid, :fn, :ln, :em, :he, :su)';
							$stmt = $pdo->prepare($sql);
							$stmt->execute(array(
							':uid' => $_SESSION['user_id'],
							':fn' => $_POST['first_name'],
							':ln' => $_POST['last_name'],
							':em' => $_POST['email'],
							':he' => $_POST['headline'],
							':su' => $_POST['summary'])
							);
							$profile_id = $pdo->lastInsertId();
							if ($posmsg === true) {
								$rank = 0;
								for($i=1; $i<=9; $i++) {
									if ( ! isset($_POST['year'.$i]) ) continue;
									if ( ! isset($_POST['desc'.$i]) ) continue;
									$year = $_POST['year'.$i];
									$desc = $_POST['desc'.$i];
									$stmt = $pdo->prepare('INSERT INTO Position (profile_id, rank, year, description) VALUES ( :pid, :rank, :year, :desc)');
									$stmt->execute(array(
									':pid' => $profile_id,
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
							$_SESSION['success'] = "Record added";
							return;
						}
					}
				}
			}
		}
	}
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
		<h1>Adding Profile for <?= $_SESSION['name'] ?></h1>
		<form method="post">
			<p>First Name:
			<input type="text" name="first_name" size="60" value="<?php
			$value = $_SESSION['first_name'] ?? '';
			echo $value;
			?>"/></p>
			<p>Last Name:
			<input type="text" name="last_name" size="60" value="<?php
			$value = $_SESSION['last_name'] ?? '';
			echo $value;
			?>"/></p>
			<p>Email:
			<input type="text" name="email" size="30" value="<?php
			$value = $_SESSION['email'] ?? '';
			echo $value;
			?>"/></p>
			<p>Headline:<br/>
			<input type="text" name="headline" size="80" value="<?php
			$value = $_SESSION['headline'] ?? '';
			echo $value;
			?>"/></p>
			<p>Summary:<br/>
			<textarea name="summary" rows="8" cols="80"><?php
			$value = $_SESSION['summary'] ?? '';
			echo $value;
				?></textarea></p>
			<p>Education: <input type="submit" id="addEdu" value="+">
			<div id="edu_fields">
				<?php
				$countEdu = 0;
				for($i=1; $i<=9; $i++) {
					if ( ! isset($_SESSION['edu_year'.$i]) ) continue;
					if ( ! isset($_SESSION['edu_school'.$i]) ) continue;
					$countEdu++;
					echo("<div id=\"edu".$countEdu."\">\n
					<p>Year: <input type=\"text\" name=\"edu_year".$countEdu."\" value=\"".htmlentities($_SESSION['edu_year'.$i])."\" />\n
					<input type=\"button\" value=\"-\" onclick=\"$('#edu".$countEdu."').remove();return false;\"><br>\n
					<p>School: <input type=\"text\" size=\"80\" name=\"edu_school".$countEdu."\" class=\"school\" value=\"".htmlentities($_SESSION['edu_school'.$i])."\" />\n
					</p></div>");
				}
				?>
			</div>
			</p>
			<p>Position: <input type="submit" id="addPos" value="+">
			<div id="position_fields">
				<?php
				$countPos = 0;
				for($i=1; $i<=9; $i++) {
					if ( ! isset($_SESSION['year'.$i]) ) continue;
					if ( ! isset($_SESSION['desc'.$i]) ) continue;
					$countPos++;
					echo("<div id=\"position".$countPos."\">\n
					<p>Year: <input type=\"text\" name=\"year".$countPos."\" value=\"".htmlentities($_SESSION['year'.$i])."\" />\n
					<input type=\"button\" value=\"-\" onclick=\"$('#position".$countPos."').remove();return false;\"></p>\n
					<textarea name=\"desc".$countPos."\" rows=\"8\" cols=\"80\">".htmlentities($_SESSION['desc'.$i])."</textarea>\n
					</div>");
				}
				?>
			</div>
			</p>
			<p>
			<input type="submit" value="Add">
			<input type="submit" name="cancel" value="Cancel">
			</p>
		</form>
		<script>
countPos = <?=$countPos?>;
countEdu = <?=$countEdu?>;
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
	</div>
</body>
</html>