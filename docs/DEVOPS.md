# CI/CD Jenkins et déploiement

`Jenkinsfile` définit le pipeline de validation et de livraison de TextileCycle.
Les branches et pull requests exécutent les tests et construisent l'image.
L'analyse SonarQube et la Quality Gate s'exécutent sur `main`; c'est compatible
avec l'édition gratuite de SonarQube. Seule `main` publie sur Docker Hub et lance
le déploiement Kubernetes. La production n'est donc pas modifiée par un push
sur une branche de travail.

## Prérequis Jenkins

Installer les plugins Jenkins Pipeline, Git, GitHub, GitHub Branch Source,
SonarQube Scanner, Credentials Binding, JUnit et Workspace Cleanup. L'agent d'exécution
doit fournir PHP 8.2+ avec les extensions exigées par `composer.json`, Composer,
Node.js/npm, Docker avec accès à son daemon, `sonar-scanner`, `kubectl` et
`cleanWs`. `composer check-platform-reqs` vérifie les extensions avant les tests.

Configurer l'installation SonarQube Jenkins sous le nom exact `SonarQube`, et
installer SonarScanner dans le `PATH` de l'agent. Le Quality Gate attend le
webhook SonarQube suivant, configuré dans Administration → Configuration →
Webhooks :

```text
https://<JENKINS_PUBLIC_URL>/sonarqube-webhook/
```

Le projet SonarQube doit exister avec la clé `textilecycle`. Configurer un
Multibranch Pipeline pointant vers le dépôt GitHub pour découvrir les branches
et les pull requests. Pour analyser chaque branche et pull
request, activer explicitement les fonctions correspondantes dans une édition
SonarQube compatible et adapter les conditions des stages SonarQube.

## Credentials Jenkins

Ajouter ces credentials dans Jenkins, sans les committer :

| ID | Type | Contenu |
| --- | --- | --- |
| `dockerhub-credentials` | Username with password | Utilisateur Docker Hub et jeton d'accès Docker Hub |
| `textilecycle-kubeconfig` | Secret file | Kubeconfig dédié, limité au namespace `textilecycle` |

Limiter les permissions de déploiement à l'environnement et aux branches de
confiance. Ne pas exposer les credentials de production aux builds de branches
ou pull requests non fiables. Les stages de publication et de déploiement sont
réservés à `main`.
Exécuter les builds de branches non fiables sur des agents isolés et éphémères :
les étapes de construction Docker nécessitent l'accès au daemon et ne doivent
pas partager un agent privilégié avec des workloads non fiables.

## Configuration initiale Kubernetes

1. Vérifier le contexte et les permissions du cluster. Le compte de service
   Jenkins a uniquement besoin des droits nécessaires au namespace `textilecycle`
   sur les Deployments, Services, ConfigMaps et Jobs.
2. Créer le namespace et la ConfigMap :

   ```sh
   kubectl apply -f k8s/namespace.yaml
   kubectl apply -f k8s/configmap.yaml
   ```

3. Créer `textilecycle-secret` hors de Git. Le pipeline s'arrête explicitement
   si le Secret manque :

   ```sh
   kubectl create secret generic textilecycle-secret \
     --namespace textilecycle \
     --from-literal=APP_KEY='base64:<clé Laravel>' \
     --from-literal=DB_USERNAME='<utilisateur MySQL>' \
     --from-literal=DB_PASSWORD='<mot de passe MySQL>' \
     --from-literal=MYSQL_ROOT_PASSWORD='<mot de passe root MySQL>' \
     --from-literal=OPENAI_API_KEY='<clé OpenAI>'
   ```

   Générer une nouvelle clé de production avec `php artisan key:generate
   --show`. Ne jamais réutiliser la clé de développement ou enregistrer une
   vraie clé dans `k8s/secret.yaml.example`.
4. Remplacer la valeur illustrative `https://<APPLICATION_URL>` de `APP_URL`
   dans `k8s/configmap.yaml` par le domaine HTTPS de l'environnement avant son
   application.
5. Vérifier l'accès Docker Hub à `ahmedfatnassi2/textilecycle`, les ressources
   MySQL et la classe de stockage, puis configurer la découverte Prometheus.
   Si le dépôt Docker Hub est privé, configurer un `imagePullSecret` dans le
   namespace et le référencer dans le Deployment.
6. Déclencher le pipeline depuis `main`. Il publie une image taguée avec le
   numéro de build et le commit, applique les migrations avec cette image,
   déploie cette même image puis attend la réussite du rollout.

Les migrations s'exécutent avant le rollout applicatif. Sauvegarder la base
avant les releases qui comportent des migrations irréversibles, et conserver
une procédure de retour arrière vers une image compatible avec le schéma.

## Webhook GitHub

Déclarer un webhook sur le dépôt GitHub pointant vers
`https://<JENKINS_PUBLIC_URL>/github-webhook/`, avec les événements push et
pull request. Jenkins doit être accessible par HTTPS depuis GitHub. Ngrok peut
servir à une démonstration temporaire, mais son URL doit être mise à jour après
chaque changement d'adresse ; utiliser une URL stable pour une intégration
durable.

Le cache et les sessions utilisent la base partagée, ce qui évite de perdre
les quotas IA ou les sessions de connexion en alternant entre les répliques.
L'annotation Prometheus cible le port Service 80 (qui transfère vers le port
conteneur 8000).

La Quality Gate Jenkins exige un webhook SonarQube. L'analyse de branches et
de pull requests demande une édition SonarQube qui prend en charge les branches;
sinon, garder le job multibranch limité à l'analyse de `main`. Le webhook GitHub,
Jenkins, SonarQube, Docker Hub, le cluster Kubernetes et leurs secrets restent
à provisionner par l'équipe. Le dépôt ne contient pas de kubeconfig ni de
credentials réels.
