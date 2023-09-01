<?php
    session_start();
    include "../../Database/config.php";
    if(!isset($_SESSION['utente']))
        header("Location: ../../login.php");

    $i=0;
    if(isset($_GET['carriera'])&&isset($_GET['page'])){
        if($_GET['carriera']==1){
            pg_send_query($conn,"select nome_insegnamento,data_ora,voto,lode,accettato from visualizza_carriera_valida(current_user::varchar) limit 15 offset (".pg_escape_string(($_GET['page']))."*15)");
            echo "<h2>Carriera Valida</h2>";
        }else{
            pg_send_query($conn,"select nome_insegnamento,data_ora,voto,lode,accettato from visualizza_carriera(current_user::varchar) limit 15 offset (".pg_escape_string(($_GET['page']))."*15)");
            echo "<h2>Carriera</h2>";
        }
            $res=pg_get_result($conn);
        if (pg_result_error($res))
            echo pg_result_error($res);
        if(pg_num_rows($res)!=0){
            echo "<table class='table table-striped'>
                    <tr>
                        <th>Esame</th>
                        <th>Data</th>
                        <th>Voto</th>
                        <th>Accettato</th>
                    </tr>";
            while($row=pg_fetch_row($res)) {
                echo "<tr>
                        <td>$row[0]</td>
                        <td>$row[1]</td>
                        <td>$row[2]";
                if($row[3]=='t')
                    echo " e lode";
                echo "  </td>";
                if ($row[4]=='t')
                    echo "<td>Accettato</td>";
                else
                    if($row[4]=='f')
                        echo "<td>Non accettato</td>";
                    else
                        echo "<td>---</td>";
                echo "</tr>";
                $i++;
            }
            echo "</table>";
        }
        echo '<ul class="list-group list-group-horizontal-sm">';
            if($_GET['carriera']==1){
                ?> <button id="1" class="list-group-item active" onclick="mostra_carriera(1,0)">Carriera Valida</button> <?php
            }else{
                ?> <button id="1" class="list-group-item" onclick="mostra_carriera(1,0)">Carriera Valida</button> <?php
            }
            
            if($_GET['carriera']==0){
                ?> <button id="0" class="list-group-item active" onclick="mostra_carriera(0,0)">Carriera</button> <?php
            }else{
                ?> <button id="0" class="list-group-item" onclick="mostra_carriera(0,0)">Carriera</button> <?php
            }
        echo "</ul><br>";

        if($_GET['page']>0){
            ?> <button  class="btn btn-primary" id="precedente" onclick="mostra_carriera(<?php echo ($_GET['carriera']).','.($_GET['page']-1);?>)">Precedente</button> <?php 
        }
        if($i==15){
            ?> <button  class="btn btn-primary" id="successivo" onclick="mostra_carriera(<?php echo ($_GET['carriera']).','.($_GET['page']+1);?>)">Successivo</button> <?php
        }

    }else if(isset($_GET['iscrizione_esami'])&&isset($_GET['page'])){
        pg_send_query($conn,"select nome_insegnamento, esame_data_ora, (nome||' '||cognome)::varchar(100),id,lettere
        from Esami_studenti inner join Studenti on Esami_studenti.studente = Studenti.matricola
            inner join esame on Esami_studenti.esame_insegnamento = esame.insegnamento and Esami_studenti.esame_data_ora = esame.data_ora and Esami_studenti.esame_lettere = esame.lettere
            inner join insegnamento on esame_insegnamento = id inner join utenti on esame.docente = utenti.email
        where esame_data_ora::Date >= current_date order by data_ora
        limit 15 offset(".pg_escape_string(($_GET['page']))."*15)");
        $res=pg_get_result($conn);
        if (pg_result_error($res))
            echo pg_result_error($res);
        if(pg_num_rows($res) != 0){
            echo "
                <table class='table table-striped'>
                    <tr>
                        <th>Esame</th>
                        <th>Data e ora</th>
                        <th>Docente</th>
                        <th>&nbsp;</th>
                    </tr>";
            while($row=pg_fetch_row($res)){
                echo "<tr>
                        <td>$row[0]</td>
                        <td>$row[1]</td>
                        <td>$row[2]</td>
                        <td><a  class='btn btn-primary' class='btn btn-primary' href='esame_disiscriviti.php?data_ora=$row[1]&id=$row[3]&lettere=$row[4]'>Disiscriviti</a></td>
                    </tr>";
                $i++;
            }
            echo "</table>";
        }else{
            echo "<p> Non sei iscritto a nessun esame </p>";
        }
        if($_GET['page']>0){
            ?> <button  class="btn btn-primary" id="precedente" onclick="mostra_esami_iscrizioni(false)">Precedente</button> <?php 
        }

        if($i==15){
            ?> <button  class="btn btn-primary" id="successivo" onclick="mostra_esami_iscrizioni(true)">Successivo</button> <?php
        }

    }else if(isset($_GET['corso'])&&isset($_GET['page'])){
        $string="";
        pg_send_query($conn,"select id,nome_insegnamento from insegnamento where corso='".pg_escape_string($_GET['corso'])."' limit 15 offset (".pg_escape_string(($_GET['page']))."*15)");
        $res=pg_get_result($conn);
        
        if (pg_result_error($res))
            echo pg_result_error($res);
        echo "<h3>Lista degli esami</h3>
            <table class='table table-striped'>
                <tr>
                    <th>Codice esame</th>
                    <th>Nome esame</th>
                </tr>";
        while($row=pg_fetch_row($res)){
            echo "<tr><td>$row[0]</td><td>$row[1]</td></tr>";
            $i++;
        }
        echo "</table>";
        if($_GET['page']>0){
            ?> <button  class="btn btn-primary" id="precedente" onclick="mostra_esami_iscrizioni(false)">Precedente</button> <?php 
        }

        if($i==15){
            ?> <button  class="btn btn-primary" id="successivo" onclick="mostra_esami_iscrizioni(true)">Successivo</button> <?php
        }
    }else if(isset($_GET['laurea_id'])&&isset($_GET['page'])){
        pg_send_query($conn,"select sum(cfu) > (anni*60),nome_corso,anni from corso left join insegnamento on corso.nome_corso = insegnamento.corso where laurea='".pg_escape_string($_GET['laurea_id'])."' group by nome_corso, anni limit 15 offset (".pg_escape_string(($_GET['page']))."*15)");
        $res=pg_get_result($conn);
        if (pg_result_error($res))
            echo pg_result_error($res);
        if(pg_num_rows($res)!=0){
            echo "<h3> Lauree ".$_GET['laurea_id']."</h3>
                    <p>Premi il corso di laurea da visualizzare</p>
                    <table class='table table-striped'>
                        <tr>
                            <th>Nome corso</th>
                            <th>Anni previsti</th>
                            <th>Possibile ottenimento laurea</th>
                            <th>&nbsp;</th>
                        </tr>";
            while($row=pg_fetch_row($res)){
                if($row[0]=='t')
                    $row[0]='Si';
                else
                    $row[0]='No';
                echo "<tr>
                        <td>$row[1]</td>
                        <td>$row[2]</td>
                        <td>$row[0]</td>
                        <td><a  class='btn btn-primary' class='btn btn-primary' href='visualizza_corso.php?corso=$row[1]'>Visualizza</td>
                    </tr>";
                $i++;
            }
            echo "</table>";
            if($_GET['page']>0){
                ?> <button  class="btn btn-primary" id="precedente" onclick="mostra_corsi('<?php echo $_GET['laurea_id'];?>',false)">Precedente</button> <?php 
            }
    
            if($i==15){
                ?> <button  class="btn btn-primary" id="successivo" onclick="mostra_corsi('<?php echo $_GET['laurea_id']; ?>',true)">Successivo</button> <?php
            }
        }
    }else if(isset($_GET['corso_laurea'])&&isset($_GET['anno'])&&isset($_GET['page'])){
        pg_send_query($conn,"select id,nome_insegnamento,cfu,anno,semestre,descrizione from insegnamento where corso='".pg_escape_string($_GET['corso_laurea'])."' and anno='".pg_escape_string($_GET['anno'])."' order by semestre limit 10 offset (".pg_escape_string(($_GET['page']))."*10)");
        $res=pg_get_result($conn);
        if (pg_result_error($res))
            echo pg_result_error($res);
        echo "<h2>Insegnamento di ".$_GET['corso_laurea']."</h2>";
        if(pg_num_rows($res)!=0){
            echo "<table class='table table-striped'>
                    <tr>
                        <th style='width:8em;'>ID</th>
                        <th>Nome insegnamento</th>
                        <th>CFU</th>
                        <th>Anno previsto</th>
                        <th>Semestre</th>
                        <th>Breve descrizione</th>
                        <th>&nbsp;</th>
                    </tr>";
            while($row=pg_fetch_row($res)){
                if($row[3]==0)
                    $row[3]='Facoltativo';
                else
                    $row[3]=$row[3]."°";
                if($row[4]==0)
                    $row[4]="Tutto l'anno";
                else
                    $row[4]=$row[4]."°";
                echo "<tr>
                        <td><b>$row[0]</b></td>
                        <td>$row[1]</td>
                        <td>$row[2]</td>
                        <td>$row[3]</td>
                        <td>$row[4]</td>
                        <td>".substr($row[5],0,100)."...</td>
                        <td><a class='btn btn-primary' href='visualizza_corso.php?insegnamento_id=$row[0]'>Visualizza</a></td>
                    </tr>";
                $i++;
            }
            echo "</table>";
            pg_send_query($conn,"select anni from corso where nome_corso='".pg_escape_string($_GET['corso_laurea'])."'");
            $res=pg_get_result($conn);
            if (pg_result_error($res))
                echo pg_result_error($res);
            $row=pg_fetch_row($res);
            echo '<ul class="list-group list-group-horizontal-sm">';
            if($_GET['anno']==1){
                ?> <button id="1" class="list-group-item active" onclick="mostra_insegnamento('<?php echo $_GET['corso_laurea'];?>',1,0)">1</button> <?php
            }else{
                ?> <button id="1" class="list-group-item" onclick="mostra_insegnamento('<?php echo $_GET['corso_laurea'];?>',1,0)">1</button> <?php
            }
            for($j=2;$j<=$row[0];$j++)
                if($_GET['anno']==$j){
                    ?> <button id="<?php echo $j;?>" class="list-group-item active" onclick="mostra_insegnamento('<?php echo $_GET['corso_laurea'].'\','.$j;?>,0)"><?php echo $j;?></button> <?php
                }else{
                ?> <button id="<?php echo $j;?>" class="list-group-item" onclick="mostra_insegnamento('<?php echo $_GET['corso_laurea'].'\','.$j;?>,0)"><?php echo $j;?></button> <?php
                }
            if($_GET['anno']==0){
                ?> <button id="0" class="list-group-item active" onclick="mostra_insegnamento('<?php echo $_GET['corso_laurea'];?>',0,0)">Facoltativo</button> <?php
            }else{
                ?> <button id="0" class="list-group-item" onclick="mostra_insegnamento('<?php echo $_GET['corso_laurea'];?>',0,0)">Facoltativo</button> <?php
            }
            echo "</ul><br>";
            if($_GET['page']>0){
                ?> <button  class="btn btn-primary" id="precedente" onclick="mostra_insegnamento('<?php echo $_GET['corso_laurea'];?>',<?php echo $_GET['anno'];?>,<?php echo $_GET['page']-1;?>)">Precedente</button> <?php 
            }else{                              
                ?> <button  class="btn btn-primary" id="precedente" style="display:none" onclick="mostra_insegnamento('<?php echo $_GET['corso_laurea'];?>',<?php echo $_GET['anno'];?>,<?php echo $_GET['page']-1;?>)">Precedente</button> <?php                              
            }
    
            if($i==10){
                ?> <button  class="btn btn-primary" id="successivo" onclick="mostra_insegnamento('<?php echo $_GET['corso_laurea'];?>',<?php echo $_GET['anno'];?>,<?php echo $_GET['page']+1;?>)">Successivo</button> <?php
            }
        }else{
            echo "<p>Non ci sono corsi</p>";
        }
    }else if(isset($_GET['page'])&&isset($_GET['esame'])){
        pg_send_query($conn,"select distinct(data_ora) from esame where insegnamento='".pg_escape_string($_GET['esame'])."' and data_ora > current_date+7");
        $res=pg_get_result($conn);
        if (pg_result_error($res))
            echo pg_result_error($res);
        if(pg_num_rows($res) == 0){
            echo "<p> Non ci sono esami per questo esame</p>";
        }else{
            echo "<table class='table table-striped'>
                    <tr>
                        <th>Codice</th>
                        <th>Esame</th>
                        <th>Data e ora</th>
                        <th>Lettera</th>
                        <th>&nbsp;</th>
                    </tr>";
            while($row=pg_fetch_row($res)){
                pg_send_query($conn,"select nome_insegnamento,data_ora,lettere,docente,id from esame inner join insegnamento on esame.insegnamento = insegnamento.id where data_ora = '$row[0]' and insegnamento='".pg_escape_string($_GET['esame'])."' order by lettere");
                $res_esami=pg_get_result($conn);
                if (pg_result_error($res))
                    echo pg_result_error($res);
                while($row_esame=pg_fetch_row($res_esami)){
                    echo "<tr>
                            <td>".$_GET['esame']."</td>
                            <td>$row_esame[0]</td>
                            <td>$row_esame[1]</td>
                            <td>$row_esame[2]</td>
                            <td><a class='btn btn-primary' href='iscrizione_esame.php?data_ora=$row_esame[1]&id=$row_esame[4]&lettere=$row_esame[2]'>Iscriviti</a></td>
                        </tr>";
                    $i++;
                }
            }
            echo "</table>";
            if($_GET['page']>0){
                ?> <button  class="btn btn-primary" id="precedente" onclick="esame('<?php echo $_GET['esame']; ?>',false)">Precedente</button> <?php 
            }
    
            if($i==15){
                ?> <button  class="btn btn-primary" id="successivo" onclick="esame('<?php echo $_GET['esame']; ?>',true)">Successivo</button> <?php
            }
        }
    }else if(isset($_GET['page'])&&isset($_GET['laurea'])){
        pg_send_query($conn,"select * from laurea order by id limit 15 offset (".pg_escape_string(($_GET['page']))."*15)");
        $res=pg_get_result($conn);
        if (pg_result_error($res))
            echo pg_result_error($res);
        if(pg_num_rows($res)!=0){
            echo "<table class='table table-striped'>
                <tr>
                    <th> Laurea </th>
                    <th> Nome laurea </th>
                    <th> &nbsp; </th>
                </tr>";
            while($row=pg_fetch_row($res)){
                echo "<tr>  
                        <td>$row[0]</td>
                        <td>$row[1]</td>
                        <td><a  class='btn btn-primary' href='visualizza_corso.php?laurea_id=$row[0]'>Visualizza</a></td>
                    </tr>";
                $i++;
            }
            echo "</table>";
        }
        if($_GET['page']>0){
            ?> <button  class="btn btn-primary" id="precedente" onclick="mostra_lauree(false)">Precedente</button> <?php 
        }

        if($i==15){
            ?> <button  class="btn btn-primary" id="successivo" onclick="mostra_lauree(true)">Successivo</button> <?php
        }
    }else if(isset($_GET['page'])){
        pg_send_query($conn,"select nome_insegnamento,id from insegnamento where corso=(select frequenta from studenti) limit 15 offset (".pg_escape_string(($_GET['page']))."*15)");
        $res=pg_get_result($conn);
        if (pg_result_error($res))
            echo pg_result_error($res);
        if(pg_num_rows($res)!=0){
            echo "<h2 style='text-align:center'>Iscrizione esame</h2>
                <table class='table table-striped'>
                    <tr>
                        <th>Codice</th>
                        <th>Esame</th>
                        <th>&nbsp;</th>
                    </tr>";
            while($row = pg_fetch_row($res)){
                echo "<tr>
                        <td><b>$row[1]</b></td>
                        <td>$row[0]</td>
                        <td><a class='btn btn-primary' href='iscrizione_esame.php?esame=$row[1]'> Visualizza </a>
                    </tr>";
                $i++;
            }
            echo "</table>";
            if($_GET['page']>0){
                ?> <button  class="btn btn-primary" id="precedente" onclick="mostra_esame(false)">Precedente</button> <?php 
            }
    
            if($i==15){
                ?> <button  class="btn btn-primary" id="successivo" onclick="mostra_esame(true)">Successivo</button> <?php
            }
        }else{
            echo "<p>Non ci sono materie</p>";
        }
    }

?>