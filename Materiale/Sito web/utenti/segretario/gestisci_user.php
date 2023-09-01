<?php
    session_start();
    include "../../Database/config.php";
    if(!isset($_SESSION['utente']))
        header("Location: ../../login.php");

    function corso_docente($dbconn,$corsi,$ruolo,$email){
        $query="insert into responsabile(docente, insegnamento, ruolo) values";
        $array=explode(";",pg_escape_string($corsi));
        if(sizeof($array)>0){
            for($i=0;$i<sizeof($array);$i++){
                if($i!=0)
                    $query=$query.",";
                $query=$query."('$email','$array[$i]',$ruolo)";
            }
            pg_send_query($dbconn,$query);
            $res=pg_get_result($dbconn);
            if (pg_result_error($res)){
                echo pg_result_error($res);
                cancella_user($dbconn);
            }
        }
    }

    function cancella_user($dbconn){
        pg_send_query($dbconn,"delete from utenti where cf='".pg_escape_string($_POST['cf'])."'");
            $res=pg_get_result($dbconn);
            if (pg_result_error($res)){
                echo pg_result_error($res);
                echo "<p>ERRORE! L'utente è stato cancellato</p>";
            }
    }
?>
<html>
    <head>
        <title>Cancella Utente</title>
        <script src="cerca.js" type="text/javascript" language="javascript"></script>        
        <meta charset="utf-8">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
        <link href="../style.css" rel="stylesheet">
        <link rel="icon" href="../../img/favicon_0_0.ico" type="image/vnd.microsoft.icon">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.0/css/all.css" integrity="sha384-lZN37f5QGtY3VHgisS14W3ExzMWZxybE1SJSEsQp9S+oqd12jhcu+A56Ebc1zFSJ" crossorigin="anonymous">
    </head>
    <body>
        <?php include_once("../nav_bar.php"); ?>
        <div class="container-md">
        <?php if(sizeof($_GET)==0 && sizeof($_POST)==0) {?>
            <div class="row">
                <div class="input-group rounded col-md-5">
                    <input type="text" class="form-control rounded" placeholder="Search" aria-label="Search" aria-describedby="search-addon"  onkeyup="mostra(this.value)"/>
                    <span class="input-group-text border-0" id="search-addon">
                        <i class="fas fa-search"></i>
                    </span>
                </div>
                <div style="position: absolute;z-index: +1;background-color:rgba(255,255,255,0.95);width:50%;margin-top:75px" class="d-grid col-6 mx-auto ">
                    <table id="risp_search" class='table table-striped' style='z-index:1;'></table>
                </div>
            </div>
            <?php 
                }
                if(isset($_GET['aggiungi'])){ 
            ?>
            <h2>Crea user</h2>
            <form action="gestisci_user.php" method="POST" class="row g-3 needs-validation">
                <div class="col-md-8">
                    <label for="nome" class="form-label">Nome</label>
                    <div class="input-group has-validation">
                        <input class="form-control" type="text" name="nome" placeholder="Nome user"/>
                    </div>
                    <label for="cognome" class="form-label">Cognome</label>
                    <div class="input-group has-validation">
                        <input class="form-control" type="text" name="cognome" placeholder="Cognome user"/>
                    </div>
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group has-validation">
                        <input class="form-control" type="password" name="password" placeholder="Password user"/>
                    </div>
                    <label for="cf" class="form-label">Codice Fiscale</label>
                    <div class="input-group has-validation">
                        <input class="form-control" type="text" name="cf" placeholder="Codice fiscale user"/>
                    </div>
                    <label for="password" class="form-label">Tipologia utente</label>
                    <div class="input-group has-validation">
                        <select class="form-control" name="tipologia" id="tipologia" class="form-control">
                            <option class="form-control">-- Select --</option>
                            <option class="form-control" value="studente">Studente</option>
                            <option class="form-control" value="docente">Docente</option>
                            <option class="form-control" value="segretario">Segretario</option>
                        </select>
                    </div>
                    <br>
                    <input class='btn btn-primary' type="submit"/>
                </div>
                <div class="col-md-4" style="padding-left:10px">
                    <label class="form-label" id="label_modifica"></label>
                    <div class="input-group has-validation" id="risposta_form_1">
                        <input type="hidden" id="rimuovi1"/>
                    </div>
                    <label class="form-label" id="label_modifica_2"></label>
                    <div class="input-group has-validation" id="risposta_form_2">
                        <input type="hidden" id="rimuovi2"/>
                    </div>
                </div>
                    <script>
                        document.getElementById("tipologia").addEventListener('change', function (e) {
                            if (e.target.value === "studente") {                                
                                document.getElementById("rimuovi1").remove();
                                document.getElementById("rimuovi2").remove();
                                var input = document.createElement("input");
                                var parent = document.getElementById("risposta_form_1");

                                document.getElementById("label_modifica").innerHTML="Inserire il Corso di laurea dello studente";
                                document.getElementById("label_modifica_2").innerHTML="";
                                input.setAttribute('type', 'text');
                                input.setAttribute('id', 'rimuovi1');
                                input.setAttribute('name', 'corso_studente');
                                input.setAttribute('placeholder', 'Corso di laurea');
                                input.setAttribute('class', 'form-control');
                                parent.appendChild(input);

                                parent = document.getElementById("risposta_form_2");
                                input = document.createElement("input");
                                input.setAttribute('type', 'hidden');
                                input.setAttribute('id', 'rimuovi2');
                                parent.appendChild(input);
                                
                            }else if (e.target.value === "docente") {                                  
                                document.getElementById("rimuovi1").remove();
                                document.getElementById("rimuovi2").remove();
                                var input = document.createElement("input");
                                var parent = document.getElementById("risposta_form_1");

                                document.getElementById("label_modifica").innerHTML="Inserire il corso in cui è responsabile (separara con ; )";
                                document.getElementById("label_modifica_2").innerHTML="Inserire il corso in cui è docente (separara con ; )";
                                input.setAttribute('type', 'text');
                                input.setAttribute('id', 'rimuovi1');
                                input.setAttribute('name', 'docente_responsabile');
                                input.setAttribute('placeholder', 'Responsabile');
                                input.setAttribute('class', 'form-control');
                                parent.appendChild(input);

                                parent = document.getElementById("risposta_form_2");
                                input = document.createElement("input");
                                input.setAttribute('type', 'text');
                                input.setAttribute('id', 'rimuovi2');
                                input.setAttribute('name', 'docente_docente');
                                input.setAttribute('class', 'form-control');
                                input.setAttribute('placeholder', 'Docente');
                                parent.appendChild(input);
                                
                            }else{
                                document.getElementById("rimuovi1").remove();
                                document.getElementById("rimuovi2").remove();
                                var input = document.createElement("input");
                                var parent = document.getElementById("risposta_form_1");

                                document.getElementById("label_modifica").innerHTML="";
                                document.getElementById("label_modifica_2").innerHTML="";
                                input.setAttribute('type', 'hidden');
                                input.setAttribute('id', 'rimuovi1');
                                parent.appendChild(input);

                                parent = document.getElementById("risposta_form_2");
                                input = document.createElement("input");
                                input.setAttribute('type', 'hidden');
                                input.setAttribute('id', 'rimuovi2');
                                parent.appendChild(input);
                            }
                        });
                    </script>
            </form>
            <?php 
                }else{
                    if(isset($_POST['nome'])&&isset($_POST['cognome'])&&isset($_POST['password'])&&isset($_POST['tipologia'])&&isset($_POST['cf'])){ 
                        pg_send_query($conn,"insert into Utenti(nome, cognome, password, cf, tipologia) values('".pg_escape_string($_POST['nome'])."','".pg_escape_string($_POST['cognome'])."','".pg_escape_string($_POST['password'])."','".pg_escape_string($_POST['cf'])."','".pg_escape_string($_POST['tipologia'])."')");
                        if (pg_result_error($res))
                            echo pg_result_error($res);
                        else{
                            if(isset($_POST['corso_studente'])){
                                pg_send_query($conn,"update studenti set frequenta='".pg_escape_string($_POST['corso_studente'])."' where email = (select email from utenti where cf='".pg_escape_string($_POST['cf'])."');");
                                $res=pg_get_result($conn);
                                if (pg_result_error($res))
                                    echo pg_result_error($res);
                               echo "<p>Utente inserito con il corso selezionato</p>";
                            }else if(isset($_POST['docente_responsabile'])&&isset($_POST['docente_docente'])){
                                pg_send_query($dbconn,"select email from utenti where cf='".pg_escape_string($_POST['cf'])."'");
                                $res=pg_get_result($dbconn);
                                if (pg_result_error($res))
                                    echo pg_result_error($res);
                                $email=pg_fetch_row($res)[0];
                                corso_docente($conn,$_POST['docente_responsabile'],1,$email);
                                corso_docente($conn,$_POST['docente_docente'],2,$email);
                                echo "<p>Insegnamenti aggiunti</p>";
                            }
                            echo "<p>Utente aggiunto con successo</p>";
                        }
                        
                    } else {
                        if(isset($_POST['email'])&&isset($_POST['password'])&&isset($_POST['password_conferma'])){
                            if($_POST['password']==$_POST['password_conferma'] && strcmp($_POST['password'],"")!=0){
                                pg_send_query($conn,"update utenti set password ='".pg_escape_string($_POST['password'])."' where email='".pg_escape_string($_POST['email'])."'");
                                $res=pg_get_result($conn);
                                if (pg_result_error($res))
                                    echo pg_result_error($res);
                                else
                                    echo "<p>Cambio password riuscito</p>";
                            }
                            if(isset($_POST['docente_responsabile'])){
                                pg_send_query($conn,"delete from responsabile where docente='".pg_escape_string($_POST['email'])."'");
                                $res=pg_get_result($conn);
                                if (pg_result_error($res))
                                    echo pg_result_error($res);
                                corso_docente($conn,$_POST['docente_responsabile'],1,pg_escape_string($_POST['email']));
                                corso_docente($conn,$_POST['docente_docente'],2,pg_escape_string($_POST['email']));
                                echo "<p>Cambio degli insegnamenti in cui insegni riuscito</p>";
                            }else if(isset($_POST['frequenta'])){
                                pg_send_query($conn,"update studenti set frequenta='".pg_escape_string($_POST['frequenta'])."' where email='".pg_escape_string($_POST['email'])."'");
                                $res=pg_get_result($conn);
                                if (pg_result_error($res))
                                    echo pg_result_error($res);
                                else
                                    echo "<p>Corso cambiato</p>";
                            }
                        }else{
                            if(isset($_GET['email'])){
                                if(isset($_GET['cancella'])){
                                    pg_send_query($conn,"delete from utenti where email ='".pg_escape_string($_GET['email'])."'");
                                    $res=pg_get_result($conn);
                                    if (pg_result_error($res))
                                        echo pg_result_error($res);
                                    else
                                        echo "<p>Cancellazione riuscita</p>";
                                }else if(isset($_GET['modifica'])){
            ?>
            <p><b>Modifica la password dello user </b><?php echo $_GET['email']; ?></p>
            <form action='gestisci_user.php' method='POST' class="row g-3 needs-validation">
                <div class="col-md-8">
                    <label for="password" class="form-label">Nuova password</label>
                    <div class="input-group has-validation">
                        <input class="form-control" type ='password' id='password' name='password' placeholder="Nuova password"/>
                    </div>
                    <label for="password_conferma" class="form-label"> Conferma la nuova password</label>
                    <div class="input-group has-validation">
                        <input class="form-control" type ='password' id='password_conferma' name='password_conferma' placeholder="Conferma password"/>
                    </div>
                    <input class="form-control" type ='hidden' id='email' name='email' value='<?php echo $_GET['email']; ?>'/>
                    <?php
                        pg_send_query($conn,"select tipologia from utenti where email='".pg_escape_string($_GET['email'])."'");
                        $res=pg_get_result($conn);
                        if (pg_result_error($res))
                            echo pg_result_error($res);
                        $row=pg_fetch_row($res);
                    
                    if($row[0]=='docente'){
                        pg_send_query($conn,"select insegnamento from responsabile where docente='".pg_escape_string($_GET['email'])."' and ruolo=1");
                        $res=pg_get_result($conn);
                        if (pg_result_error($res))
                            echo pg_result_error($res);
                    ?>
                    <label for="docente_responsabile" class="form-label"> Insegnamenti in cui è responsabile</label>
                    <div class="input-group has-validation">
                        <input class="form-control" type ='text' id='docente_responsabile' name='docente_responsabile' value="<?php $i=0; while($ris=pg_fetch_row($res)){ if($i==0) echo $ris[0]; else echo ";",$ris[0]; $i++;}?>" placeholder="Responsabile"/>
                    </div>
                    <?php
                        pg_send_query($conn,"select insegnamento from responsabile where docente='".pg_escape_string($_GET['email'])."' and ruolo=2");
                        $res=pg_get_result($conn);
                        if (pg_result_error($res))
                            echo pg_result_error($res);
                    ?>
                    <label for="docente_docente" class="form-label"> Insegnamenti in cui insegna</label>
                    <div class="input-group has-validation">
                        <input class="form-control" type ='text' id='docente_docente' name='docente_docente' value="<?php $i=0; while($ris=pg_fetch_row($res)){ if($i==0) echo $ris[0]; else echo ";",$ris[0]; $i++;}?>" placeholder="Docente"/>
                    </div>
                    <script> mostra_corsi_propedeutici('*',true); </script>
                    <?php 
                        }else if($row[0]=='studente'){                      
                            pg_send_query($conn,"select frequenta from studenti where email='".pg_escape_string($_GET['email'])."'");
                            $res=pg_get_result($conn);
                            if (pg_result_error($res))
                                echo pg_result_error($res);
                            $row=pg_fetch_row($res);
                        ?>
                        <label for="docente_docente" class="form-label"> Corso in cui è iscritto</label>
                        <div class="input-group has-validation">
                            <input class="form-control" name="frequenta" value="<?php echo $row[0];?>" placeholder="Frequenta"/>
                        </div>
                    <?php }?>

                    <input class="btn btn-primary" type="submit" style="width:100px;margin-top:25px"/>
                </div>
            </form>
            <?php
                                }
                            }else{
            ?>
                <br><br><a class='btn btn-primary' href="gestisci_user.php?aggiungi=1">Inserisci nuovo user</a>
                <script> mostra_utenti(true); </script>
            <?php
                            }
                        }
                    }
                }
            ?><div id="risposta"></div><?php
                if((sizeof($_GET)>0 &&!isset($_GET['page']))||sizeof($_POST)>0){
                    echo "<a class='btn btn-primary 'href='gestisci_user.php'>Torna indietro</a>";
                }
            ?>
        </div>
    </body>
</html>
