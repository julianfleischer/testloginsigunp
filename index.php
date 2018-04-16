<?php require('includes/config.php');

//if logged in redirect to members page
if( $user->is_logged_in() ){ header('Location: memberpage.php'); exit(); }

//if form has been submitted process it
if(isset($_POST['submit'])){

    if (!isset($_POST['username'])) $error[] = "Please fill out all fields";
    if (!isset($_POST['email'])) $error[] = "Please fill out all fields";
    if (!isset($_POST['password'])) $error[] = "Please fill out all fields";

	$username = $_POST['username'];

	//very basic validation
	if(!$user->isValidUsername($username)){
		$error[] = 'Usernames must be at least 3 Alphanumeric characters';
	} else {
		$stmt = $db->prepare('SELECT username FROM members WHERE username = :username');
		$stmt->execute(array(':username' => $username));
		$row = $stmt->fetch(PDO::FETCH_ASSOC);

		if(!empty($row['username'])){
			$error[] = 'Username provided is already in use.';
		}

	}

	if(strlen($_POST['password']) < 3){
		$error[] = 'Password is too short.';
	}

	if(strlen($_POST['passwordConfirm']) < 3){
		$error[] = 'Confirm password is too short.';
	}

	if($_POST['password'] != $_POST['passwordConfirm']){
		$error[] = 'Passwords do not match.';
	}

	//email validation
	$email = htmlspecialchars_decode($_POST['email'], ENT_QUOTES);
	if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
	    $error[] = 'Please enter a valid email address';
	} else {
		$stmt = $db->prepare('SELECT email FROM members WHERE email = :email');
		$stmt->execute(array(':email' => $email));
		$row = $stmt->fetch(PDO::FETCH_ASSOC);

		if(!empty($row['email'])){
			$error[] = 'Email provided is already in use.';
		}

	}


	//if no errors have been created carry on
	if(!isset($error)){

		//hash the password
		$hashedpassword = $user->password_hash($_POST['password'], PASSWORD_BCRYPT);

		//create the activasion code
		$activasion = md5(uniqid(rand(),true));

		try {

			//insert into database with a prepared statement
			$stmt = $db->prepare('INSERT INTO members (username,password,email,active) VALUES (:username, :password, :email, :active)');
			$stmt->execute(array(
				':username' => $username,
				':password' => $hashedpassword,
				':email' => $email,
				':active' => $activasion
			));
			$id = $db->lastInsertId('memberID');

			//send email
			$to = $_POST['email'];
			$subject = "Registration Confirmation";
			$body = "<h1>Thank you for registering at bitcoin-faucet.com</h1>
			<p>To activate your account, please click on this link: <a href='".DIR."activate.php?x=$id&y=$activasion'>".DIR."activate.php?x=$id&y=$activasion</a></p>
			<p>Regards Your Team from<br>bitcoin-faucet.com</p>";

			$mail = new Mail();
			$mail->setFrom(SITEEMAIL);
			$mail->addAddress($to);
			$mail->subject($subject);
			$mail->body($body);
			$mail->send();

			//redirect to index page
			header('Location: index.php?action=joined');
			exit;

		//else catch the exception and show the error.
		} catch(PDOException $e) {
		    $error[] = $e->getMessage();
		}

	}

}

//define page title
$title = 'The Bitcoin Faucet';

//include header template
require('layout/header.php');
?>

<html>
<head>
	<meta charset="utf-8">
    <meta name="author" content="Adtile">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
	<link href="https://fonts.googleapis.com/css?family=Lato:100,300" rel="stylesheet"> 
    <link rel="stylesheet" href="css/styles.css">
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
      <link rel="stylesheet" href="css/ie.css">
    <![endif]-->
    <script src="js/responsive-nav.js"></script>
	<link href="//netdna.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
<script src="//netdna.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
<script src="//code.jquery.com/jquery-1.11.1.min.js"></script>
	<link rel="stylesheet" href="style/main.css">
<script defer src="https://use.fontawesome.com/releases/v5.0.10/js/all.js" integrity="sha384-slN8GvtUJGnv6ca26v8EzVaR9DC58QEwsIk9q1QXdCU8Yu8ck/tL/5szYlBbqmS+" crossorigin="anonymous"></script>
	
	
</head>
<body>

