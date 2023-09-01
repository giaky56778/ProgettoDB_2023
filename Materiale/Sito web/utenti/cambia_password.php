<?php
    session_start();
    include "../Database/config.php";
    if(!isset($_SESSION['utente']))
        header("Location: ../login.php");
?>
<html>
    <head>
        <title>Cambia password</title>        
        <meta charset="utf-8">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
        <link href="style.css" rel="stylesheet">
        <link rel="icon" href="../img/favicon_0_0.ico" type="image/vnd.microsoft.icon">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
    </head>
    <body>
        <?php  include_once("nav_bar.php"); ?>
        <div class="container-md position-absolute top-50 start-50 translate-middle">
            <?php if(isset($_POST['vecchia_password'])&&isset($_POST['password'])&&isset($_POST['conferma_password'])){
                    if($_POST['vecchia_password']==$_SESSION['psw'] && $_POST['password']==$_POST['conferma_password']){
                        pg_send_query($conn,"begin; update utenti set password ='".pg_escape_string($_POST['password'])."' where email=current_user; commit;");
                        $res=pg_get_result($conn);
                        if (pg_result_error($res))
                            echo pg_result_error($res);
                        $_SESSION['psw']=$_POST['password'];
                        echo "<p>Cambio password eseguito con successo</p>";
                    }else{
                        echo "<p>Errore! Password non giusta</p>";
                    }
            ?>
            <?php }else{ ?>
            <div class="card" style="width:30em;margin:auto; padding:20px">
                <h3>Cambia password</h3>
                <form method="POST" action="cambia_password.php" class="row g-3 needs-validation">
                    <label for="password" class="form-label">Vecchia Password</label>
                    <div class="input-group has-validation">
                        <input type="password" class="form-control" id="vecchia_password" name="vecchia_password" placeholder="vecchia password"/>
                    </div>
                    <label class="form-label" for="password">Nuova password</label>
                    <div class="input-group has-validation">
                        <input type="password" class="form-control" id="password" name="password" placeholder="password"/>
                    </div>
                    <label class="form-label" for="password">Conferma password</label>
                    <div class="input-group has-validation">
                        <input type="password" class="form-control" id="conferma_password" name="conferma_password" placeholder="conferma password"/>
                    </div>
                    <br>
                    <input type="submit" class="btn btn-primary mb-3" style="height:40px"/>
                </form> 
            </div>
            <?php } ?>
        </div>
    </body>
</html>