<?php
	session_start();

	require_once("pdo.php");

	unset($_SESSION['who']);

	$failure = false;

	if (isset($_POST['logout'])) {
		header("Location: logout.php");
		return;
	}

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

	$stmt = $pdo->query("SELECT profile_id, first_name, last_name, headline FROM profile");
	$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<?php require_once("head.php") ?>
<body>
	<div style="margin-left: 60px">
		<h1>Hussain Mayoof's Resume Registry</h1>
	<?php
	if (isset($_SESSION['name'])) {
		if (isset($_SESSION['success'])) {
				echo('<p style="color: green">'.$_SESSION['success']."</p>\n");
				unset($_SESSION['success']);
		}
		if (isset($_SESSION['error'])) {
				echo('<p style="color: red">'.$_SESSION['error']."</p>\n");
				unset($_SESSION['error']);
		}
		echo('</div>');
		
		echo('<div style="margin-left: 60px">');
				if(isset($rows[0])) {
					echo("<table class='resumes'>");
					echo("<tr><th class='resumes'>Name</th><th class='resumes'>Headline</th><th class='resumes'>Action</th></tr>");
					foreach ( $rows as $row ) {
						echo "<tr><td class='resumes'>";
						echo('<a href = "view.php?profile_id='.$row['profile_id'].'">'.htmlentities($row['first_name']).' '.htmlentities($row['last_name']).'</a>');
						echo("</td><td class='resumes'>");
						echo(htmlentities($row['headline']));
						echo("</td><td class='resumes'>");
						echo("<a href='edit.php?profile_id=".$row['profile_id']."'>Edit</a> <a href='delete.php?profile_id=".$row['profile_id']."'>Delete</a>");
					}
					echo("</table>");
				}
				else {
					echo("<p>No rows found</p>");
				}

			echo('<p>');
			echo('<a href="add.php">Add New Entry</a> | ');
			echo('<a href="logout.php">Logout</a>');
			echo('</p>');
		echo('</div>');
	}else {
		echo('<p><a href="login.php">Please log in</a></p>');
		echo('</div>');
		echo('<div style="margin-left: 60px">');
				if(isset($rows[0])) {
					echo("<table class='resumes'>");
					echo("<tr><th class='resumes'>Name</th><th class='resumes'>Headline</th></tr>");
					foreach ( $rows as $row ) {
						echo "<tr><td class='resumes'>";
						echo('<a href = "view.php?profile_id='.$row['profile_id'].'">'.htmlentities($row['first_name']).' '.htmlentities($row['last_name']).'</a>');
						echo("</td><td class='resumes'>");
						echo(htmlentities($row['headline']));
						echo("</td>");
					}
					echo("</table>");
				}
				else {
					echo("<p>No rows found</p>");
				}

	}
	?>
</body>
</html>