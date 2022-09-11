<?php
	require_once('pdo.php');

	$sql = "SELECT * FROM profile WHERE profile_id = :pid";
	$stmt = $pdo->prepare($sql);
	$stmt->execute(array(":pid" => $_GET['profile_id']));
	$row = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<?php require_once("head.php") ?>
<body>
	<div style="margin-left: 60px; margin-top: 30px;">
		<h1>Profile Information</h1>
		<p>First Name: <?= $row['first_name'] ?></p>
		<p>Last Name: <?= $row['last_name'] ?></p>
		<p>Email: <?= $row['email'] ?></p>
		<p>Headline:<br> <?= $row['headline'] ?></p>
		<p>Summary:<br> <?= $row['summary'] ?></p>
		<?php
		$sql = "SELECT * FROM education WHERE profile_id = :pid ORDER BY rank";
		$stmt = $pdo->prepare($sql);
		$stmt->execute(array(":pid" => $_GET['profile_id']));
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if (count($rows) !== 0) {
			echo("<p>Education</p><ul>");
			foreach($rows as $row) {
				$sql = "SELECT * FROM institution WHERE institution_id = :pid";
				$stmt = $pdo->prepare($sql);
				$stmt->execute(array(":pid" => $row['institution_id']));
				$school = $stmt->fetch(PDO::FETCH_ASSOC);
				echo("<li>".$row['year'].": ".$school['name']."</li>");
			}
			echo("</ul>");
		}

		$sql = "SELECT * FROM position WHERE profile_id = :pid ORDER BY rank";
		$stmt = $pdo->prepare($sql);
		$stmt->execute(array(":pid" => $_GET['profile_id']));
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if (count($rows) !== 0) {
			echo("<p>Position</p><ul>");
			foreach($rows as $row) {
				echo("<li>".$row['year'].": ".$row['description']."</li>");
			}
			echo("</ul>");
		}
		?>
		<p><a href="index.php">Done</a></p>
	</div>
</body>
</html>