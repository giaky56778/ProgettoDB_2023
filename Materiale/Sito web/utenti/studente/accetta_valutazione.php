<?php
    session_start();
    include "../../Database/config.php";
    if(!isset($_SESSION['utente']))
        header("Location: ../../login.php");
?>
<html>
    <head>
        <title>Accetta voto</title>
        <script src="cerca.js"></script>
        <meta charset="utf-8">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
        <link href="../style.css" rel="stylesheet">
        <link rel="icon" href="../../img/favicon_0_0.ico" type="image/vnd.microsoft.icon">
        <script src="cerca.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>

    </head>
    <body>
        <?php include_once("../nav_bar.php");?>
        <div class="container-md">
            <?php echo "<br>";
                if(isset($_GET['accetta'])){
                    if($_GET['accetta']=='true'){
            ?>
            <div class='accetta'>
                <?php
                    pg_send_query($conn,"update esami_studenti set accettato = true where studente = (select matricola from studenti) and esame_insegnamento='".pg_escape_string($_GET['insegnamento'])."' and esame_data_ora='".pg_escape_string($_GET['data_ora'])."' and  esame_lettere='".pg_escape_string($_GET['lettere'])."'");
                    $res=pg_get_result($conn);
                    if (pg_result_error($res))
                        echo pg_result_error($res);
                    else
                        echo "<p> Voto accettato con successo </p>";
                ?>
            </div>
            <?php }else if($_GET['accetta']=='false'){ ?>
            <div class='accetta'>
                <?php
                    pg_send_query($conn,"update esami_studenti set accettato = false where studente = (select matricola from studenti) and esame_insegnamento='".pg_escape_string($_GET['insegnamento'])."' and esame_data_ora='".pg_escape_string($_GET['data_ora'])."' and  esame_lettere='".pg_escape_string($_GET['lettere'])."'");
                    $res=pg_get_result($conn);
                    if (pg_result_error($res))
                        echo pg_result_error($res);
                    else
                        echo "<p> Voto rifiutato con successo </p>";
                ?>
            </div>
            <?php
                    }
                }else{
            ?>
            <div class="esami">
                <?php
                    pg_send_query($conn,"select nome_insegnamento,voto,lode,docente,data_ora,id,lettere
                    from esami_studenti inner join esame on esami_studenti.esame_insegnamento = esame.insegnamento and esami_studenti.esame_data_ora = esame.data_ora and esami_studenti.esame_lettere = esame.lettere
                        inner join insegnamento on esame.insegnamento = insegnamento.id
                    where voto is not null and accettato is null and esame_data_ora < current_date");
                    $res=pg_get_result($conn);
                    if (pg_result_error($res))
                        echo pg_result_error($res);
                    if(pg_num_rows($res) != 0){
                        echo "<h2>Accetta valutazione degli esami</h2>
                            <table class='table table-striped'>
                                <tr>
                                    <th>Codice esame</th>
                                    <th>Esame</th>
                                    <th>Voto</th>
                                    <th>&nbsp;</th>
                                    <th>&nbsp;</th>
                                </tr>";
                        while($row = pg_fetch_row($res)){
                            echo "<tr>
                                    <td><b>$row[5]</b></td>
                                    <td>$row[0]</td>
                                    <td>$row[1]";
                            if($row[2]=='t')
                                echo "e lode";
                            echo "</td><td><a class='btn btn-primary' href='accetta_valutazione.php?lettere=$row[6]&insegnamento=$row[5]&data_ora=$row[4]&accetta=true'>Accetta</a></td>
                                    <td><a class='btn btn-primary' href='accetta_valutazione.php?lettere=$row[6]&insegnamento=$row[5]&data_ora=$row[4]&accetta=false'>Rifiuta</a></td>
                                </tr>";
                        }
                    }else{
                        echo "<p>Non ci sono esami da accettare</p>";
                    }
                ?>
            </div>
            <?php } if (sizeof($_GET)>0 || sizeof($_POST)>0) {?>
                <a href='accetta_valutazione.php' class='btn btn-primary'>Torna indietro</a>
            <?php } ?>
        </div>
    </body>
</html>