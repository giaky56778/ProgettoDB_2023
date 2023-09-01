<?php
    session_start();
    include "../../Database/config.php";
    if(!isset($_SESSION['utente']))
        header("Location: ../../login.php");
    $voto=0.0;
    $cfu=0;
?>
<html>
    <head>
        <title>Studente</title>
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
            <div class="row">
                <div class="col-6 card">
                    <?php
                        pg_send_query($conn, "select (nome||' '||cognome)::varchar(100),matricola,(select 1+ extract(year from current_date) - extract( year from anno_iscrizione) from studenti),nome_corso,(nome_laurea||' ('||Laurea.id||')')::varchar(115), (select extract(year from anno_iscrizione) > (1+ extract(year from current_date) - extract( year from anno_iscrizione)) from studenti inner join corso on studenti.frequenta = corso.nome_corso) from studenti inner join utenti on studenti.email = utenti.email inner join corso on studenti.frequenta = corso.nome_corso inner join laurea on corso.laurea = laurea.id");
                        $res=pg_get_result($conn);
                        if (pg_result_error($res))
                            echo pg_result_error($res);
                        $row = pg_fetch_row($res);
                        echo "<div class='card-header'>Informazioni Studente</div>
                                <p><b>Email:</b> ".$_SESSION['utente']."</p>
                                <p><b>Nome:</b> $row[0]</p>
                                <p><b>Matricola:</b> $row[1]</p>
                            </div>
                            <div class='card col-6'>
                                <div class='card-header'>Informazioni Corso</div>
                                    <p><b>Anno:</b> $row[2]</p>
                                    <p><b>Corso:</b> $row[3] - $row[4]</p>";
                        if($row[5]=='t'){
                            echo "<p><b>In corso:</b> Si</p>";
                        }else{
                            echo "<p><b>In corso:</b> No</p>";
                        }
                    ?>
                </div>
            </div>
            <div class="card row" >
                <div class='card-header' style="padding:10px"> Esami in cui ti sei iscritto </div>
                <div id="risposta"></div>
            </div>
            <script>  mostra_esami_iscrizioni(true);  </script>
            <div class="card row">
                <div class='card-header' style="padding:10px"> Carriera </div>
                <?php
                    pg_send_query($conn,"select nome_insegnamento,data_ora,voto,lode from visualizza_carriera_valida(current_user::varchar) where data_ora = (select max(data_ora) from visualizza_carriera_valida(current_user::varchar))");
                    $res=pg_get_result($conn);
                    if (pg_result_error($res))
                        echo pg_result_error($res);
                    if(pg_num_rows($res)!=0){
                        $row=pg_fetch_row($res);
                        pg_send_query($conn,"select voto,cfu from visualizza_carriera_valida(current_user::varchar)");
                        $res_cfu=pg_get_result($conn);
                        if (pg_result_error($res_cfu))
                            echo pg_result_error($res_cfu);
                        while($row_cfu= pg_fetch_row($res_cfu)){
                            $cfu+=$row_cfu[1];
                            $voto+=$row_cfu[0]*$row_cfu[1];
                        }
                        $voto/=$cfu;
                        echo "<p><b>Media pesata:</b> $voto </p>";
                        echo "<p><b>CFU: </b>$cfu</p>";
                        echo "<p><b>Ultimo in piano:</b> $row[0], $row[1], Voto: $row[2]";
                        if($row[3] =='t')
                            echo " e lode";
                        echo "</p>";
                        pg_send_query($conn,"select * from laurea(current_user::varchar);");
                        $res=pg_get_result($conn);
                        if (pg_result_error($res))
                            echo pg_result_error($res);
                        $row=pg_fetch_row($res);
                        echo "<p><b>Puoi ottenere la laurea?</b> ";
                        if($row[0]=='t')
                            echo "Si";
                        else
                            echo "No";
                        echo "</p>";
                    }else{
                        echo "<p>Esami non presenti</p>";
                    }
                ?>
            </div>
        </div>
    </body>
</html>