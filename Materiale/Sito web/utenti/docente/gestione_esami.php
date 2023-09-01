<?php
    session_start();
    include "../../Database/config.php";
    if(!isset($_SESSION['utente']))
        header("Location: ../../login.php");
?>
<html>
    <head>
        <title>Gestione esami</title>
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
                if(isset($_GET['insegnamento'])&&isset($_GET['data_ora'])&&isset($_GET['lettere'])){
                    if(isset($_GET['cancella'])){
                        pg_send_query($conn,"delete from esame where insegnamento='".pg_escape_string($_GET['insegnamento'])."' and data_ora='".pg_escape_string($_GET['data_ora'])."' and lettere='".pg_escape_string($_GET['lettere'])."'");
                        $res=pg_get_result($conn);
                        if (pg_result_error($res))
                            echo pg_result_error($res);
                        else
                            echo "<p>Cancellazione avvenuta con successo</p>";
                    }else if(isset($_GET['nuova_data'])&&isset($_GET['ora_nuova'])){
                        pg_send_query($conn,"update esame set data_ora='".pg_escape_string($_GET['nuova_data'])." ".pg_escape_string($_GET['ora_nuova'])."' where insegnamento='".pg_escape_string($_GET['insegnamento'])."' and data_ora='".pg_escape_string($_GET['data_ora'])."' and lettere='".pg_escape_string($_GET['lettere'])."'");
                        $res=pg_get_result($conn);
                        if (pg_result_error($res))
                            echo pg_result_error($res);
                        else
                            echo "<p>Operazione avvenuta con successo</p>";
                    } else {
            ?>
            <h3>Alterazione esame</h3>
            <form method="GET" action="gestione_esami.php" class="row g-3 needs-validation">   
                <div class="col-md-8">
                    <label for="nuova_data" class="form-label">Cambio data</label>
                    <div class="input-group has-validation">
                        <input type="date" id="nuova_data" name="nuova_data" class="form-control"/>
                        <input type="time" id="ora_nuova" name="ora_nuova" class="form-control"/>
                    </div>
                    <input type="hidden" id="docente" name="insegnamento" value="<?php echo $_GET['insegnamento'];?>"/>
                    <input type="hidden" id="data_ora" name="data_ora" value="<?php echo $_GET['data_ora'];?>"/>
                    <input type="hidden" id="lettere" name="lettere" value="<?php echo $_GET['lettere'];?>"/>
                    <br>
                    <input type="submit" class='btn btn-primary'/>
                </div>
            </form>
            <?php 
                    }
                }else{
            ?>
                <h2>Gestione degli esami</h2>
                <p>Verranno mostrati solo gli esami degli insegnamenti in cui si è responsabile</p>
                <script>gestione_esami(0);</script>
            <?php } ?>
            
            <div id="risposta"></div>
        </div>
    </body>
</html>