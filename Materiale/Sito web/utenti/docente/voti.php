<?php
    session_start();
    include "../../Database/config.php";
    if(!isset($_SESSION['utente']))
        header("Location: ../../login.php");
?>
<html>
    <head>
        <title>Gestione esami voti</title>        
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
                if(isset($_POST['voto'])&&isset($_POST['matricola'])&&isset($_POST['data'])&&isset($_POST['id'])){
                    for($i=0;$i<sizeof($_POST['matricola']);$i++){
                        if($_POST['voto'][$i]==31)
                            pg_send_query($conn,"update esami_studenti set voto=30, lode=true where esame_data_ora='".pg_escape_string($_POST['data'])."' and esame_insegnamento='".pg_escape_string($_POST['id'])."' and studente='".pg_escape_string($_POST['matricola'][$i])."'");
                        else
                            pg_send_query($conn,"update esami_studenti set voto=".pg_escape_string($_POST['voto'][$i])." where esame_data_ora='".pg_escape_string($_POST['data'])."' and and esame_insegnamento='".pg_escape_string($_POST['id'])."' and studente='".pg_escape_string($_POST['matricola'][$i])."'");
                            $res=pg_get_result($conn);
                        if (pg_result_error($res)){
                            echo pg_result_error($res);
                            exit;
                        }
                    }
                    echo "<p>Voti caricati con successo</p>";
                }else if(isset($_GET['id'])&&isset($_GET['data'])){
                    pg_send_query($conn,"select studente from esami_studenti where esame_data_ora='".pg_escape_string($_GET['data'])."' and esame_insegnamento='".pg_escape_string($_GET['id'])."' order by studente");
                    $res=pg_get_result($conn);
                    if (pg_result_error($res))
                        echo pg_result_error($res);
                    if(pg_num_rows($res)!=0){
                            echo "<form action='voti.php' method='POST'>
                                    <table class='table table-striped'>
                                        <tr>
                                            <th>Matricola</th>
                                            <th>Voto</th>
                                        </tr>";
                            while($row=pg_fetch_row($res)){
                                echo "<tr>
                                        <td>$row[0]</td>
                                        <td><input type='number' id='voto' name='voto[]' min='1' max='31' placeholder='voto' value='1'/></td>
                                        <input type='hidden' name='matricola[]' value='$row[0]'/>
                                    </tr>";
                            }
                            echo "</table>
                                <input type='hidden' name='data' value='".$_GET['data']."'/>
                                <input type='hidden' name='id' value='".$_GET['id']."'/>
                                <input type='submit' class='btn btn-primary'/>
                            </form>";
                        }
                }else{
                    pg_send_query($conn,"select distinct (esame_data_ora),nome_insegnamento,id
                    from esami_studenti inner join esame on esami_studenti.esame_insegnamento = esame.insegnamento and esami_studenti.esame_data_ora = esame.data_ora and esami_studenti.esame_lettere = esame.lettere
                    inner join insegnamento on esame.insegnamento = insegnamento.id
                    where voto is null and docente=current_user");
                    $res=pg_get_result($conn);
                    if (pg_result_error($res))
                        echo pg_result_error($res);
                    if(pg_num_rows($res)!=0){
                        echo "<h2>Gestisci voto degli esami</h2>
                            <table class='table table-striped'>
                                <tr>
                                    <th>Esame</th>
                                    <th>Data e ora</th>
                                    <th>&nbsp;</th>
                                </tr>";
                        while($row=pg_fetch_row($res)){
                            echo "<tr>
                                    <td>$row[1]</td>
                                    <td>$row[0]</td>
                                    <td><a class='btn btn-primary' href='voti.php?id=$row[2]&data=$row[0]'>Gestisci</a></td>
                                </tr>";
                        }
                        echo "</table>";
                    }else{
                        echo "<p>Non ci sono voti da inserire</p>";
                    }
                }
            ?>
        </div>
    </body>
</html>