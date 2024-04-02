---
lang: fr
---
# SAE4A - Trello-Trollé

Alias le Trello codé à l'envers, alias le Trello trop laid codé par un troll !

## Site de Base

Le projet est un clone du site web [Trello](https://trello.com/) utilisé pour la gestion de projets qui permet de définir des **tableaux** contenant diverses **cartes** (tâches) organisées en différentes **colonnes**. Sur ce site, un utilisateur peut notamment :

* S'inscrire, se connecter.
* Gérer son compte (mise à jour, suppression...).
* Gérer des tableaux (CRUD).
* Gérer des colonnes dans des tableaux (CRUD).
* Gérer des cartes dans des colonnes (CRUD).
* Ajouter des membres sur ses tableaux.
* Affecter des membres du tableau à certaines cartes.
* Quitter les tableaux où il est simplement membre.

Au niveau des droits d'accès, on notera que :

* Le tableau peut être **partagé** en lecture seule (si la personne n'est pas membre du tableau) à n'importe qui par son URL. L'URL est déterminée par un système de **code secret** (système similaire aux vidéos non répertoriées de YouTube). Il ne doit pas être possible d'accéder à un tableau dont on ne connait pas l'URL, sauf à utiliser une méthode de force brute qui prendrait trop de temps à s'exécuter.  
* Les membres invités du tableau ont quasiment les mêmes droits que le propriétaire (créateur) mais ne peuvent pas supprimer le tableau ou inviter de nouveaux membres.

Le site propose aussi quelques autres fonctionnalités mineures.

Actuellement, l'application est **complètement fonctionnelle**, mais **uniquement codée en PHP** (pas de JavaScript). Donc, chaque action demande un chargement d'une nouvelle page.

Le SGBD utilisé est **PostgreSQL** (vous ne devez pas en changer).

## Projet

La SAÉ s'articule en deux parties : 
* analyse et audit de l'application existante
* puis amélioration et virtualisation de l'application.

### Analyse

Il semble que ce site web a été codé par un développeur amateur... Il y a donc un gros risque que le code produit (et ce qu'il y a autour) soit de mauvaise qualité, voir dangereux !

Avant de toucher au code à proprement parler, vous devrez faire une analyse profonde des défauts de l'application dans son état actuel et faire un **rapport commenté** sur les points suivants :

* La modélisation de la **base de données**, vous devrez notamment :

    * Commenter le choix de la **clé primaire** choisie par le développeur.
    * Expliquer les différentes **anomalies** qui peuvent survenir à cause de cette modélisation et leurs impacts sur l'application.
    * Lister (par déduction) les différentes **dépendances fonctionnelles**.
    * Déduire (en justifiant) la **forme normale** de l'unique relation de la base.    
    * Proposer une normalisation de la base donnée afin d'obtenir un schéma sain. Dans votre rapport, vous devrez notamment prouver que votre décomposition est **sans perte de données** et **sans perte de dépendances fonctionnelles**.

* La qualité du code et de l'**architecture** de l'application, le respect des différents principes de qualité logicielle (**DRY**, **SOLID**...)

* Les différentes **failles de sécurité** exploitables. Pour chaque faille, il faudra bien indiquer le bout de code non sécurisé, donner un exemple d'exploitation de cette faille, expliquer les dégats qu'elle pourrait causer, et enfin comment la régler.

* Les différents problèmes rencontrés lors de l'utilisation du site.

### Amélioration et virtualisation

Après votre analyse, il vous est demandé d'**améliorer cette application** de différentes manières :

* Supprimer toutes les **failles de sécurité**.

* **Normaliser** la base de données à partir de la décomposition proposée lors de la phase d'analyse.

* Améliorer la **qualité du code** et l'**architecture** de l'application. Il faudra appliquer les principes étudiés en cours de **complément web** : architecture en couches, couche **service**, **conteneur** de services.

* Utiliser un meilleur système de **routing** (comme dans le cours de complément web)

* Utiliser **twig** pour les vues.

* Ajouter des **tests unitaires** grâce à l'outil **PHPUnit** vu en cours. Attention à ce que vos tests ne dépendent pas de l'état (en production) de votre application ! C'est pour cela qu'il est important de posséder une bonne architecture. L'objectif est d'obtenir une couverture de test de **100%** pour la partie **métier** (notamment, les **services**). 

* Ajouter du **dynamisme** sur le site grâce à l'utilisation de **JavaScript** :

    * Site avec le minimum de rechargement de page grâce à **AJAX** (il faudra donc transformer une partie de l'application en **API**). Notamment, tout ce qui est relatif à la gestion d'un **tableau** (gestion des colonnes, cartes, membres) doit pouvoir se passer sur la même page, sans rechargement.
    * Ajouter un système de **Drag'n'drop** de cartes entre des colonnes d'un tableau.
    * Utilisation de la programmation **réactive** (TD7 de JavaScript) :
        * Pour **synchroniser** certains éléments du tableau, par exemple le nombre de cartes associées à chaque utilisateur par catégorie de colonne, la liste des utilisateurs associés à une carte, les membres du tableau, etc.
        * Toujours au niveau du tableau, pour proposer des **formulaires** de modification qui changent la page en direct. Par exemple, édition d'une carte, de sa colonne, etc.
        * Pour vérifier en direct lors du remplissage du formulaire de création que le login et l'email n'existe pas déjà.
    * C'est une liste non exhaustive, donc, toute fonctionnalité existante qui peut être dynamisée grâce à JavaScript (et éventuellement grâce à de la programmation réactive) est une amélioration bienvenue !

* **Une fois toutes les améliorations faites**, il est possible d'ajouter de nouvelles fonctionnalités que vous jugerez utile.

* De manière mineure (une fois que tout est fini) vous pouvez éventuellement améliorer le **style** du site, mais ce n'est pas du tout la priorité ! Dans ce cas, il est autorisé (voire conseillé) d'utiliser un framework CSS : **Bootstrap** (plus vraiment conseillé de nos jours), **Tailwind CSS**, **bulma**, etc.

Enfin, il faudra proposer une **virtualisation** multi-conteneur de votre projet avec un fichier `docker-compose.yml` permettant de déployer :

* Un conteneur pour la base de données **PostgreSQL** de l'application.
* Un conteneur qui fait tourner l'application sur un **serveur web** (qui communiquera donc avec le premier conteneur).
L'objectif est que votre application puisse être déployée sans problèmes en quelques commandes simples grâce à **Docker**.

## Ressources

Dans ce dépôt, vous trouverez le fichier `sae4a.sql` qui vous permettra de créer la structure de votre base de données.

Vous devrez utiliser le SGBD **PostgresSQL**. Les informations de connexion à votre base PostgreSQL de l'IUT sont disponibles sur [cette page](https://iutdepinfo.iutmontp.univ-montp2.fr/intranet/bases-de-donnees/).

## Liens avec les différentes ressources

Cette **SAÉ** mobilise des compétences développées lors de différentes ressources du semestre 4 :

* R4.A.10 Complément web
* R4.01 Architecture logicielle (**JavaScript**)
* R4.02 Qualité de développement
* R4.03 Qualité et au-delà du relationnel
* R4.A.08 Virtualisation

## Groupes et suivi

Les groupes sont composés de **5 personnes** (exceptionnellement 4).

Contrairement à la SAÉ du semestre 3, il n'y a pas vraiment de client. Néanmoins, vous pouvez quand même vous organiser (en interne) en sprints et appliquer les méthodes de gestion de projet.

Liste des enseignants référents pour chaque groupe :

* Groupe Q1 : [Malo Gasquet](mailto:malo.gasquet@umontpellier.fr)
* Groupe Q2 : [Romain Lebreton](mailto:romain.lebreton@umontpellier.fr)
* Groupe Q5 : [Cyrille Nadal](mailto:cyrille.nadal@umontpellier.fr)

Dès que les groupes sont formés vous devez :

* Créer un **dépôt gitlab privé** pour votre équipe en effectuant un **fork** de ce dépôt. À terme, le fichier `docker-compose.yml` devra aussi être placé dans ce dépôt.
* Inviter votre enseignant référent dans de dépôt.
* Envoyer un mail à votre enseignant référent pour lui donner la composition de l'équipe ainsi que le lien du dépôt.

## Rendus et soutenance

Il y aura **trois rendus** à faire pour cette SAÉ :

* Rendu du **rapport d'analyse** (format PDF) de l'application sur ce [dépôt Moodle](https://moodle.umontpellier.fr/mod/assign/view.php?id=798160) le **19 mars (23h59 max)**.

* Rendu du **rapport de projet final** (format PDF) le **2 avril (23h59 max)** sur ce [dépôt Moodle](https://moodle.umontpellier.fr/mod/assign/view.php?id=798161). Vous travaillez sur ce rapport en cours de communication.

* Rendu du **projet amélioré** le **2 avril (23h59 max)** sur ce [dépôt Moodle](https://moodle.umontpellier.fr/mod/assign/view.php?id=798162). Votre rendu sera sous la forme d'une **archive zip** contenant :

    * Les sources du projet (qui devra aussi inclure le fichier `docker-compose.yml`).
    * Un script SQL permettant de créer votre base de données.
    * Un fichier `README.md` contenant :
        * L'adresse du site (hébergé dur **webinfo**)
        * La liste des fonctionnalités améliorées.
        * La répartition du travail dans l'équipe.
    
Les **soutenances** de projet auront lieu le **4 avril** à Sète et le **5 avril** à Montpellier

Bon projet !