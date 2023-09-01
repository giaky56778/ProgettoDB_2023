<?php
    session_start();
    include "../../Database/config.php";
    if(!isset($_SESSION['utente']))
        header("Location: ../../login.php");
?>
<html>
    <head>
        <title>Gestione esami futuri</title>        
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
                if(isset($_GET['id'])){
                    if(isset($_GET['nuova_data'])&&isset($_GET['ora_nuova'])){
                        pg_send_query($conn,"insert into esame(docente, data_ora, insegnamento) values('".$_SESSION['utente']."','".pg_escape_string($_GET['nuova_data'])." ".pg_escape_string($_GET['ora_nuova'])."','".pg_escape_string($_GET['id'])."')");
                        $res=pg_get_result($conn);
                        if (pg_result_error($res))
                            echo pg_result_error($res);
                        else
                            echo "<p>Inserimento avvenuto con successo</p>";
                    }else{
            ?>
            <h2>Inserimento esame <?php echo $_GET['id'];?></h2>
            <form action="inserisci_esami.php" method="GET"> 
                <div class="col-md-8">
                    <label class="form-label" for="nuova_data">Inserisco data</label>
                    <div class="input-group has-validation">
                        <input class="form-control" type="date" id="nuova_data" name="nuova_data"/>
                        <input class="form-control" type="time" id="ora_nuova" name="ora_nuova"/>
                    </div>
                    <input type="hidden" id="id" name="id" value='<?php echo $_GET['id']; ?>'/>
                    <input type="submit" class='btn btn-primary'/>
                </div>
            </form>
            <?php        
                }
                    }else{
                        pg_send_query($conn,"select nome_insegnamento,id from insegnamento inner join responsabile on insegnamento.id = responsabile.insegnamento where ruolo=1 and docente = current_user");
                        $res=pg_get_result($conn);
                        if (pg_result_error($res))
                            echo pg_result_error($res);
                        if(pg_num_rows($res)!=0){
                            echo "
                            <h2>Inserimento nuovo esame</h2>
                                <table class='table table-striped'>
                                    <tr>
                                        <th>Codice</th>
                                        <th>Insegnamento</th>
                                        <th>&nbsp;</th>
                                    </tr>";
                            while($row = pg_fetch_row($res)){
                                echo "<tr>
                                        <td>$row[1]</td>
                                        <td>$row[0]</td>
                                        <td><a class='btn btn-primary' href='inserisci_esami.php?id=$row[1]'>Inserisci</a></td>
                                    </tr>";
                            }
                            echo "</table>";
                        }else{
                            echo "<p>Non puoi inserire nessun esame, non sei responsabile in nessun esame</p>";
                        }
                    }  
            ?>
        </div>
    </body>
</html>