<!--NAVIGATION BAR + BITCOIN PREIS JAVASCRIPT + LOGO-->
	
	<header id="home">
		<div class="navigationbar">
			<a href="/">
				<img class="logo-img" src="media/logo.png"></a>
        <nav class="nav-collapse">
			<script>
				 $('li a').click(function(e) {
        e.preventDefault();
        $('a').removeClass('active');
        $(this).addClass('active');
    });
			</script>
            <ul class="ulnav">
                <li class="menu-item zoom"><a href="#home" data-scroll>Home</a></li>
                <li class="menu-item zoom"><a href="#features" data-scroll>Features</a></li>
                <li class="menu-item zoom"><a href="#projects" data-scroll>Bitcoin</a></li>
                <li class="menu-item zoom"><a href="#blog" data-scroll>Signup</a></li>
                <li class="menu-item zoom"><a href="http://www.google.com" target="_blank">Login</a></li>
				<li><a style="background-color:transparent; margin:0;">1 Bitcoin = <span id="btcPrice"><script>
	function getBTCPrice(f) {
		var x = new XMLHttpRequest;
		x.open("GET", "https://blockchain.info/ticker");
		x.onreadystatechange = function() {
			if (196 == x.status - x.readyState)
				f(JSON.parse(x.responseText));
		};
		x.send();
	}
	// get price:
	getBTCPrice(function(json) {
		var elem = document.getElementById("btcPrice");
		elem.innerHTML = "$" + Math.round(json.USD.last);
	});
</script></span></a></li>
            </ul>
        </nav>
		</div>
    </header>

    <script src="js/fastclick.js"></script>
    <script src="js/scroll.js"></script>
	<script src="js/fixed-responsive-nav.js"></script>
	
<!--NAVIGATION BAR + BITCOIN PREIS JAVASCRIPT + LOGO-->

<div id="particles-js" style="position:absolute; height:80%; width:100%;"></div>	
<div class="containerself">
	<div class="backgroundimage">
	<div class="grid">
      <div class="listboxsignup">
		  <ul style="list-style:none; color:white;">
			  
<li class="listitemleftfontawesome"><p><em class="fa fa-check fa-2x"></em><span class="textleftoffontawesome"> earn free bitcoins every hour</span></p></li>
<li class="listitemleftfontawesome"><p><em class="fa fa-check fa-2x"></em><span class="textleftoffontawesome"> multiply your bitcoin everytime</span></p></li>
<li class="listitemleftfontawesome"><p><em class="fa fa-check fa-2x"></em><span class="textleftoffontawesome"> play our fair hi-lo game</span></p></li>
<li class="listitemleftfontawesome"><p><em class="fa fa-check fa-2x"></em><span class="textleftoffontawesome"> win massive bitcoin jackpots</span></p></li>
<li class="listitemleftfontawesome"><p><em class="fa fa-check fa-2x"></em><span class="textleftoffontawesome"> free bitcoin lottery</span></p></li>
</ul>
</div>
		<div class="content2">
         <form class="form" role="form" method="post" action="" autocomplete="off">
            <h2 style="color:white !important;">Create a Account</h2>
            <p style="color:white !important;">Already a member? <a href='login.php' style="color:#1ab188;">Login</a></p>
            <hr>
            <?php
               //check for any errors
               if(isset($error)){
               	foreach($error as $error){
               		echo '<p class="bg-danger">'.$error.'</p>';
               	}
               }
               
               //if action is joined show sucess
               if(isset($_GET['action']) && $_GET['action'] == 'joined'){
               	echo "<h2 class='bg-success'>Registration successful, please check your email to activate your account.</h2>";
               }
               ?>
            <div class="form-group">
               <input type="text" name="username" id="username" class="form-control input-lg" placeholder="User Name" value="<?php if(isset($error)){ echo htmlspecialchars($_POST['username'], ENT_QUOTES); } ?>" tabindex="1">
            </div>
            <div class="form-group">
               <input type="email" name="email" id="email" class="form-control input-lg" placeholder="Email Address" value="<?php if(isset($error)){ echo htmlspecialchars($_POST['email'], ENT_QUOTES); } ?>" tabindex="2">
            </div>
            <div class="row" style="margin:0px;">
               <div class="col-xs-6 col-sm-6 col-md-6" style="padding: 0;">
                  <div class="form-group">
                     <input type="password" name="password" id="password" class="form-control input-lg" placeholder="Password" tabindex="3">
                  </div>
               </div>
               <div class="col-xs-6 col-sm-6 col-md-6" style="padding: 0;">
                  <div class="form-group">
                     <input type="password" name="passwordConfirm" id="passwordConfirm" class="form-control input-lg" placeholder="Confirm Password" tabindex="4">
                  </div>
               </div>
            </div>
            <div class="row">
               <div class="col-xs-6 col-md-6" style="margin: 0; padding: 0; margin-left: 15px;"><input type="submit" name="submit" value="Register" class="btn btn-primary btn-block btn-lg" tabindex="5"></div>
            </div>
         </form>
      </div>
	</div>		
	</div>	



<div class="backgroundcolormain">
	<div class="grid3col">
		<div style="margin-top:20px;"><span class="fab fa-btc fa-4x"></span><br><p style="font-size:35px; font-family: 'Lato', sans-serif; color:#fff !important;">3415</p><p style="font-size:18px; font-family: 'Lato', sans-serif; color:white !important">Bitcoins earned</p></div>
		<div style="margin-top:20px;"><span class="fa fa-user fa-4x"></span><br><p style="font-size:35px; font-family: 'Lato', sans-serif; color:#fff !important;">215.000</p><p style="font-size:18px; font-family: 'Lato', sans-serif; color:white !important">Users Registered</p></div>
		<div style="margin-top:20px;"><span class="fa fa-gamepad fa-4x"></span><br><p style="font-size:35px; font-family: 'Lato', sans-serif; color:#fff !important;">3.204.312</p><p style="font-size:18px; font-family: 'Lato', sans-serif; color:white !important">Games Played</p></div>
	</div>
	</div>
	
	
	<!--Section: Features v.4-->
