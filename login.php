<?php
session_start();

require_once("pdo.php");

if ( isset($_POST['cancel'] ) ) {
    header("Location: index.php");
    return;
}

$salt = 'XyZzy12*_';
$stored_hash = '1a52e17fa899cf40fb04cfc42e6352f1';

if ( isset($_POST['email']) && isset($_POST['pass']) ) {
	unset($_SESSION['who']);
	$_SESSION['who'] = $_POST['email'];
	$_SESSION['pass'] = $_POST['pass'];
	if ( strlen($_SESSION['who']) < 1 || strlen($_SESSION['pass']) < 1 ) {
        $_SESSION['failure'] = "User name and password are required";
		header("Location: login.php");
		return;
    } else {
		if ( strpos($_SESSION['who'], '@') === false  ) {
			$_SESSION['failure'] = "Email must have an at-sign (@)";
			header("Location: login.php");
			return;
	} else {
	$check = hash('md5', $salt.$_POST['pass']);
	$stmt = $pdo->prepare('SELECT user_id, name FROM users WHERE email = :em AND password = :pw');
	$stmt->execute(array( ':em' => $_POST['email'], ':pw' => $check));
	$row = $stmt->fetch(PDO::FETCH_ASSOC);
	if ( $row !== false) {
		$_SESSION['user_id'] = $row['user_id'];
		$_SESSION['name'] = $row['name'];
		unset($_SESSION['who']);
		header("Location: index.php");
		error_log("Login success ".$_SESSION['who']);
		return;
	}else {
		$_SESSION['failure'] = "Incorrect password";
		error_log("Login fail ".$_SESSION['who']." $check");
		header("Location: login.php");
		return;
		}
	}
	}
}

?>

<!DOCTYPE html>
<html lang="en">
<?php require_once("head.php") ?>
<body>
<?php
if (isset($_SESSION['failure'])) {
    echo('<p style="color: red;">'.htmlentities($_SESSION['failure'])."</p>\n");
	unset($_SESSION['failure']);
}
?>
	<div style="margin-left:60px; margin-top: 30px">
		<form method="POST">
			<label for="email">Email</label>
			<input type="text" name="email" id="email" value="<?php
			$value = $_SESSION['who'] ?? '';
			echo $value;
			?>"><br/>
			<br>
			<label for="id_1723">Password</label>
			<input type="password" name="pass" id="id_1723"><br/>
			<br>
			<input type="submit" onclick="return doValidate();" value="Log In">
			<input type="submit" name="cancel" value="Cancel">
		</form>
	</div>
</body>
<script>
function doValidate() {
    console.log('Validating...');
    try {
        addr = document.getElementById('email').value;
        pw = document.getElementById('id_1723').value;
        console.log("Validating addr="+addr+" pw="+pw);
        if (addr == null || addr == "" || pw == null || pw == "") {
            alert("Both fields must be filled out");
            return false;
        }
        if ( addr.indexOf('@') == -1 ) {
            alert("Invalid email address");
            return false;
        }
        return true;
    } catch(e) {
        return false;
    }
    return false;
}	
</script>
</html>