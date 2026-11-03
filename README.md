# TexTileCycle

Application Laravel de collecte et de réemploi textile.

## Démarrage

1. Installer PHP 8.2+, Composer, Node.js et npm.
2. Exécuter `composer run setup` pour installer les dépendances, créer `.env`, générer la clé, appliquer les migrations et compiler les assets.
3. Exécuter `php artisan serve` puis ouvrir `http://127.0.0.1:8000`.

Pour tester le back office après `php artisan db:seed --class=TunisiaDemoSeeder`, utiliser `admin@textilecycle.test` avec le mot de passe `DemoTunisie2026!`. Trois comptes association de démonstration sont également créés; le même mot de passe fonctionne pour eux. Ces identifiants sont réservés au développement local et doivent être remplacés/supprimés avant tout déploiement.

Pour un compte administrateur personnalisé, renseigner `ADMIN_EMAIL` et `ADMIN_PASSWORD` dans le `.env` local, puis exécuter `php artisan db:seed`. Ces valeurs restent locales et ne doivent pas être commitées.

## Module 4 · Associations et dons

Un membre connecté peut soumettre une demande d’association à `/association/demande`. Elle reste « En attente » jusqu’à son approbation par un administrateur dans `/admin/associations`. Après approbation, le membre peut gérer ses demandes de dons dans `/association/dons`; le lien vers l’association est déterminé côté serveur.

Les routes du back office nécessitent un compte dont le rôle est `admin`. Les migrations ajoutent ce rôle et les tables `associations` et `donations`.

## Gestion des utilisateurs

- **Authentification : Laravel Fortify** (`config/fortify.php`, `app/Providers/FortifyServiceProvider.php`). Pages Blade du site : `/connexion`, `/inscription`, `/mot-de-passe-oublie`. 5 tentatives de connexion par minute.
- **Mot de passe oublié** : lien envoyé par e-mail, jeton à usage unique qui expire après 60 minutes (`config/auth.php`).
- **Vérification d'e-mail** : `User implements MustVerifyEmail`. Les espaces citoyen, atelier et admin exigent le middleware `verified`.
- **Rôles et permissions : Spatie Permission**. Rôles `admin`, `workshop_owner`, `user`. Les routes sont protégées par permission (`permission:deposits.manage`…), et la matrice se règle dans Admin → Rôles et permissions (`app/Support/Permissions.php`).
- **Double authentification (TOTP)** : depuis « Mon profil » (après confirmation du mot de passe). QR code, clé manuelle, 8 codes de récupération.
- **Profil** : nom, e-mail, mot de passe, photo de profil, suppression du compte (le dernier administrateur ne peut pas supprimer le sien).
- E-mails envoyés via la configuration `MAIL_*` du `.env`. En local, `MAIL_MAILER=log` les écrit dans `storage/logs/laravel.log`.

## About Laravel

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## Fonctionnalités IA du module Réparation

Le module peut proposer un assistant de réparation et des brouillons de message. Configurez `OPENAI_API_KEY` et `OPENAI_MODEL` dans le `.env` local pour activer les réponses IA ; ne placez jamais la clé dans `.env.example` ni dans Git. `AI_REPAIR_ENABLED=false` désactive les appels ; les messages d’atelier utilisent alors leurs modèles automatiques. Les quotas par défaut sont de 20 questions, 30 messages, 10 conseils et 10 contrôles qualité par jour et par compte ou adresse IP, avec un plafond partagé de 300 appels quotidiens. Les réponses sont mises en cache pour limiter les appels. Les tableaux de bord administrateur et atelier affichent les appels API réels et les jetons utilisés ; aucun prompt ni contenu client n’est conservé. `AI_REPAIR_UPCYCLE_COST_THRESHOLD` règle le seuil d’affichage des alternatives (80 TND) et `REPAIR_REPLACEMENT_SHARE` règle la part indicative de l’impact évité (0,5). Exécutez `php artisan migrate` après mise à jour.

Le tableau de bord atelier présente aussi des indicateurs de délai, de durée moyenne et d’avis qualité automatique sur 30 jours. Une fois la réparation terminée et son impact calculé, le citoyen peut consulter et imprimer un certificat récapitulatif ; les chiffres environnementaux restent des estimations indicatives.

### Comptes de démonstration

Après `php artisan migrate --seed`, les comptes suivants utilisent tous le mot de passe `DemoTunisie2026!` :

| Espace | Adresse e-mail |
| --- | --- |
| Administration | `admin@textilecycle.test` |
| Citoyen principal | `citoyen@textilecycle.test` |
| Deuxième citoyenne (vérifier la confidentialité) | `citoyenne2@textilecycle.test` |
| Propriétaire d’atelier | `atelier@textilecycle.test` |
| Partenaire associatif | `partenaire.tunis@textilecycle.test` |

Le partenaire associatif utilise le rôle citoyen et un profil association, conformément aux rôles actuellement disponibles dans l’application.

Le seeder ajoute l’atelier **Atelier Démo AI Tunis**, une demande en attente, un devis élevé à 125 TND pour tester les alternatives, des demandes actives avec des échéances différentes, un dossier prêt au retrait avec impact et avis qualité enregistrés, ainsi que trois réparations clôturées pour alimenter l’estimation historique des tarifs. Pour montrer l’analyse visuelle en direct, créez une demande avec une photo Avant, démarrez-la depuis l’espace atelier, puis ajoutez une photo Après avant l’analyse.

### Démonstration du risque et des alternatives

- Connectez-vous avec `atelier@textilecycle.test`, ouvrez `/atelier` et regardez la carte **Réparations à surveiller**. Le dossier **Manteau avec une couture ouverte** est en risque élevé car sa date estimée est dépassée.
- Ouvrez ce dossier et modifiez **Ajuster la date estimée pour recalculer le risque**. Enregistrez une date dans le futur : le badge passe de risque élevé à risque modéré, car la charge ouverte de l’atelier reste prise en compte. Le même badge et sa raison apparaissent dans `/atelier/reparations`.
- Déconnectez-vous, puis connectez-vous avec `citoyen@textilecycle.test` et ouvrez `/repairs`. Le panneau **Devis élevé** en haut de page mène à la veste avec un devis de **125 TND** (seuil par défaut : 80 TND).
- Ouvrez la demande pour voir les trois options Réparer, Upcycler et Donner. **Voir les alternatives avec l’assistant** demande une formulation IA. Si l’API n’a pas de crédit, le mode automatique garde les options accessibles et l’interface l’indique.

Ces identifiants sont réservés au développement et à la démonstration : ne les utilisez pas en production.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
