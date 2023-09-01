<?php
    session_start();
    include "../../Database/config.php";
    if(!isset($_SESSION['utente']))
        header("Location: ../../login.php");
?>
<html>
    <head>
        <title>Docente</title>        
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
            <div class="card">
                <div class='card-header'>
                    <h3>Info</h3>
                </div>
                <?php
                    pg_send_query($conn,"select nome,cognome from utenti where email=current_user");
                    $res=pg_get_result($conn);
                    if (pg_result_error($res))
                        echo pg_result_error($res);
                    $row=pg_fetch_row($res);
                    echo "<p><b>Nome e cognome:</b> $row[0] $row[1]</p>";
                    echo "<p><b>Email:</b> ".$_SESSION['utente']."</p>";
                ?></div>
                <?php
                    pg_send_query($conn,"select nome_insegnamento,ruolo from insegnamento inner join responsabile on insegnamento.id = responsabile.insegnamento where ruolo=1 and docente=current_user order by nome_insegnamento");
                    $res=pg_get_result($conn);
                    if (pg_result_error($res))
                        echo pg_result_error($res);
                    if(pg_num_rows($res)!=0){
                        echo '<div class="card">
                                <div class="card-header">
                                    <h3>Esame in cui sei responsabile</h3>
                                </div>
                                <ul class="list-group list-group-flush">';
                        while($row=pg_fetch_row($res)){
                            echo "<li class='list-group-item'>$row[0]</li>";
                        }
                        echo "</ul>
                        </div>";
                    }
                    pg_send_query($conn,"select nome_insegnamento,ruolo from insegnamento inner join responsabile on insegnamento.id = responsabile.insegnamento where ruolo=2 and docente=current_user order by nome_insegnamento");
                    $res=pg_get_result($conn);
                    if (pg_result_error($res))
                        echo pg_result_error($res);
                    if(pg_num_rows($res)!=0){
                        echo '<div class="card"> 
                                <div class="card-header">
                                    <h3>Esame in cui sei docente</h3>
                                </div>
                                <ul class="list-group list-group-flush">';
                        while($row=pg_fetch_row($res)){
                            echo "<li class='list-group-item'>$row[0]</li>";
                        }
                        echo "</ul></div>";
                    }
                    ?>
            <div class="card">
                <div class="card-header">
                    <h3>Prossimi esami delle materie in cui insegni</h3>
                </div>
                <div id="risposta"></div>
                <script>mostra_esami(1,0)</script>
            </div>
        </div>
    </body>
</html>