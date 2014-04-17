   
<!-- WORK IN PROGRESS !!! -->
 <!--Division pour le sommaire -->
    <div id="menuGauche">
     <div id="infosUtil">
    
        <h2>
    
        </h2>
    
      </div>  
        <ul id="menuList">
			<li >
				  Comptable :<br>
				<?php echo $_SESSION['prenom']."  ".$_SESSION['nom']  ?>
      </li>
           </li>
           <li class="smenu">
              <a href="index.php?uc=remboursementFrais&action=selectionnerMois" title="Consultation de mes fiches de frais">Suivi de remboursement</a>
           </li>
        <li class="smenu">
              <a href="index.php?uc=validationFrais&action=selectionnerMois" title="Consultation de mes fiches de frais">Validation des frais</a>
           </li>
     <li class="smenu">
              <a href="index.php?uc=connexion&action=deconnexion" title="Se déconnecter">Déconnexion</a>
           </li>
         </ul>
        
    </div>