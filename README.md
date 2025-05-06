# PROUD - Site Web de l'ONG

Ce projet est un site web pour l'ONG PROUD, développé avec HTML, CSS, JavaScript et PHP. Le site est entièrement en français et comprend plusieurs pages présentant les activités et les programmes de l'organisation.

## Fonctionnalités

- Pages principales : Accueil, À Propos, Nos Actions, Nous Rejoindre
- Formulaire d'adhésion avec validation côté serveur
- Base de données MySQL pour stocker les informations des membres
- Design responsive et moderne
- Interface utilisateur intuitive en français

## Prérequis

- PHP 8.0 ou supérieur
- MySQL 5.7 ou supérieur
- Serveur web (Apache, Nginx, etc.)
- Composer (pour les dépendances PHP)

## Installation

1. Clonez le dépôt :
```bash
git clone [URL_DU_REPO]
cd proud-ong
```

2. Configurez la base de données :
- Créez une base de données MySQL nommée `ngo_db`
- Importez le fichier `database.sql` :
```bash
mysql -u root -p ngo_db < database.sql
```

3. Configurez les paramètres de connexion :
- Modifiez le fichier `config.php` avec vos informations de connexion à la base de données :
```php
$host = "localhost";
$username = "votre_utilisateur";
$password = "votre_mot_de_passe";
$database = "ngo_db";
```

4. Démarrez le serveur de développement PHP :
```bash
php -S localhost:8000
```

5. Accédez au site via votre navigateur :
```
http://localhost:8000
```

## Structure du Projet

```
proud-ong/
├── index.html          # Page d'accueil
├── about.html          # Page À Propos
├── actions.html        # Page Nos Actions
├── join.html          # Page d'inscription
├── success.html       # Page de confirmation
├── styles.css         # Feuille de style principale
├── config.php         # Configuration de la base de données
├── process_membership.php  # Traitement du formulaire
├── database.sql       # Script de création de la base de données
└── README.md          # Documentation
```

## Fonctionnalités du Formulaire

- Validation des champs obligatoires
- Vérification du format de l'email
- Stockage des données dans la base de données
- Envoi d'email de confirmation
- Messages d'erreur en français

## Contribution

Les contributions sont les bienvenues ! Pour contribuer :

1. Fork le projet
2. Créez une branche pour votre fonctionnalité (`git checkout -b feature/AmazingFeature`)
3. Committez vos changements (`git commit -m 'Ajout d'une fonctionnalité incroyable'`)
4. Push vers la branche (`git push origin feature/AmazingFeature`)
5. Ouvrez une Pull Request

## Licence

Ce projet est sous licence MIT. Voir le fichier `LICENSE` pour plus de détails.

## Contact

Pour toute question ou suggestion, contactez-nous à :
- Email : contact@proud.org
- Site web : www.proud.org 