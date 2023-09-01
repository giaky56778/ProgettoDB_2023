<?php
    session_start();
    include "../../Database/config.php";
    if(!isset($_SESSION['utente']))
        header("Location: ../../login.php");
?>
<html>
    <head>
        <title> Gestisci laurea </title>
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
            <?php
                if(isset($_GET['insegnamento_id'])){
                    pg_send_query($conn,"select id,nome_insegnamento,cfu,anno,semestre,descrizione from insegnamento where id='".pg_escape_string($_GET['insegnamento_id'])."'");
                    $res=pg_get_result($conn);
                    if (pg_result_error($res))
                        echo pg_result_error($res);
                    if(pg_num_rows($res)!=0){
                        $row=pg_fetch_row($res);
                        if($row[3]==0)
                            $row[3]='Facoltativo';
                        else
                            $row[3]=$row[3]."°";
                        if($row[4]==0)
                            $row[4]="Tutto l'anno";
                        else
                            $row[4]=$row[4]."°";
                        echo "<div class='card'>
                                <div class='card-header'>
                                    <h3>$row[1]</h3>
                                </div>
                                <ul class='list-group list-group-flush'>
                                    <li class='list-group-item'><b>ID: </b>$row[0]</li>
                                    <li class='list-group-item'><b>CFU: </b>$row[2]</li>
                                    <li class='list-group-item'><b>Anno: </b>$row[3]</li>
                                    <li class='list-group-item'><b>Semestre: </b>$row[4]</li>
                                    <li class='list-group-item'><b>Descrizione: </b>$row[5]</li>
                                </ul>
                            </div>";
                    }
                    pg_send_query($conn,"select nome,cognome,email from insegnamento inner join responsabile on insegnamento.id = responsabile.insegnamento inner join utenti on responsabile.docente = utenti.email where id='".pg_escape_string($_GET['insegnamento_id'])."' and ruolo=1");      
                    $res=pg_get_result($conn);
                    if (pg_result_error($res))
                        echo pg_result_error($res);
                    else{
                        if(pg_num_rows($res)>0){
                            echo "<div class='card'>
                                <div class='card-header'>
                                    <h3>Docenti responsabili</h3>
                                </div>
                                <table class='table table-striped'>
                                    <tr>
                                        <th>Nome e cognome</th>
                                        <th>Email</th>
                                    </tr>";
                            while($row=pg_fetch_row($res)){
                                echo "<tr>
                                        <td>$row[0] $row[1]</td>
                                        <td>$row[2]</td>
                                    </tr>";
                            }
                            echo "</table></div>";
                        }else{
                            echo "<p>Non presente</p>";
                        }
                    }
                    pg_send_query($conn,"select nome,cognome,email from insegnamento inner join responsabile on insegnamento.id = responsabile.insegnamento inner join utenti on responsabile.docente = utenti.email where id='".pg_escape_string($_GET['insegnamento_id'])."' and ruolo=2");            
                    $res=pg_get_result($conn);
                    if (pg_result_error($res))
                        echo pg_result_error($res);
                    else{
                        if(pg_num_rows($res)>0){
                            echo "<div class='card'>
                                    <div class='card-header'>
                                        <h3>Docenti</h3>
                                    </div>
                                    <table class='table table-striped'>
                                    <tr>
                                        <th>Nome e cognome</th>
                                        <th>Email</th>
                                    </tr>";
                            while($row=pg_fetch_row($res)){
                                echo "<tr>
                                        <td>$row[0] $row[1]</td>
                                        <td>$row[2]</td>
                                    </tr>";
                            }
                            echo "</table></div>";
                        }else{
                            echo "<p>Docente non presente</p>";
                        }
                    }
                    pg_send_query($conn,"select insegnamento.nome_insegnamento from propedeutico inner join insegnamento on propedeutico.insegnamento = insegnamento.id inner join insegnamento as i on propedeutico.propedeutico=i.id where i.id='".pg_escape_string($_GET['insegnamento_id'])."';");  
                    $res=pg_get_result($conn);
                    if (pg_result_error($res))
                        echo pg_result_error($res);
                    if(pg_num_rows($res)!=0){
                        echo "<div class='card'>
                                <div class='card-header'>
                                    <h3>Dipendenze di esami</h3>
                                </div>
                                <ul class='list-group list-group-flush'>";
                        while($row=pg_fetch_row($res)){
                            echo "<li class='list-group-item'>$row[0]</li>";
                        }
                        echo "</ul></div>";
                    }
                }else if(isset($_GET['laurea_id'])){
                ?><script> mostra_corsi('<?php echo $_GET['laurea_id'];?>',true);</script><?php
                }else if(isset($_GET['corso'])){
                ?>
                    <script>
                        mostra_insegnamento('<?php echo $_GET['corso'];?>',1,0); 
                    </script>
                <?php }else { ?>
                    <h2>Visualizza Corsi vari </h2>
                    <p>Premera la laurea da visualizzare</p>
                    <script> mostra_lauree(true); </script> 
                <?php } ?> 
            <div id="risposta"></div>
            <?php
                if(sizeof($_POST)>0 || sizeof($_GET)>0)
                    echo "<br><a class='btn btn-primary' href='visualizza_corso.php'>Torna indietro</a>";
            ?>
        </div>
    </body>
</html>