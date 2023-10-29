<?php

    session_start();

    $sqluser = "iyad";
    $sqlpassword = "engeyad5amisg3fr";
    $sqldatabase = "registration";

    $post = $_SERVER['REQUEST_METHOD']=='POST';

    if ($post) {
        if(
            empty($_POST['uname'])||
            empty($_POST['fname'])||
            empty($_POST['lname'])||
            empty($_POST['email'])||
            empty($_POST['pass'])||
            empty($_POST['repass'])
        ) $empty_fields = true;

        else {
            $empty_fields = false;
            $unmatch = preg_match('/^[A-Za-z][A-Za-z0-9_]{3,}$/', $_POST['uname']);
            $fnmatch = preg_match('/^[A-Za-z]+$/', $_POST['fname']);
            $lnmatch = preg_match('/^[A-Za-z]+$/', $_POST['lname']);
            $emmatch = preg_match('/^[A-Za-z_0-9]+@[A-Za-z]+.[A-Za-z]+$/', $_POST['email']);
            $pmatch = preg_match('/.{5,}/',$_POST['pass']);
            $peq = $_POST['pass']==$_POST['repass'];
            if($unmatch&&$fnmatch&&$lnmatch&&$emmatch&&$pmatch&&$peq) {
                try {
                    $pdo = new PDO("mysql:host=localhost;dbname=".$sqldatabase,$sqluser,$sqlpassword);
                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                } catch (PDOException $e) {
                    exit($e->getMessage());
                }
                $st = $pdo->prepare('SELECT * FROM list WHERE user_name=?');
                $st->execute(array($_POST['uname']));
                $uname_err = $st->fetch() != null;
                $st = $pdo->prepare('SELECT * FROM list WHERE email=?');
                $st->execute(array($_POST['email']));
                $email_err = $st->fetch() != null;
                if(!$uname_err&&!$email_err) {
                    $hashpass = md5($_POST['pass']);
                    $stmt = 'INSERT INTO list(user_name,first_name,last_name,email,password) VALUES (?,?,?,?,?)';
                    $pdo->prepare($stmt)->execute(array(
                        $_POST['uname'],
                        $_POST['fname'],
                        $_POST['lname'],
                        $_POST['email'],
                        $hashpass
                    ));
                    $_SESSION["uname"] = $_POST["uname"];
                    $_SESSION["pass"] = $hashpass;
                    $_SESSION["fname"] = $_POST["fname"];
                    header("Location:success.php");
                    exit;
                }
            }
        }
    }
?>

<!DOCTYPE HTML>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="styles.css">
</head> 
<body>
<div>
<form method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
    <p>SignUp</p>
    <?php
    if (isset($_POST['submit'])){
      $uname = isset($_POST['uname']);             
      $fname = isset($_POST['fname']);             
      $lname = isset($_POST['lname']); 
      $email = isset($_POST['email']);                       
    } else {
        $uname = '';          
        $fname = '';
        $lname = '';
        $email = '';                        
    }
    echo 'Username<br><input type="text" name="uname" value="'.$uname.'" placeholder="Username"><br>';
    if($post&&!$empty_fields&&!$unmatch) echo '<span>Username can contain alphabet letters, numbers and underscore(_), but must begin with a letter. It must be at least 4 character long.<br></span>';
    if(!empty($uname_err)&&$uname_err) echo '<span>Username taken. Try another username.</span>';
    echo '<br>Name<br><input type="text" name="fname" value="'.$fname = ''.'" placeholder="First Name"><br>';
    echo '<input type="text" name="lname" value="'.$lname.'" placeholder="Last Name"><br>';
    if($post&&!$empty_fields&&!($lnmatch&&$fnmatch)) echo '<span>Name can only contain alphabet letters.<br></span>';
    echo '<br>E-mail<br><input type="text" name="email" value="'.$email.'" placeholder="email@example.com"><br>';
    if(!empty($email_err)&&$email_err) echo '<span>Email already registered. Enter another email.</span>';
    if($post&&!$empty_fields&&!$emmatch) echo '<span>Email must be of format example@site.domain<br></span>';
    echo '<br>Password<br><input type="password" name="pass" placeholder="Password"><br>';
    echo '<input type="password" name="repass" placeholder="Retype password">';
    if($post&&!$empty_fields&&!$pmatch) echo '<span>Password must be at least 5 character long</span>';
    if($post&&!$empty_fields&&$pmatch&&!$peq) echo '<span>Password don\'t match</span><br>';
    if($post &&$empty_fields) {
        $empty_field = [];

        foreach ($_POST as $key => $value) {
            if (empty($value)) {
                $empty_field[] = $key;
                // echo "<pre>";
                // print_r($_POST);
                // echo "</pre>";
            }
        }

        echo "<br><span>Please fill all the fields completely. The following fields are empty: ";
        echo implode(', ', $empty_field);
        echo ".</span><br>";
        // echo "<br><span>Please fill all the fields completely.</span><br>";
    }
    ?>
    <br>
    <input type="submit" id="submit" value="SignUp"><br><br>
    Already have a account? <a href="login.php">LogIn</a>.<br><br>
</form>
</div>
</body>
</html>