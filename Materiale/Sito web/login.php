<?php
    include "Database/config.php";
    session_start();
    if(isset($_SESSION['tipologia'])){
        header("Location: utenti/".$_SESSION['tipologia'].".php");
    }
    if(isset($_POST['password']) && isset($_POST['username'])){
        $result = pg_query($conn, "SELECT * FROM public.esiste_utente('".pg_escape_string($_POST['username'])."','".pg_escape_string($_POST['password'])."')");
        if (!$result) {
            echo "An error occurred 2.\n";
            exit;
        }
        if(pg_num_rows($result)!=0){
            $row = pg_fetch_row($result);
            $_SESSION['utente'] = $_POST['username'];
            $_SESSION['psw'] = $_POST['password'];
            $_SESSION['tipologia'] = $row[0];
            header("Location: utenti/".$_SESSION['tipologia']."/".$_SESSION['tipologia'].".php");
        }else{
            echo "<p>ERRORE LOGIN</p>";
        }
    }
?>
<html>
    <head>
        <title>Login</title>        
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
        <link href="utenti/style.css" rel="stylesheet">
        <link rel="icon" href="img/favicon_0_0.ico" type="image/vnd.microsoft.icon">
        <meta charset="utf-8">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
    </head>
    <body>
        <div class="login position-absolute top-50 start-50 translate-middle">
            <h1>Login</h1>
            <div class="mb-3">
                <form method="POST" action="login.php" class="row g-3 needs-validation">
                    <div class="col-md-6">
                        <label for="validationCustomUsername" class="form-label">Username</label>
                        <div class="input-group has-validation">
                            <span class="input-group-text" id="inputGroupPrepend">@</span>
                            <input type="text" class="form-control" name="username" id="validationCustomUsername" aria-describedby="inputGroupPrepend" required placeholder="Utente"/>
                            <div class="invalid-feedback">
                                Inserire Username.
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="exampleFormControlInput1" name="password" placeholder="Password" required/>
                        <div class="invalid-feedback">
                            Inserire Password.
                        </div>
                    </div>
                        <br><input type="submit" class="btn btn-primary mb-3" style="height:40px"/>
                </form>
            </div> 
        </div>
        <img src="img/LogoFooter_a9f0c3692bf29c71609e5f204522c5d4_0.png" width="175px" class="rounded float-end">
    </body>
</html>