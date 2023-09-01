<?php
    session_start();
    include "../../Database/config.php";
    if(!isset($_SESSION['utente']))
        header("Location: ../../login.php");
?>
<html>
    <head>
        <title> Gestisci laurea </title>        
        <script src="cerca.js" type="text/javascript" language="javascript"></script>        
        <meta charset="utf-8">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
        <link href="../style.css" rel="stylesheet">
        <link rel="icon" href="../../img/favicon_0_0.ico" type="image/vnd.microsoft.icon">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
    </head>
    <body>
        <?php include_once("../nav_bar.php"); ?>
        <div class="container-md">
            <?php
                if(isset($_POST['nome_insegnamento'])&&isset($_POST['anno'])&&isset($_POST['descrizione'])&&isset($_POST['semestre'])&&isset($_POST['cfu'])&&isset($_POST['codice_insegnamento'])&&isset($_POST['id'])){
                    pg_send_query($conn,"insert into insegnamento(id, nome_insegnamento, descrizione, anno, semestre, cfu, corso) values('".pg_escape_string($_POST['codice_insegnamento'])."','".pg_escape_string($_POST['nome_insegnamento'])."','".pg_escape_string($_POST['descrizione'])."','".pg_escape_string($_POST['anno'])."','".pg_escape_string($_POST['semestre'])."','".pg_escape_string($_POST['cfu'])."','".pg_escape_string($_POST['id'])."')");
                    $res=pg_get_result($conn);
                    if (pg_result_error($res))
                        echo pg_result_error($res);
                    else{
                        if(isset($_POST['Propedeuticita'])){
                            $prop = explode (';',pg_escape_string($_POST['Propedeuticita']));
                            $query = "insert into propedeutico(insegnamento, propedeutico) values ";
                            for($i=0;$i<sizeof($prop);$i++){
                                if($i!=0)
                                    $query=$query.",";
                                $query=$query."('$prop[$i]','".pg_escape_string($_POST['codice_insegnamento'])."')";
                            } 
                            pg_send_query($conn,$query);
                            $res=pg_get_result($conn);
                            if (pg_result_error($res))
                                echo pg_result_error($res);
                            else
                                echo "<p>Propedeuticità inserita/e con successo</p>";
                        }
                        echo "<p>Insegnamento inserito con successo</p>";
                    }
                }else if(isset($_GET['insegnamento'])&&isset($_GET['corso'])){
            ?>
                <?php
                        pg_send_query($conn,"select anni as max_anni from corso where nome_corso='".pg_escape_string($_GET['corso'])."'");
                        $res=pg_get_result($conn);
                        if (pg_result_error($res))
                            echo pg_result_error($res);
                        $row=pg_fetch_row($res);
                    ?>
                
                <div class="col-md-8">
                    <form action="gestisci_laurea.php" method="POST">
                        <h3>Aggiungi un nuovo insegnamento a <?php echo $_GET['corso'];?></h3><br>
                        <label class="form-label" for="codice_insegnamento">Codice Corso</label>
                        <div class="input-group has-validation">
                            <input class="form-control" type="text" name="codice_insegnamento" placeholder="Codice corso"/>
                        </div>
                        <br><label class="form-label" for="nome_insegnamento">Nome insegnamento</label>
                        <div class="input-group has-validation">
                            <input class="form-control" type="text" name="nome_insegnamento" placeholder="Descrizione"/>
                        </div>
                        <br><label class="form-label" for="anno">Anno previsto</label>
                        <div class="input-group has-validation">
                            <select class="form-select" name="anno" id="anno">
                                <?php 
                                    for($i=0;$i<=$row[0];$i++){
                                        if($i==0)
                                            echo "<option value='$i'>Facoltativo</option>";
                                        else
                                            echo "<option value='$i'>$i</option>";
                                    }
                                ?>
                            </select>
                        </div>
                        <br><label class="form-label" for="semestre">Semestre</label>
                        <div class="input-group has-validation">
                            <select class="form-select" name="semestre" id="semestre">
                                <option value="0">Tutto l'anno</option>
                                <option value="1">1° semestre</option>
                                <option value="2">2° semestre</option>
                            </select>
                        </div>
                        <br><label class="form-label" for="cfu">CFU</label>
                        <div class="input-group has-validation">
                            <input class="form-control" type="number" name="cfu" placeholder="CFU" min="1" max="60"/>
                        </div>
                        <br><label class="form-label" for="descrizione">Descrizione</label>
                        <div class="input-group has-validation">
                            <input class="form-control" type="text" name="descrizione" placeholder="Descrizione"/>
                        </div>
                        <br><label class="form-label" for="Propedeuticita">Propedeuticità (separarle con ; )</label>
                        <div class="input-group has-validation">
                            <input class="form-control" type="text" name="Propedeuticita" placeholder="Propedeuticità"/>
                        </div>
                        <input type="hidden" name="id" value="<?php echo $_GET['corso'];?>"/>
                        <br><input type="submit"/>
                    </form>
                </div>
                
                <script>
                    mostra_corsi_propedeutici('<?php echo $_GET['corso'];?>',true);
                </script>
            <?php
                }else if(isset($_POST['nome_corso'])&&isset($_POST['laurea'])&&isset($_POST['anni_corso'])){
                    pg_send_query($conn,"insert into Corso(nome_corso, anni, laurea) values('".pg_escape_string($_POST['nome_corso'])."','".pg_escape_string($_POST['anni_corso'])."','".pg_escape_string($_POST['laurea'])."')");
                    $res=pg_get_result($conn);
                    if (pg_result_error($res))
                        echo pg_result_error($res);
                    else
                        echo "<p>Corso aggiunto con successo</p>";
                }else if(isset($_GET['aggiungi_corso'])&&isset($_GET['id_laurea'])){
            ?>
                <h3>Aggiungi un nuovo corso</h3>
                <form action="gestisci_laurea.php" method="POST">
                    <label class="form-label" for="nome_corso">Nome Corso</label>
                    <div class="input-group has-validation">
                        <input class="form-control" type="text" name="nome_corso" placeholder="Nome corso"/>
                    </div>
                    <div class="input-group has-validation">
                        <input class="form-control" type="hidden" name="laurea" value="<?php echo $_GET['id_laurea'];?>"/>
                    </div>
                    <br><label class="form-label" for="anni_corso">Anni di durata del corso</label>
                    <div class="input-group has-validation">
                        <select class="form-select" name="anni_corso" id="anni_corso">
                            <option value="3" selected>Triennale</option>
                            <option value="2" >Magistrale</option>
                            <option value="5" >Magistrale a ciclo unico (5 anni)</option>
                            <option value="6" >Magistrale a ciclo unico (6 anni)</option>
                        </select>
                    </div>
                    <br><input class='btn btn-primary' type="submit"/>
                </form>
            <?php
                }else if(isset($_POST['id_laurea'])&&isset($_POST['nome_laurea'])){
                    pg_send_query($conn,"insert into Laurea(id, nome_laurea) values('".pg_escape_string($_POST['id_laurea'])."','".pg_escape_string($_POST['nome_laurea'])."')");
                    $res=pg_get_result($conn);
                    if (pg_result_error($res))
                        echo pg_result_error($res);
                    else
                        echo "<p>Laurea aggiunta con successo</p>";
                }else if(isset($_GET['aggiungi_laurea'])){
            ?>
                <h3>Aggiungi una nuova laurea</h3>
                <div class="col-md-8">
                    <form action="gestisci_laurea.php" method="POST">
                        <label class="form-label" for="nome_laurea">Nome Laurea</label>
                        <div class="input-group has-validation">
                            <input class="form-control" type="text" name="nome_laurea" placeholder="Nome laurea"/>
                        </div>
                        <br><label class="form-label" for="id_laurea">Codice della Laurea</label>
                        <div class="input-group has-validation">
                            <input class="form-control" type="text" name="id_laurea" placeholder="Codice laurea"/>
                        </div>
                        <br><input class='btn btn-primary' type="submit"/>
                    </form>
                </div>
            <?php
                }
                else if(isset($_GET['laurea_id'])){
                ?><script> mostra_corsi('<?php echo $_GET['laurea_id'];?>',true) </script><?php
                }else if(isset($_GET['corso'])){
                ?><script> mostra_insegnamento('<?php echo $_GET['corso'];?>',1,0) </script><?php 
                }else if(isset($_GET['insegnamento_id'])){
            ?>
                <form action="gestisci_laurea.php" method="POST" class="row g-3 needs-validation">
                    <div class="col-md-8">
                        <?php
                            pg_send_query($conn,"select anni as max_anni,cfu,anno,semestre,descrizione,nome_insegnamento,corso from insegnamento inner join corso on insegnamento.corso = corso.nome_corso where id='".pg_escape_string($_GET['insegnamento_id'])."'");
                            $res=pg_get_result($conn);
                            if (pg_result_error($res))
                                echo pg_result_error($res);
                            $row=pg_fetch_row($res);
                        ?>
                        <h3>Modifica dell'insegnamento <?php echo $_GET['insegnamento_id']." (".$row[5];?>)</h3>
                        <label for="anno" class="form-label">Anno previsto</label>
                        <div class="input-group has-validation">
                            <select class="form-select" name="anno" id="anno">
                                <?php 
                                    for($i=0;$i<=$row[0];$i++){
                                        if($i==0)
                                            if($i==$row[2])
                                                echo "<option value='$i' selected>Facoltativo</option>";
                                            else
                                                echo "<option value='$i'>Facoltativo</option>";
                                        else
                                            if($i==$row[2])
                                                echo "<option value='$i' selected>$i</option>";
                                            else
                                                echo "<option value='$i'>$i</option>";
                                    }
                                ?>
                            </select>
                        </div>
                        <br><label for="semestre" class="form-label">Semestre</label>
                        <div class="input-group has-validation">
                            <select class="form-select" name="semestre" id="semestre">
                                <option value="0" <?php if($row[3]==0) echo"selected";?>>Tutto l'anno</option>
                                <option value="1" <?php if($row[3]==1) echo"selected";?>>1° semestre</option>
                                <option value="2" <?php if($row[3]==2) echo"selected";?>>2° semestre</option>
                            </select>
                        </div>
                        <br><label for="cfu" class="form-label">CFU</label>
                        <div class="input-group has-validation">
                            <input class="form-control" type="number" name="cfu" placeholder="CFU" min="1" max="60" value="<?php echo $row[1];?>"/>
                        </div>
                        <br><label for="descrizione" class="form-label">Descrizione</label>
                        <div class="input-group has-validation">
                            <input class="form-control" type="text" name="descrizione" placeholder="Descrizione" value="<?php echo $row[4];?>"/>
                        </div>
                        <?php
                            pg_send_query($conn,"select insegnamento from propedeutico where propedeutico='".pg_escape_string($_GET['insegnamento_id'])."'");
                            $res=pg_get_result($conn);
                            if (pg_result_error($res))
                                echo pg_result_error($res);
                        ?>
                        <br><label for="Propedeuticita" class="form-label">Propedeuticità (separarle con ; )</label>
                        <div class="input-group has-validation">
                            <input class="form-control" type="text" name="Propedeuticita" placeholder="Propedeuticità" value="<?php $i=0; while($ris=pg_fetch_row($res)){ if($i==0) echo $ris[0]; else echo ";",$ris[0]; $i++;}?>"/>
                        </div>
                        <input type="hidden" name="id" value="<?php echo $_GET['insegnamento_id'];?>"/>
                        <br><input class='btn btn-primary' type="submit"/>
                    </div>
                </form>
                <script> mostra_corsi_propedeutici('<?php echo $row[6];?>',true); </script>
            <?php
                }else if(isset($_POST['anno'])&&isset($_POST['semestre'])&&isset($_POST['cfu'])&&isset($_POST['descrizione'])&&isset($_POST['id'])){
                    pg_send_query($conn,"update insegnamento set descrizione='".pg_escape_string($_POST['descrizione'])."', anno='".pg_escape_string($_POST['anno'])."', semestre='".pg_escape_string($_POST['semestre'])."', cfu='".pg_escape_string($_POST['cfu'])."' where id='".pg_escape_string($_POST['id'])."'");
                    $res=pg_get_result($conn);
                    if (pg_result_error($res))
                        echo pg_result_error($res);
                    else{
                        if(isset($_POST['Propedeuticita'])){
                            pg_send_query($conn,"delete from propedeutico where propedeutico='".pg_escape_string($_POST['id'])."' ");
                            $res=pg_get_result($conn);
                            if (pg_result_error($res))
                                echo pg_result_error($res);
        
                            $prop = explode (';',pg_escape_string($_POST['Propedeuticita']));
                            $query = "insert into propedeutico(insegnamento, propedeutico) values ";
                            $flag=false;
                            for($i=0;$i<sizeof($prop);$i++){
                                $flag=true;
                                $query=$query."('$prop[$i]','".pg_escape_string($_POST['id'])."'),";
                            } 
                            if($flag){
                                pg_send_query($conn,substr($query,0,strlen($query)-1));
                                $res=pg_get_result($conn);
                                if (pg_result_error($res))
                                    echo pg_result_error($res);
                                else
                                    echo "<p>Propedeuticità inserita/e con successo</p>";
                            }else{
                                echo "<p>Propedeuticità già presenti</p>";
                            }
                        }
                        echo "<p>Insegnamento aggiornato con successo</p>";
                    }
                }else{ ?><script> mostra_lauree(true); </script> <?php }
            ?><div id="risposta"></div><?php
                if(sizeof($_POST)>0 || sizeof($_GET)>0)
                    echo "<br><a href='gestisci_laurea.php' class='btn btn-primary'>Torna indietro</a>";
            ?>
        </div>
    </body>
</html>