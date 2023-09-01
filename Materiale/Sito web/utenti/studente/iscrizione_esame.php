<?php
    session_start();
    include "../../Database/config.php";
    if(!isset($_SESSION['utente']))
        header("Location: ../../login.php");
?>
<html>
    <head>
        <title>Iscrizione esame</title>
        <script src="cerca.js"></script>
        <meta charset="utf-8">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
        <link href="../style.css" rel="stylesheet">
        <link rel="icon" href="../../img/favicon_0_0.ico" type="image/vnd.microsoft.icon">
        <script src="cerca.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
    </head>
    <body>
        <?php include_once("../nav_bar.php"); ?>
        <div class="container-md">
            <div id="risposta"></div>
            <?php if(isset($_GET['esame'])){ ?>
                <script>esame('<?php echo $_GET['esame']; ?>',true);</script>
            <?php }else if(isset($_GET['data_ora'])&&isset($_GET['lettere'])&&isset($_GET['id'])){ ?>
            <div class="iscrizione_successo">
            <?php 
                pg_send_query($conn,"insert into esami_studenti(studente, esame_lettere, esame_data_ora,esame_insegnamento) values((select matricola from studenti),'".pg_escape_string($_GET['lettere'])."','".pg_escape_string($_GET['data_ora'])."','".pg_escape_string($_GET['id'])."')");
                $res=pg_get_result($conn);
                if (pg_result_error($res))
                    echo pg_result_error($res);
                else
                echo "<p>Inserimento effettuato con successo</p>";
            ?>
            </div>
            <?php }else{ ?>
                <script> mostra_esame(true); </script>
            <?php } if (sizeof($_GET)>0 || sizeof($_POST)>0) {?>
                <br><a class="btn btn-primary" href='iscrizione_esame.php'>Torna indietro</a>
            <?php } ?>
        </div>
    </body>
</html>