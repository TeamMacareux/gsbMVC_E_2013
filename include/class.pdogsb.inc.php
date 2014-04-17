<?php
/** 
 * Classe d'accès aux données. 
 
 * Utilise les services de la classe PDO
 * pour l'application GSB
 * Les attributs sont tous statiques,
 * les 4 premiers pour la connexion
 * $monPdo de type PDO 
 * $monPdoGsb qui contiendra l'unique instance de la classe
 
 * @package default
 * @author Cheri Bibi
 * @version    1.0
 * @link       http://www.php.net/manual/fr/book.pdo.php
 */

class PdoGsb{   		
      	private static $serveur='mysql:host=localhost';
      	private static $bdd='dbname=gsb_frais';   		
      	private static $user='root' ;    		
      	private static $mdp='' ;	
		private static $monPdo;
		private static $monPdoGsb=null;
/**
 * Constructeur privé, crée l'instance de PDO qui sera sollicitée
 * pour toutes les méthodes de la classe
 */				
	private function __construct(){
    	PdoGsb::$monPdo = new PDO(PdoGsb::$serveur.';'.PdoGsb::$bdd, PdoGsb::$user, PdoGsb::$mdp); 
		PdoGsb::$monPdo->query("SET CHARACTER SET utf8");
	}
	public function _destruct(){
		PdoGsb::$monPdo = null;
	}
/**
 * Fonction statique qui crée l'unique instance de la classe
 
 * Appel : $instancePdoGsb = PdoGsb::getPdoGsb();
 
 * @return l'unique objet de la classe PdoGsb
 */
	public  static function getPdoGsb(){
		if(PdoGsb::$monPdoGsb==null){
			PdoGsb::$monPdoGsb= new PdoGsb();
		}
		return PdoGsb::$monPdoGsb;  
	}
/**
 * Retourne les informations d'un utilisateur
 
 * @param $login 
 * @param $mdp
 * @return l'id, le nom et le prénom sous la forme d'un tableau associatif 
*/
	public function getInfosUtilisateur($login, $mdp){
		$req = "SELECT utilisateur.id AS id, utilisateur.nom AS nom, utilisateur.prenom AS prenom, utilisateur.idPermission AS permis FROM utilisateur 
		WHERE utilisateur.login='$login' AND utilisateur.mdp='$mdp'";
		$rs = PdoGsb::$monPdo->query($req);
		$ligne = $rs->fetch();
		return $ligne;
	}

/**
 * Retourne sous forme d'un tableau associatif toutes les lignes de frais hors forfait
 * concernées par les deux arguments
 
 * La boucle foreach ne peut être utilisée ici car on procède
 * à une modification de la structure itérée - transformation du champ date-
 
 * @param $idUtilisateur 
 * @param $mois sous la forme aaaamm
 * @return tous les champs des lignes de frais hors forfait sous la forme d'un tableau associatif 
*/
	public function getLesFraisHorsForfait($idUtilisateur,$mois){
	    $req = "SELECT * FROM lignefraishorsforfait 
		WHERE lignefraishorsforfait.idutilisateur ='$idUtilisateur' 
		AND lignefraishorsforfait.mois = '$mois' ";	
		$res = PdoGsb::$monPdo->query($req);
		$lesLignes = $res->fetchAll();
		$nbLignes = count($lesLignes);
		for ($i=0; $i<$nbLignes; $i++){
			$date = $lesLignes[$i]['date'];
			$lesLignes[$i]['date'] =  dateAnglaisVersFrancais($date);
		}
		return $lesLignes; 
	}
/**
 * Retourne le nombre de justificatif d'un utilisateur pour un mois donné
 
 * @param $idUtilisateur 
 * @param $mois sous la forme aaaamm
 * @return le nombre entier de justificatifs 
*/
	public function getNbjustificatifs($idUtilisateur, $mois){
		$req = "SELECT fichefrais.nbjustificatifs AS nb FROM fichefrais WHERE fichefrais.idutilisateur ='$idUtilisateur' AND fichefrais.mois = '$mois'";
		$res = PdoGsb::$monPdo->query($req);
		$laLigne = $res->fetch();
		return $laLigne['nb'];
	}
/**
 * Retourne sous forme d'un tableau associatif toutes les lignes de frais au forfait
 * concernées par les deux arguments
 
 * @param $idUtilisateur 
 * @param $mois sous la forme aaaamm
 * @return l'id, le libelle et la quantité sous la forme d'un tableau associatif 
*/
	public function getLesFraisForfait($idUtilisateur, $mois){
		$req = "SELECT fraisforfait.id AS idfrais, fraisforfait.libelle AS libelle, 
		lignefraisforfait.quantite AS quantite FROM lignefraisforfait INNER JOIN fraisforfait 
			ON fraisforfait.id = lignefraisforfait.idfraisforfait
		WHERE lignefraisforfait.idutilisateur ='$idUtilisateur' AND lignefraisforfait.mois='$mois' 
		ORDER BY lignefraisforfait.idfraisforfait";	
		$res = PdoGsb::$monPdo->query($req);
		$lesLignes = $res->fetchAll();
		return $lesLignes; 
	}
/**
 * Retourne tous les id de la table FraisForfait
 
 * @return un tableau associatif 
*/
	public function getLesIdFrais(){
		$req = "SELECT fraisforfait.id AS idfrais FROM fraisforfait ORDER BY fraisforfait.id";
		$res = PdoGsb::$monPdo->query($req);
		$lesLignes = $res->fetchAll();
		return $lesLignes;
	}
/**
 * Met à jour la table ligneFraisForfait
 
 * Met à jour la table ligneFraisForfait pour un utilisateur et
 * un mois donné en enregistrant les nouveaux montants
 
 * @param $idUtilisateur
 * @param $mois sous la forme aaaamm
 * @param $lesFrais tableau associatif de clé idFrais et de valeur la quantité pour ce frais
*/
	public function majFraisForfait($idUtilisateur, $mois, $lesFrais){
		$lesCles = array_keys($lesFrais);
		foreach($lesCles as $unIdFrais) {
			$qte = $lesFrais[$unIdFrais];
			$req = "UPDATE lignefraisforfait SET lignefraisforfait.quantite = $qte
			WHERE lignefraisforfait.idutilisateur = '$idUtilisateur' AND lignefraisforfait.mois = '$mois'
			AND lignefraisforfait.idfraisforfait = '$unIdFrais'";
			PdoGsb::$monPdo->exec($req);
		}
		
	}
/**
 * met à jour le nombre de justificatifs de la table ficheFrais
 * pour le mois et le utilisateur concerné
 
 * @param $idUtilisateur 
 * @param $mois sous la forme aaaamm
*/
	public function majNbJustificatifs($idUtilisateur, $mois, $nbJustificatifs){
		$req = "UPDATE fichefrais SET nbjustificatifs = $nbJustificatifs 
		WHERE fichefrais.idutilisateur = '$idUtilisateur' AND fichefrais.mois = '$mois'";
		PdoGsb::$monPdo->exec($req);	
	}
/**
 * Teste si un utilisateur possède une fiche de frais pour le mois passé en argument
 
 * @param $idUtilisateur 
 * @param $mois sous la forme aaaamm
 * @return vrai ou faux 
*/	
	public function estPremierFraisMois($idUtilisateur,$mois)
	{
		$ok = false;
		$req = "SELECT count(*) AS nblignesfrais FROM fichefrais 
		WHERE fichefrais.mois = '$mois' AND fichefrais.idutilisateur = '$idUtilisateur'";
		$res = PdoGsb::$monPdo->query($req);
		$laLigne = $res->fetch();
		if($laLigne['nblignesfrais'] == 0){
			$ok = true;
		}
		return $ok;
	}
/**
 * Retourne le dernier mois en cours d'un utilisateur
 
 * @param $idUtilisateur 
 * @return le mois sous la forme aaaamm
*/	
	public function dernierMoisSaisi($idUtilisateur)
	{
		$req = "SELECT MAX(mois) AS dernierMois FROM fichefrais WHERE fichefrais.idutilisateur = '$idUtilisateur'";
		$res = PdoGsb::$monPdo->query($req);
		$laLigne = $res->fetch();
		$dernierMois = $laLigne['dernierMois'];
		return $dernierMois;
	}
	
/**
 * Crée une nouvelle fiche de frais et les lignes de frais au forfait pour un utilisateur et un mois donnés
 
 * récupère le dernier mois en cours de traitement, met à 'CL' son champs idEtat, crée une nouvelle fiche de frais
 * avec un idEtat à 'CR' et crée les lignes de frais forfait de quantités nulles 
 * @param $idUtilisateur 
 * @param $mois sous la forme aaaamm
*/
	public function creeNouvellesLignesFrais($idUtilisateur,$mois)
	{
		$dernierMois = $this->dernierMoisSaisi($idUtilisateur);
		$laDerniereFiche = $this->getLesInfosFicheFrais($idUtilisateur,$dernierMois);
		if($laDerniereFiche['idEtat']=='CR'){
				$this->majEtatFicheFrais($idUtilisateur, $dernierMois,'CL');
				
		}
		$req = "insert into fichefrais(idutilisateur,mois,nbJustificatifs,montantValide,dateModif,idEtat) 
		values('$idUtilisateur','$mois',0,0,now(),'CR')";
		PdoGsb::$monPdo->exec($req);
		$lesIdFrais = $this->getLesIdFrais();
		foreach($lesIdFrais as $uneLigneIdFrais){
			$unIdFrais = $uneLigneIdFrais['idfrais'];
			$req = "insert into lignefraisforfait(idutilisateur,mois,idFraisForfait,quantite) 
			values('$idUtilisateur','$mois','$unIdFrais',0)";
			PdoGsb::$monPdo->exec($req);
		 }
	}
/**
 * Crée un nouveau frais hors forfait pour un utilisateur un mois donné
 * à partir des informations fournies en paramètre
 
 * @param $idUtilisateur 
 * @param $mois sous la forme aaaamm
 * @param $libelle : le libelle du frais
 * @param $date : la date du frais au format français jj//mm/aaaa
 * @param $montant : le montant
*/
	public function creeNouveauFraisHorsForfait($idUtilisateur,$mois,$libelle,$date,$montant)
	{
		$dateFr = dateFrancaisVersAnglais($date);
		$req = "INSERT INTO lignefraishorsforfait 
		VALUES('','$idUtilisateur','$mois','$libelle','$dateFr','$montant')";
		PdoGsb::$monPdo->exec($req);
	}
/**
 * Supprime le frais hors forfait dont l'id est passé en argument
 
 * @param $idFrais 
*/
	public function supprimerFraisHorsForfait($idFrais)
	{
		$req = "DELETE FROM lignefraishorsforfait WHERE lignefraishorsforfait.id =$idFrais ";
		PdoGsb::$monPdo->exec($req);
	}
/**
 * Retourne les mois pour lesquel un utilisateur a une fiche de frais
 
 * @param $idUtilisateur 
 * @return un tableau associatif de clé un mois -aaaamm- et de valeurs l'année et le mois correspondant 
*/
	public function getLesMoisDisponibles($idUtilisateur)
	{
		$req = "select fichefrais.mois as mois from  fichefrais 
		where fichefrais.idutilisateur ='$idUtilisateur' 
		order by fichefrais.mois desc ";
		$res = PdoGsb::$monPdo->query($req);
		$lesMois =array();
		
		while ($laLigne = $res->fetch())
		{
			$mois = $laLigne['mois'];
			$numAnnee =substr( $mois,0,4);
			$numMois =substr( $mois,4,2);
			$lesMois["$mois"]=array(
				"mois"=>"$mois",
				"numAnnee"  => "$numAnnee",
				"numMois"  => "$numMois"
				);
		}
		return $lesMois;
	}
/**
 * Retourne les informations d'une fiche de frais d'un utilisateur pour un mois donné
 
 * @param $idUtilisateur 
 * @param $mois sous la forme aaaamm
 * @return un tableau avec des champs de jointure entre une fiche de frais et la ligne d'état 
*/	
	public function getLesInfosFicheFrais($idUtilisateur,$mois)
	{
		$req = "SELECT ficheFrais.idEtat AS idEtat, ficheFrais.dateModif AS dateModif, ficheFrais.nbJustificatifs AS nbJustificatifs, 
			ficheFrais.montantValide AS montantValide, etat.libelle AS libEtat FROM  fichefrais INNER JOIN Etat ON ficheFrais.idEtat = Etat.id 
			WHERE fichefrais.idutilisateur ='$idUtilisateur' AND fichefrais.mois = '$mois'";
		$res = PdoGsb::$monPdo->query($req);
		$laLigne = $res->fetch();
		return $laLigne;
	}
/**
 * Modifie l'état et la date de modification d'une fiche de frais
 
 * Modifie le champ idEtat et met la date de modif à aujourd'hui
 * @param $idUtilisateur 
 * @param $mois sous la forme aaaamm
 */
 
	public function majEtatFicheFrais($idUtilisateur,$mois,$etat)
	{
		$req = "UPDATE ficheFrais SET idEtat = '$etat', dateModif = now() WHERE fichefrais.idutilisateur = '$idUtilisateur'
				AND fichefrais.mois = '$mois'";
		PdoGsb::$monPdo->exec($req);
	}
}
?>