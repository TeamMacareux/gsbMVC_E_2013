<?php
if(!isset($_REQUEST['action'])){
	$_REQUEST['action'] = 'demandeConnexion';
}
$action = $_REQUEST['action'];
switch($action){
	case 'demandeConnexion':{
		include("vues/v_connexion.php");
		break;
	}
	case 'valideConnexion':{
		$login = $_REQUEST['login'];
		$mdp = $_REQUEST['mdp'];
		$utilisateur = $pdo->getInfosUtilisateur($login,$mdp);
		if(!is_array( $utilisateur)){
			ajouterErreur("Login ou mot de passe incorrect");
			include("vues/v_erreurs.php");
			include("vues/v_connexion.php");
		}
		else if ($utilisateur['permis'] == 2) {
			$id = $utilisateur['id'];
			$nom =  $utilisateur['nom'];
			$prenom = $utilisateur['prenom'];
			connecter($id,$nom,$prenom);
			include("vues/v_sommaireVisiteur.php");
		}
		else{
			$id = $utilisateur['id'];
			$nom =  $utilisateur['nom'];
			$prenom = $utilisateur['prenom'];
			connecter($id,$nom,$prenom);
			include("vues/v_sommaireComptable.php");
		}
		break;
		break;
	}
	default :{
		include("vues/v_connexion.php");
		break;
	}
}
?>