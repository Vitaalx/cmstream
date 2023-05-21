# Cahier des charges du projet CM-STREAM 21/07/2023

Participants:
> William FLORENTIN - **Dev back (Responsable)**  
> Liam MACQUAIRE - **Dev back**  
> Matthieu AUBRY - **Dev front**  
> Mathieu CAMPANI - **Dev front**  

## 1 - Introduction

### 1.1 - Contexte et aperçu du projet


> Ce projet à pour objectif de créer un logiciel en ligne grâce auquel il est possible de créer, de gérer et de modifier facilement un site web de streaming, sans avoir besoin de connaissances techniques en langage informatique.  
> Ce type de logiciel est appelé un CMS **(Content Management System)**.  
> Ce CMS devra inclure un SGC **(Système de gestion de contenu)** ainsi qu'un constructeur de template facile et rapide à utiliser.  

### 1.2 - Descriptifs et répartition des rôles

- **Rôle N°1** :
    - Lorem ipsum dolor sit amet, con et temp 

- **Rôle N°2** :
    - Lorem ipsum dolor sit amet, con et temp 

- **Rôle N°3** :
    - Lorem ipsum dolor sit amet, con et

- **Rôle N°4** :
    - .........................


## 2 - Description du projet

### 2.1 - Fonctionnalités principales attendues

- [ ] Inscription et authentification des utilisateurs
- [ ] Recherche de contenus par titre, catégorie, genre, etc.
- [ ] Lecture en continu (streaming) des vidéos
- [ ] Recommandations personnalisées en fonction des préférences de l'utilisateur
- [ ] Création et gestion de listes de lecture ou de favoris
- [ ] Intégration de fonctionnalités sociales (commentaires, partages sure les réseaux, etc.)
- [ ] Profils d'utilisateurs avec historique de visionnage et gestion des paramètres de compte
- [ ] Système de notation et d'évaluation des contenus
- [ ] Gestion des droits d'accès aux contenus (contenus exclusifs pour certains abonnés, niveaux de restriction, etc.)
- [ ] Sections dédiées aux nouveautés, tendances, populaires, etc.
- [ ] Fonctionnalités de lecture automatique pour la continuité de visionnage
- [ ] Support client et assistance utilisateur (report de bug exemple film down)
- [ ] Personnalisation de l'interface utilisateur (thèmes, paramètres d'affichage, etc.)
- [ ] Section d'administration pour la gestion des contenus, des [VipersS, Click, Clm4 membres.url](..%2F..%2F..%2FAppData%2FLocal%2FTemp%2FVipersS%2C%20Click%2C%20Clm4%A0membres.url)utilisateurs et des statistiques.

- [ ] Gestion des abonnements et des paiements (à voir)
- [ ] Fonctionnalités de streaming en direct (live streaming) (à voir)
- [ ] Support multilingue et sous
- [ ] Titres pour différents langages (à voir)
- [ ] Newsletter custom personnalisées (nouveaux épisodes, recommandations, etc.) (à voir)

### 2.2 - Plateformes cibles

> Mobile 1284 x 2778 () 6.1"  
> PC 2560 x 1600 (16:10) 13.3"

### 2.3 - Exigences de performance

Nombre d'utilisateurs simultanés
> 10000000000000 à peu prêt

Débit de streaming
> Objectif : **720p / 30ips**

API compatible:
> Sibnet  
> DoodStream  
> UpStream  
> Twitch (à voir)  
> etc

## 3 - Exigences fonctionnelles

- Gestion des utilisateurs (inscription, authentification, profils, etc.)
- Gestion des contenus multimédias (ajout, édition, suppression, catégorisation, etc.)
- Fonctionnalités de recherche et de filtrage des contenus
- Gestion des droits d'accès aux contenus (public, privé, restreint, système de role.)
- Lecture en continu (streaming) des contenus
- Intégration de fonctionnalités sociales (commentaires, partage, recommandations, etc.)
- Statistiques et rapports sur l'utilisation du CMS

## 4 - Exigences techniques

Plateforme de développement:
1.    **PHP natif**
2.    **JS natif**

Compatible avec les naviguateur Chromium  
Infrastructure d'hébergement et de streaming (ajouter le shema réseaux)  
Sécurité des données et des utilisateurs  
Intégration avec des services tiers (paiements, analyse d'audience, etc.) (à voir)  

## 5 - Interface utilisateur et expérience utilisateur

- Maquettes ou wireframes de l'interface utilisateur (figma)
- Exigence visuel (à definir)

## 6 - Contraintes de développement

- Échéance du projet **21/07/2023 08h00**
- Méthodologie de développement (agile)
- Répartition des tâches et responsabilités:
> William FLORENTIN (dev back)
>> Tâches:
>>> Tâche n*1
> 
> Liam MACQUAIRE (dev back)
>> Tâches:
>>> Tâche n*1
> 
> Matthieu AUBRY (dev front)
>> Tâches:
>>> Tâche n*1 
> 
> Mathieu CAMPANI (dev front)
>> Tâches:
>>> Tâche n*1

## 7 - Exigences de documentation et de formation
- Documentation technique 
- Guides d'utilisation et de maintenance

## 8 - Critères d'évaluation et de sélection

Réaliser un CMS (wordpress like) from scratch en PHP
Ce CMS doit être dans l'environnement graphique de votre choix et pour ce qui concerne le front vous
êtes libres d'utiliser le framework de votre choix.
N'hésitez pas à utiliser un Faker : <https://fakerphp.github.io/>
Utilisation de Postgres et non MySQL.
Liste des fonctionnalités :

- Dashboard (accueil backoffice)
- Gestion des utilisateurs (CRUD) avec gestion des rôles et validation du compte par mail.
- Gestion des pages (CRUD) avec gestion du menu en front
- Gestion des commentaires avec modération ou signalement
- Sitemap.xml
- Optimisation du SEO (Title, meta description, ...)
- Gestion du template front (Police, couleurs, ...)
Librairies autorisées :
- jQuery
- WYSIWYG
- JavaScript Charts & Maps (amcharts, highcharts, ...)
- Datatables
- PHPMAILER (interdiction d'utiliser la fonction native mail de php) ou autre si autre système de
notification (Exemple SMS)
- OK pour l'utilisation de composer
- POUR TOUTES AUTRES LIBRAIRIES ME DEMANDER.
Attentes :
- Une solution en ligne (OBLIGATOIRE) <https://free-for.dev/>
- Github ou Gitlab <https://www.conventionalcommits.org/en/v1.0.0/>
- Commit signé non obligatoire
- Docker essentiellement pour l'env de dev
Techniques :
- Namespace
- Singleton
- MVC

## 10 -  Annexes
- ZIP contenant les design des vue figma du projet
- lien kanban