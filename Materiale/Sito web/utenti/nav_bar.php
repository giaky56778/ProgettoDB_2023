<?php 
    $prefix;
    $file=basename($_SERVER['PHP_SELF']);
    if($file=='cambia_password.php')
        $prefix=$_SESSION['tipologia'];
?>
<div class="nav">
    <nav>
        <ul class="nav nav-pills nav-fill">
            <li class="nav-item">
            <?php 
                if(isset($prefix))
                    echo '<a class="nav-link " id="'.$prefix.'.php" aria-current="page" href="'.$prefix.'/'.$_SESSION['tipologia'].'.php">Home</a>';
                else
                    echo '<a class="nav-link " id="'.$_SESSION['tipologia'].'.php" aria-current="page" href="'.$_SESSION['tipologia'].'.php">Home</a>';

                if ($_SESSION['tipologia']=='studente') {
                    if(isset($prefix)){ 
            ?>  
                <li class="nav-item">
                    <a class="nav-link " id="carriera.php" aria-current="page" href="<?php echo $prefix.'/';?>carriera.php">Carriera</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link " id="iscrizione_esame.php" aria-current="page" href="<?php echo $prefix.'/';?>iscrizione_esame.php">Iscrizione ad un esame</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link " id="accetta_valutazione.php" aria-current="page" href="<?php echo $prefix.'/';?>accetta_valutazione.php">Accetta voto</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link " id="visualizza_corso.php" aria-current="page" href="<?php echo $prefix.'/';?>visualizza_corso.php">Visualizza Corsi</a>
                </li>
            <?php }else { ?>
                <li class="nav-item">
                    <a class="nav-link " id="carriera.php" aria-current="page" href="carriera.php">Carriera</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link " id="iscrizione_esame.php" aria-current="page" href="iscrizione_esame.php">Iscrizione ad un esame</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link " id="accetta_valutazione.php" aria-current="page" href="accetta_valutazione.php">Accetta voto</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link " id="visualizza_corso.php" aria-current="page" href="visualizza_corso.php">Visualizza Corsi</a>
                </li>
            <?php 
                    } 
                }else if($_SESSION['tipologia']=='docente'){
                    if(isset($prefix)) {
            ?>  
                <li class="nav-item">
                    <a class="nav-link " id="gestione_esami.php" aria-current="page" href="<?php echo $prefix.'/';?>gestione_esami.php">Gestione esami futuri</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link " id="voti.php" aria-current="page" href="<?php echo $prefix.'/';?>voti.php">Gestione esami passati</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link " id="inserisci_esami.php" aria-current="page" href="<?php echo $prefix.'/';?>inserisci_esami.php">Inserisci esame</a>
                </li>
            <?php }else{ ?>
                <li class="nav-item">
                    <a class="nav-link " id="gestione_esami.php" aria-current="page" href="gestione_esami.php">Gestione esami futuri</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link " id="voti.php" aria-current="page" href="voti.php">Gestione esami passati</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link " id="inserisci_esami.php" aria-current="page" href="inserisci_esami.php">Inserisci esame</a>
                </li>
            <?php } 
                    } else if($_SESSION['tipologia']=='segretario') {
                        if(isset($prefix)) {
            ?>
                <li class="nav-item">
                    <a class="nav-link " id="gestisci_user.php" aria-current="page" href="<?php echo $prefix.'/';?>gestisci_user.php">Gestisti user</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link " id="gestisci_laurea.php" aria-current="page" href="<?php echo $prefix.'/';?>gestisci_laurea.php">Gestisci Laurea</a>
                </li>
            <?php } else { ?>
                <li class="nav-item">
                    <a class="nav-link " id="gestisci_user.php" aria-current="page" href="gestisci_user.php">Gestisti user</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link " id="gestisci_laurea.php" aria-current="page" href="gestisci_laurea.php">Gestisci Laurea</a>
                </li>
            <?php
                    }
                } if(isset($prefix)) { 
            ?>
                <li class="nav-item">
                    <a class="nav-link " id="cambia_password.php" aria-current="page" href="cambia_password.php">Cambia password</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link logout" aria-current="page" href='../Database/destroy.php'>Logout</a>
                </li>
            <?php }else{ ?>
                <li class="nav-item">
                    <a class="nav-link " id="cambia_password.php" aria-current="page" href="../cambia_password.php">Cambia password</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link logout" aria-current="page" href='../../Database/destroy.php'>Logout</a>
                </li>
            <?php } ?>
        </ul>
    </nav>
</div>
<?php if(isset($prefix)) { ?>
    <img src="../img/LogoFooter_a9f0c3692bf29c71609e5f204522c5d4_0.png" width="175px" class="rounded float-end" style="margin-top:-70px;margin-right:15px">
<?php }else{ ?>
    <img src="../../img/LogoFooter_a9f0c3692bf29c71609e5f204522c5d4_0.png" width="175px" class="rounded float-end" style="margin-top:-70px;margin-right:15px">
<?php } ?>

<script>
    document.getElementById('<?php echo $file;?>').classList.add("active");
</script>
