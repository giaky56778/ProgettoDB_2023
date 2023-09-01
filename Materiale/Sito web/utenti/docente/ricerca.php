<?php
    session_start();
    include "../../Database/config.php";
    if(!isset($_SESSION['utente']))
        header("Location: ../../login.php");

    $i=0;
    if(isset($_GET['esame'])&&isset($_GET['page'])){
        pg_send_query($conn,"select nome_insegnamento,data_ora,lettere,id from esame inner join insegnamento on esame.insegnamento = insegnamento.id where docente = current_user and data_ora > current_date limit 15 offset(15*".pg_escape_string($_GET['page']).")");
        $res=pg_get_result($conn);
        if (pg_result_error($res))
            echo pg_result_error($res);
        if(pg_num_rows($res)!=0){
            echo "<table class='table table-striped'>
                    <tr>
                        <th> Esame </th>
                        <th> Data ora </th>
                        <th> Lettere </th>
                        <th> &nbsp; </th>
                        <th> &nbsp; </th>
                    </tr>";
            while($row=pg_fetch_row($res)){
                echo "<tr>
                        <td>$row[0]</td>
                        <td>$row[1]</td>
                        <td>$row[2]</td>
                        <td><a class='btn btn-primary' href='gestione_esami.php?insegnamento=$row[3]&data_ora=$row[1]&lettere=$row[2]'>Modifica</a></td>
                        <td><a class='btn btn-primary' href='gestione_esami.php?insegnamento=$row[3]&data_ora=$row[1]&lettere=$row[2]&cancella=1'>Cancella</a></td>
                    </tr>";
                $i++;
            }
        }
        if($_GET['page']>0){
            ?> <button class="btn btn-primary" id="precedente" onclick="gestione_esami(<?php echo $_GET['page']-1;?>)">Precedente</button> <?php 
        }

        if($i==15){
            ?> <button class="btn btn-primary" id="successivo" onclick="gestione_esami(<?php echo $_GET['page']+1;?>)">Successivo</button> <?php
        }

    }else if(isset($_GET['page'])&&isset($_GET['tipo'])){
        if($_GET['tipo']=='1')
            pg_send_query($conn,"select nome_insegnamento,data_ora,id from esame inner join insegnamento on esame.insegnamento = insegnamento.id where data_ora > current_date and docente=current_user limit 15 offset(15*".pg_escape_string($_GET['page']).")");
        else
            pg_send_query($conn,"select nome_insegnamento,data_ora,id from esame inner join insegnamento on esame.insegnamento = insegnamento.id where data_ora > current_date and insegnamento in (select insegnamento from responsabile where ruolo = 2 and docente=current_user) limit 15 offset(15*".pg_escape_string($_GET['page']).")");

        $res=pg_get_result($conn);
        if (pg_result_error($res))
            echo pg_result_error($res);
        if(pg_num_rows($res)!=0){
            echo "<table class='table table-striped'>
                    <tr>
                        <th>ID</td>
                        <th>Insegnamento</td>
                        <th>Data e ora</td>
                    </tr>";
            while($row=pg_fetch_row($res)){
                echo "<tr>
                        <td><b>$row[2]</b></td>
                        <td>$row[0]</td>
                        <td>$row[1]</td>
                    </tr>";
            }
            echo "</table>";
        }
        echo '<ul class="list-group list-group-horizontal-sm">';
            if($_GET['tipo']==1){
                ?> <button id="1" class="list-group-item active" onclick="mostra_esami(1,0)">Responsabile</button> <?php
            }else{
                ?> <button id="1" class="list-group-item" onclick="mostra_esami(1,0)">Responsabile</button> <?php
            }
            
            if($_GET['tipo']==0){
                ?> <button id="0" class="list-group-item active" onclick="mostra_esami(0,0)">Non Responsabile</button> <?php
            }else{
                ?> <button id="0" class="list-group-item" onclick="mostra_esami(0,0)">Non Responsabile</button> <?php
            }
        echo "</ul><br>";
        
        if($_GET['page']>0){
            ?> <button  class="btn btn-primary" id="precedente" onclick="mostra_esami(1,<?php echo $_GET['page']--;?>)">Precedente</button> <?php 
        }

        if($i==15){
            ?> <button  class="btn btn-primary" id="successivo" onclick="mostra_esami(1,<?php echo $_GET['page']++;?>)">Successivo</button> <?php
        }
    }
?>  