<section style="position:relative; z-index:0;">

    <!--Section heading-->
    <p class="py-5 font-weight-bold text-center" id="features" style="font-size:40px;">Our outstanding Features</p>
    <!--Section description-->
    <i class="px-5 mb-5 pb-3 lead grey-text text-center">"Bitcoin is the currency of resistance... If Satoshi had released Bitcoin 10 yrs. earlier, 9/11 would never have happened" - Max Keiser</i>

    <!--Grid row-->
    <div class="row" style="max-width: 1000px; width:80%; margin:auto; margin-top:60px;">

    <!--Grid column-->
    <div class="col-md-4">

        <!--Grid row-->
        <div class="row mb-2">
        <div class="col-2">
            <i class="fa fa-2x fa-flag-checkered deep-purple-text"></i>
        </div>
        <div class="col-10 text-left">
            <h5 class="font-weight-bold">Earn free Bitcoins</h5>
            <p class="grey-text">Earn Bitcoins for free everytime you draw a number. This is a great and fast way to stack up Bitcoins and expand your Cryptocurreny Portfolio</p>
        </div>
        </div>
        <!--Grid row-->

        <!--Grid row-->
        <div class="row mb-2">
        <div class="col-2">
            <i class="fa fa-2x fa-flask deep-purple-text"></i>
        </div>
        <div class="col-10 text-left">
            <h5 class="font-weight-bold">Every Hour</h5>
            <p class="grey-text">On our Site you can get Free Bitcoins every hour. That way you can stack your Bitcoins easily 24 Times per Day</p>
        </div>
        </div>
        <!--Grid row-->

        <!--Grid row-->
        <div class="row mb-2">
        <div class="col-2">
            <i class="fa fa-2x fa-user	 deep-purple-text"></i>
        </div>
        <div class="col-10 text-left">
            <h5 class="font-weight-bold">Multiply</h5>
            <p class="grey-text">On bitcoin-faucet.com you have the Opportunity to multiply your free earned Bitcoins. You can play a provably fair HI-LO Game to increase your Bitcoin Balance</p>
        </div>
        </div>
        <!--Grid row-->

    </div>
    <!--Grid column-->

    <!--Grid column-->
    <div class="col-md-4 mb-2 center-on-small-only flex-center iphoneimagefeatures">
        <img src="media/iphone6.png" alt="" class="z-depth-0" style="max-width:260px;">
    </div>
    <!--Grid column-->

    <!--Grid column-->
    <div class="col-md-4">

        <!--Grid row-->
        <div class="row mb-2">
        <div class="col-2">
            <i class="fa fa-2x fa-heart deep-purple-text"></i>
        </div>
        <div class="col-10 text-left">
            <h5 class="font-weight-bold">Free Lottery</h5>
            <p class="grey-text">On our platform we offer a free Bitcoin lottery. You can participate by simply rolling numbers or betting your credit on the HI-LO game. You will automatically participate in our free Bitcoin lottery</p>
        </div>
        </div>
        <!--Grid row-->

        <!--Grid row-->
        <div class="row mb-2">
        <div class="col-2">
            <i class="fa fa-2x fa-user deep-purple-text"></i>
        </div>
        <div class="col-10 text-left">
            <h5 class="font-weight-bold">Wallet</h5>
            <p class="grey-text">At bitcoin-faucet.com we also give you the opportunity to deposit additional bitcoins and use them to participate in our free lottery or to multiply them in the HI-LO game</p>
        </div>
        </div>
        <!--Grid row-->

        <!--Grid row-->
        <div class="row mb-2">
        <div class="col-2">
            <i class="fa fa-2x fa-magic deep-purple-text"></i>
        </div>
        <div class="col-10 text-left">
            <h5 class="font-weight-bold">Bonus Bitcoins</h5>
            <p class="grey-text">You can double your Bitcoins to wager them inside the Multiply Bitcoins Section</p>
        </div>
        </div>
        <!--Grid row-->

    </div>
    <!--Grid column-->

    </div>
    <!--Grid row-->

</section>
<!--/Section: Features v.4-->
            
            

	</div>
	
	
		
		
		
		
	
	
<!--containerself END END END END END END END END END END END END END ENDEND END END END END END ENDEND END END END END END ENDEND END END END END END END-->
	
	

	
	
	
	
	
	
<?php
//include header template
require('layout/footer.php');
?>
	<script src="particles.js"></script>
	<script src="app.js"></script>	
	


</body>
</html>
