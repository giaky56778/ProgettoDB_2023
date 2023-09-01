<?php
    session_start();
    include "../../Database/config.php";
    if(!isset($_SESSION['utente']))
        header("Location: ../../login.php");
?>
<html>
    <head>
        <title>Segretario</title>        
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
            <?php
                pg_send_query($conn,"select nome,cognome from utenti where email=current_user");
                $res=pg_get_result($conn);
                if (pg_result_error($res))
                    echo pg_result_error($res);
                $row=pg_fetch_row($res);
                echo "<div class='card'>
                        <div class='card-header'><h5>Informazioni Segretario</h3></div>
                        <p><b>Email:</b> ".$_SESSION['utente']."
                        <p><b>Nome e cognome:</b> $row[0] $row[1]
                    </div>";

                pg_send_query($conn,"select count(*),tipologia from utenti group by tipologia");
                $res=pg_get_result($conn);
                if (pg_result_error($res))
                    echo pg_result_error($res);
                if(pg_num_rows($res)!=0){
                    echo "<div class='card'>
                            <div class='card-header'><h5>Informazioni Utenti</h3></div>
                            <table class='table table-striped'>
                            <tr>
                                <th>Tipologia studenti</th>
                                <th>Numero di utenti</th>
                            </tr>";
                    while($row=pg_fetch_row($res)){
                        echo "<tr>
                                <td>$row[1]</td>
                                <td>$row[0]</td>
                            </tr>";
                    }
                    echo "</table></div>";
                }

                pg_send_query($conn,"select count(*),tipologia from utenti_old group by tipologia;");
                $res=pg_get_result($conn);
                if (pg_result_error($res))
                    echo pg_result_error($res);
                if(pg_num_rows($res)!=0){
                    echo "<div class='card'>
                            <div class='card-header'><h5>Informazioni Utenti old</h3></div>
                            <table class='table table-striped'>
                            <tr>
                                <th>Tipologia utenti old</th>
                                <th>Numero di utenti old</th>
                            </tr>";
                    while($row=pg_fetch_row($res)){
                        echo "<tr>
                                <td>$row[1]</td>
                                <td>$row[0]</td>
                            </tr>";
                    }
                    echo "</table></div>";
                }
            ?>
        </div>
    </body>
</html>