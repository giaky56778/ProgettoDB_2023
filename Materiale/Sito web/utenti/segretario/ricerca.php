<?php
    session_start();
    include "../../Database/config.php";
    if(!isset($_SESSION['utente']))
        header("Location: ../../login.php");

    $i=0;
    if(isset($_GET['stringa'])){
        $query="select email 
        from utenti 
        where email like '%".pg_escape_string($_GET['stringa'])."%' or nome like '%".pg_escape_string($_GET['stringa'])."%' or cognome like '%".pg_escape_string($_GET['stringa'])."%' limit 5";
        pg_send_query($conn,$query);
        $res=pg_get_result($conn);
        
        if (pg_result_error($res))
            echo pg_result_error($res);
       
        while($row=pg_fetch_row($res)){
            echo "<tr><td><p >$row[0]</p></td>";
            echo "<td><a href='gestisci_user.php?email=$row[0]&cancella=1' class='btn btn-primary' style='color:white'>Cancella</a></td>";
            echo "<td><a href='gestisci_user.php?email=$row[0]&modifica=1' class='btn btn-primary' style='color:white'>Modifica</a></td></tr>";
        }

    }else if(isset($_GET['corso'])&&isset($_GET['page'])){
        $string="";
        $query="";
        if(strcmp($_GET['corso'],'*')!=0){
            $query="select id,nome_insegnamento from insegnamento where corso='".pg_escape_string($_GET['corso'])."' limit 15 offset (".pg_escape_string(($_GET['page']))."*15)";
        }else{
            $query="select id,nome_insegnamento from insegnamento limit 15 offset (".pg_escape_string(($_GET['page']))."*15)";
        }          
        pg_send_query($conn,$query);
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
            ?> <button class='btn btn-primary' id="precedente" style="display:inline-block" onclick="mostra_corsi_propedeutici('<?php echo $_GET['corso'];?>',false)">Precedente</button> <?php 
        }else{                              
            ?> <button class='btn btn-primary' id="precedente" style="display:none" onclick="mostra_corsi_propedeutici('<?php echo $_GET['corso'];?>',false)">Precedente</button> <?php                              
        }

        if($i==15){
            ?> <button class='btn btn-primary' id="successivo" style="display:inline-block" onclick="mostra_corsi_propedeutici('<?php echo $_GET['corso']; ?>',true)">Successivo</button> <?php
        }else{
            ?> <button class='btn btn-primary' id="successivo" style="display:none" onclick="mostra_corsi_propedeutici('<?php echo $_GET['corso']; ?>',true)">Successivo</button> <?php
        }
    }else if(isset($_GET['laurea_id'])&&isset($_GET['page'])){
        
        pg_send_query($conn,"select sum(cfu) > (anni*60),nome_corso,anni from corso left join insegnamento on corso.nome_corso = insegnamento.corso where laurea='".pg_escape_string($_GET['laurea_id'])."' group by nome_corso, anni limit 15 offset (".pg_escape_string(($_GET['page']))."*15)");
        $res=pg_get_result($conn);
        if (pg_result_error($res))
            echo pg_result_error($res);
        else
            echo "<br><a style='margin-bottom:25px' class='btn btn-primary' href='gestisci_laurea.php?aggiungi_corso=1&id_laurea=".$_GET['laurea_id']."'>Aggiungi corso</a>";
        if(pg_num_rows($res)!=0){
            echo "<h3> Lauree ".$_GET['laurea_id']."</h3>
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
                        <td><a class='btn btn-primary' href='gestisci_laurea.php?corso=$row[1]&page=0&anno=1'>Modificia</td>
                    </tr>";
                $i++;
            }
            echo "</table>";
            if($_GET['page']>0){
                ?> <button class='btn btn-primary' id="precedente" style="display:inline-block" onclick="mostra_corsi('<?php echo $_GET['laurea_id'];?>',false)">Precedente</button> <?php 
            }else{                              
                ?> <button class='btn btn-primary' id="precedente" style="display:none" onclick="mostra_corsi('<?php echo $_GET['laurea_id'];?>',false)">Precedente</button> <?php                              
            }
    
            if($i==15){
                ?> <button class='btn btn-primary' id="successivo" style="display:inline-block" onclick="mostra_corsi('<?php echo $_GET['laurea_id']; ?>',true)">Successivo</button> <?php
            }else{
                ?> <button class='btn btn-primary' id="successivo" style="display:none" onclick="mostra_corsi('<?php echo $_GET['laurea_id']; ?>',true)">Successivo</button> <?php
            }
        }
    }else if(isset($_GET['corso_laurea'])&&isset($_GET['page'])&&isset($_GET['anno'])){
        
        echo "<a style='margin-bottom:25px' class='btn btn-primary' href='gestisci_laurea.php?insegnamento=1&corso=".$_GET['corso_laurea']."'>Inserisci un nuovo insegnamento</a>";
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
                        <td><a class='btn btn-primary' href='gestisci_laurea.php?insegnamento_id=$row[0]'>Modifica</a></td>
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
    }else if(isset($_GET['user'])&&isset($_GET['page'])){
        
        pg_send_query($conn,"select email,nome,cognome,tipologia,cf from utenti where email <> current_user order by cf limit 15 offset (".pg_escape_string(($_GET['page']))."*15)");
        $res=pg_get_result($conn);
        if (pg_result_error($res))
            echo pg_result_error($res);
        if($num=pg_num_rows($res)){
            echo "<h2>Gestione user</h2>
                    <p>Seleziona uno user</p>
                    <table class='table table-striped'>
                    <tr>
                        <th>Email</th>
                        <th>Nome e cognome</th>
                        <th>Tipologia</th>
                        <th>Codice fiscale</th>
                        <th>&nbsp;</th>
                        <th>&nbsp;</th>
                    </tr>";
            while($row=pg_fetch_row($res)){
                echo "<tr>
                        <td>$row[0]</td>
                        <td>$row[1] $row[2]</td>
                        <td>$row[3]</td>
                        <td>$row[4]</td>
                        <td><a class='btn btn-primary' href='gestisci_user.php?email=$row[0]&cancella=1'>Cancella</a></td>
                        <td><a class='btn btn-primary' href='gestisci_user.php?email=$row[0]&modifica=1'>Modifica</a></td>
                    </tr>";
                $i++;    
            }
            echo "</table>";
        }
        if($_GET['page']>0){
            ?> <button class='btn btn-primary' id="precedente" style="display:inline-block" onclick="mostra_utenti(false)">Precedente</button> <?php 
        }else{                              
            ?> <button class='btn btn-primary' id="precedente" style="display:none" onclick="mostra_utenti(false)">Precedente</button> <?php                              
        }

        if($i==15){
            ?> <button class='btn btn-primary' id="successivo" style="display:inline-block" onclick="mostra_utenti(true)">Successivo</button> <?php
        }else{
            ?> <button class='btn btn-primary' id="successivo" style="display:none" onclick="mostra_utenti(true)">Successivo</button> <?php
        }

    }else if(isset($_GET['page'])){
        pg_send_query($conn,"select * from laurea order by id limit 15 offset (".pg_escape_string(($_GET['page']))."*15)");
        $res=pg_get_result($conn);
        if (pg_result_error($res))
            echo pg_result_error($res);
        else
            echo "<br><a style='margin-bottom:25px' class='btn btn-primary' href='gestisci_laurea.php?aggiungi_laurea=1'>Aggiungi laurea</a>";
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
                        <td><a class='btn btn-primary' href='gestisci_laurea.php?laurea_id=$row[0]'>Gestisci</a></td>
                    </tr>";
                $i++;
            }
            echo "</table>";
        }
        if($_GET['page']>0){
            ?> <button class='btn btn-primary' id="precedente" style="display:inline-block" onclick="mostra_lauree(false)">Precedente</button> <?php 
        }else{                              
            ?> <button class='btn btn-primary' id="precedente" style="display:none" onclick="mostra_lauree(false)">Precedente</button> <?php                              
        }

        if($i==15){
            ?> <button class='btn btn-primary' id="successivo" style="display:inline-block" onclick="mostra_lauree(true)">Successivo</button> <?php
        }else{
            ?> <button class='btn btn-primary' id="successivo" style="display:none" onclick="mostra_lauree(true)">Successivo</button> <?php
        }
    }
?>