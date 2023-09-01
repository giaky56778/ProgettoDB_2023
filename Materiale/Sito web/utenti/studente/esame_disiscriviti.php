<?php
    session_start();
    include "../../Database/config.php";
    if(!isset($_SESSION['utente']))
        header("Location: ../../login.php");
?>
<html>
    <head>
        <title>Disiscrizione esame</title>        
        <meta charset="utf-8">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
        <link href="../style.css" rel="stylesheet">
        <link rel="icon" href="../../img/favicon_0_0.ico" type="image/vnd.microsoft.icon">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
    </head>
    <body>
        <?php include_once("../nav_bar.php"); ?>
        <div class="container-md">
            <?php
                if(isset($_GET['id'])&&isset($_GET['data_ora'])&&isset($_GET['lettere'])){
                    pg_send_query($conn,"delete from esami_studenti where esame_insegnamento='".pg_escape_string($_GET['id'])."' and esame_data_ora='".pg_escape_string($_GET['data_ora'])."' and esame_lettere='".pg_escape_string($_GET['lettere'])."'");
                    $res=pg_get_result($conn);
                    if (pg_result_error($res))
                        echo pg_result_error($res);
                    else
                        echo "<p>Cancellazione avvenuta con successo</p>";
                }else{
                    header("Location: studente.php");
                }
            ?>
            <br><a href="studente.php" class='btn btn-primary'>Torna indietro</a>
            <script>
                document.getElementById('studente.php').classList.add("active");
            </script>
        </div>
    </body>
</html>