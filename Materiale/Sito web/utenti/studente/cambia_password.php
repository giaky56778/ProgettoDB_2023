<?php
    session_start();
    include "../../Database/config.php";
    if(!isset($_SESSION['utente']))
        header("Location: ../../login.php");
?>
<html>
    <head>
        <title>Cambia password</title>
    </head>
    <body>
        <?php 
            include_once("../nav_bar.php");
            if(isset($_POST['vecchia_password'])&&isset($_POST['password'])&&isset($_POST['conferma_password'])){
                if($_POST['vecchia_password']==$_SESSION['psw'] && $_POST['password']==$_POST['conferma_password']){
                    pg_send_query($conn,"begin; update utenti set password ='".$_POST['password']."' where email=current_user; commit;");
                    $_SESSION['psw']=$_POST['password'];
                    echo "<p>Cambio password eseguito con successo</p>";
                }
        ?>
        <a href='studente.php'>Torna alla Home</a> 
        <?php }else{ ?>
        <div class="password_cambia">
            <form method="POST" action="cambia_password.php">
                <label for="password">Vecchia Password</label>
                <input type="password" id="vecchia_password" name="vecchia_password" placeholder="vecchia password"/>
                <br>
                <label for="password">Nuova password</label>
                <input type="password" id="password" name="password" placeholder="password"/>
                <br>
                <label for="password">Conferma password</label>
                <input type="password" id="conferma_password" name="conferma_password" placeholder="conferma password"/>
                <br>
                <input type="submit"/>
            </form> 
        </div>
        <?php } ?>
    </body>
</html>