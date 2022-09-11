<?php
	session_start();

	require_once("pdo.php");

	if ( ! isset($_SESSION['name']) || strlen($_SESSION['name']) < 1  ) {
		die('Not logged in');
	}
	
	if ( isset($_POST['delete']) && isset($_POST['profile_id']) ) {
		$sql = "DELETE FROM profile WHERE profile_id = :profile_id";
		$stmt = $pdo->prepare($sql);
		$stmt->execute(array(':profile_id' => $_POST['profile_id']));
		$_SESSION['success'] = 'Record deleted';
		header( 'Location: index.php' ) ;
		return;
	}

	if ( ! isset($_GET['profile_id']) ) {
	  $_SESSION['error'] = "Missing profile_id";
	  header('Location: index.php');
	  return;
	}

	$stmt = $pdo->prepare("SELECT first_name, last_name FROM profile WHERE profile_id = :profile_id");
	$stmt->execute(array(":profile_id" => $_GET['profile_id']));
	$row = $stmt->fetch(PDO::FETCH_ASSOC);
	if ( $row === false ) {
		$_SESSION['error'] = 'Bad value for profile_id';
		header( 'Location: index.php' ) ;
		return;
	}

?>
<!DOCTYPE html>
<html lang="en">
<?php require_once("head.php") ?>
<body>
	<div style="margin-left:60px; margin-top:30px">
		<h1>Deleting Profile</h1>
		<p>First Name: <?= $row['first_name']?></p>
		<p>Last Name: <?= $row['last_name']?></p>
		<form method="post">
			<input type="hidden" name="profile_id" value="<?= $_GET['profile_id'] ?>"/>
			<input type="submit" value="Delete" name="delete"/>
			<a href="index.php">Cancel</a>
		</form>
	</div>
</body>
</html>
