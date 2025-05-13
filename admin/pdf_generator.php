<?php
// Désactiver l'affichage des erreurs
error_reporting(0);
ini_set('display_errors', 0);

// Démarrer la mise en tampon de sortie
ob_start();

// Définir les en-têtes HTTP
header('Content-Type: application/pdf');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Inclure les fichiers nécessaires
require_once '../config.php';
require_once 'auth.php';
require_once '../vendor/autoload.php';

// Vérifier si TCPDF est disponible
if (!class_exists('TCPDF')) {
    die('Erreur : La bibliothèque TCPDF n\'est pas installée correctement.');
}

// Vérifier l'authentification
requireLogin();

// Nettoyer la sortie tampon
ob_clean();

// Créer un nouveau document PDF
class MYPDF extends TCPDF {
    private $searchTerm;
    
    public function __construct($searchTerm = '') {
        parent::__construct();
        $this->searchTerm = $searchTerm;
    }
    
    public function Header() {
        $this->SetFont('helvetica', 'B', 20);
        $title = 'Liste des Membres';
        if (!empty($this->searchTerm)) {
            $title .= ' - ' . $this->searchTerm;
        }
        $this->Cell(0, 15, $title, 0, false, 'C', 0, '', 0, false, 'M', 'M');
    }
    
    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

try {
    // Récupérer le terme de recherche
    $searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
    $ageFilter = isset($_GET['age_filter']) ? $_GET['age_filter'] : '';

    // Préparer la requête de base
    $baseQuery = "FROM membres";
    $whereClause = "";
    $params = [];

    // Modifier la clause WHERE pour inclure le filtre d'âge
    if (!empty($searchTerm) || !empty($ageFilter)) {
        $whereClause = "WHERE ";
        $conditions = [];
        
        if (!empty($searchTerm)) {
            $conditions[] = "(nom LIKE :search OR prenom LIKE :search OR ville LIKE :search)";
            $params[':search'] = "%$searchTerm%";
        }
        
        if (!empty($ageFilter)) {
            $conditions[] = "TIMESTAMPDIFF(YEAR, date_de_naissance, CURDATE()) " . 
                           ($ageFilter === 'majeur' ? ">= 18" : "< 18");
        }
        
        $whereClause .= implode(" AND ", $conditions);
    }

    // Requête pour récupérer les membres
    $query = "SELECT * " . $baseQuery . " " . $whereClause . " ORDER BY date_inscription DESC";
    $stmt = $pdo->prepare($query);

    // Bind les paramètres de recherche
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }

    $stmt->execute();
    $members = $stmt->fetchAll();

    // Créer une nouvelle instance de PDF
    $pdf = new MYPDF($searchTerm);

    // Définir les informations du document
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('FIER Admin');
    $pdf->SetTitle('Liste des Membres - ' . $searchTerm . ' - ' . ($ageFilter === 'majeur' ? 'Majeurs' : 'Mineurs'));

    // Définir les marges
    $pdf->SetMargins(15, 40, 15);
    $pdf->SetHeaderMargin(20);
    $pdf->SetFooterMargin(10);

    // Ajouter une page
    $pdf->AddPage();

    // Définir le style du tableau
    $pdf->SetFont('helvetica', '', 10);

    // En-têtes du tableau
    $header = array('ID', 'Nom', 'Prénom', 'Email', 'Téléphone', 'Fonction', 'Statut', 'Ville');
    $w = array(15, 35, 35, 45, 30, 35, 25, 30);

    // Couleurs, ligne et police
    $pdf->SetFillColor(52, 152, 219);
    $pdf->SetTextColor(255);
    $pdf->SetDrawColor(128, 128, 128);
    $pdf->SetLineWidth(0.3);
    $pdf->SetFont('', 'B');

    // En-têtes
    for($i = 0; $i < count($header); $i++) {
        $pdf->Cell($w[$i], 7, $header[$i], 1, 0, 'C', 1);
    }
    $pdf->Ln();

    // Couleurs et police pour le contenu
    $pdf->SetFillColor(224, 235, 255);
    $pdf->SetTextColor(0);
    $pdf->SetFont('');

    // Données
    $fill = 0;
    foreach($members as $member) {
        $pdf->Cell($w[0], 6, $member['id'], 'LR', 0, 'L', $fill);
        $pdf->Cell($w[1], 6, $member['nom'], 'LR', 0, 'L', $fill);
        $pdf->Cell($w[2], 6, $member['prenom'], 'LR', 0, 'L', $fill);
        $pdf->Cell($w[3], 6, $member['email'], 'LR', 0, 'L', $fill);
        $pdf->Cell($w[4], 6, $member['telephone'], 'LR', 0, 'L', $fill);
        $pdf->Cell($w[5], 6, $member['fonction_actuelle'] ?? 'Non spécifié', 'LR', 0, 'L', $fill);
        $pdf->Cell($w[6], 6, $member['statut'], 'LR', 0, 'L', $fill);
        $pdf->Cell($w[7], 6, $member['ville'], 'LR', 0, 'L', $fill);
        $pdf->Ln();
        $fill = !$fill;
    }

    // Ligne de fermeture
    $pdf->Cell(array_sum($w), 0, '', 'T');

    // Nettoyer la sortie tampon une dernière fois
    ob_clean();

    // Générer le PDF
    $pdf->Output('membres_' . $searchTerm . '_' . ($ageFilter === 'majeur' ? 'majeurs' : 'mineurs') . '.pdf', 'I');
} catch (Exception $e) {
    // En cas d'erreur, nettoyer la sortie et afficher un message d'erreur
    ob_clean();
    die('Une erreur est survenue lors de la génération du PDF : ' . $e->getMessage());
} 