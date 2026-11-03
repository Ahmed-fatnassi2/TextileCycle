pipeline {
    agent any

    options {
        timestamps()
        disableConcurrentBuilds()
        skipDefaultCheckout(true)
    }

    environment {
        DOCKER_IMAGE = 'ahmedfatnassi2/textilecycle'
        KUBERNETES_NAMESPACE = 'textilecycle'
        SONARQUBE_SERVER = 'SonarQube'
    }

    stages {
        stage('Checkout') {
            steps {
                checkout scm
                script {
                    env.IMAGE_TAG = "${env.BUILD_NUMBER}-${sh(
                        script: 'git rev-parse --short=12 HEAD',
                        returnStdout: true
                    ).trim()}"
                }
            }
        }

        stage('Install and build') {
            steps {
                sh '''
                    set -eu
                    php --version
                    composer --version
                    node --version
                    npm --version

                    cp .env.example .env
                    mkdir -p database reports
                    touch database/database.sqlite

                    composer install --no-interaction --prefer-dist
                    composer check-platform-reqs
                    php artisan key:generate --force
                    php artisan migrate --force

                    npm ci
                    npm run build
                '''
            }
        }

        stage('Tests') {
            steps {
                sh '''
                    set -eu
                    mkdir -p reports
                    php artisan test --log-junit=reports/junit.xml
                '''
            }
            post {
                always {
                    junit allowEmptyResults: true, testResults: 'reports/junit.xml'
                }
            }
        }

        stage('SonarQube analysis') {
            when {
                branch 'main'
            }
            steps {
                withSonarQubeEnv(env.SONARQUBE_SERVER) {
                    sh 'sonar-scanner'
                }
            }
        }

        stage('SonarQube quality gate') {
            when {
                branch 'main'
            }
            steps {
                timeout(time: 10, unit: 'MINUTES') {
                    waitForQualityGate abortPipeline: true
                }
            }
        }

        stage('Build container image') {
            steps {
                sh 'docker build --pull --tag "$DOCKER_IMAGE:$IMAGE_TAG" .'
            }
        }

        stage('Publish container image') {
            when {
                branch 'main'
            }
            steps {
                withCredentials([
                    usernamePassword(
                        credentialsId: 'dockerhub-credentials',
                        usernameVariable: 'DOCKERHUB_USERNAME',
                        passwordVariable: 'DOCKERHUB_TOKEN'
                    )
                ]) {
                    sh '''
                        set -eu
                        set +x
                        printf '%s' "$DOCKERHUB_TOKEN" | docker login \
                            --username "$DOCKERHUB_USERNAME" \
                            --password-stdin
                        trap 'docker logout >/dev/null 2>&1 || true' EXIT
                        docker tag "$DOCKER_IMAGE:$IMAGE_TAG" "$DOCKER_IMAGE:latest"
                        docker push "$DOCKER_IMAGE:$IMAGE_TAG"
                        docker push "$DOCKER_IMAGE:latest"
                    '''
                }
            }
        }

        stage('Deploy to Kubernetes') {
            when {
                branch 'main'
            }
            steps {
                withCredentials([
                    file(
                        credentialsId: 'textilecycle-kubeconfig',
                        variable: 'KUBECONFIG'
                    )
                ]) {
                    sh '''
                        set -eu
                        export KUBECONFIG

                        kubectl apply -f k8s/namespace.yaml
                        if grep -q '<APPLICATION_URL>' k8s/configmap.yaml; then
                            echo 'Set APP_URL in k8s/configmap.yaml before deploying.' >&2
                            exit 1
                        fi
                        kubectl apply -f k8s/configmap.yaml
                        kubectl apply -f k8s/service.yaml
                        kubectl get secret textilecycle-secret \
                            --namespace "$KUBERNETES_NAMESPACE"
                        for required_key in APP_KEY DB_USERNAME DB_PASSWORD MYSQL_ROOT_PASSWORD; do
                            if ! kubectl get secret textilecycle-secret \
                                --namespace "$KUBERNETES_NAMESPACE" \
                                -o "jsonpath={.data.$required_key}" | grep -q .; then
                                echo "Required Kubernetes Secret key is missing: $required_key" >&2
                                exit 1
                            fi
                        done

                        IMAGE_REFERENCE="$DOCKER_IMAGE:$IMAGE_TAG"
                        JOB_NAME="textilecycle-migrate-$BUILD_NUMBER"

                        cat <<EOF | kubectl apply -f -
                        apiVersion: batch/v1
                        kind: Job
                        metadata:
                          name: ${JOB_NAME}
                          namespace: ${KUBERNETES_NAMESPACE}
                        spec:
                          backoffLimit: 3
                          ttlSecondsAfterFinished: 86400
                          template:
                            spec:
                              restartPolicy: Never
                              containers:
                                - name: migrate
                                  image: ${IMAGE_REFERENCE}
                                  command: ["php", "artisan", "migrate", "--force"]
                                  envFrom:
                                    - configMapRef:
                                        name: textilecycle-config
                                    - secretRef:
                                        name: textilecycle-secret
                        EOF

                        if ! kubectl wait \
                            --namespace "$KUBERNETES_NAMESPACE" \
                            --for=condition=complete \
                            "job/$JOB_NAME" \
                            --timeout=10m; then
                            kubectl logs \
                                --namespace "$KUBERNETES_NAMESPACE" \
                                "job/$JOB_NAME"
                            exit 1
                        fi

                        sed "s#image: ahmedfatnassi2/textilecycle:latest#image: $IMAGE_REFERENCE#" \
                            k8s/deployment.yaml |
                            kubectl apply -f -

                        kubectl rollout status \
                            --namespace "$KUBERNETES_NAMESPACE" \
                            deployment/textilecycle \
                            --timeout=5m
                    '''
                }
            }
        }
    }

    post {
        always {
            sh 'docker image rm "$DOCKER_IMAGE:$IMAGE_TAG" >/dev/null 2>&1 || true'
            cleanWs()
        }
    }
}
