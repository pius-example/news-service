#!groovy
// -*- coding: utf-8; mode: Groovy; -*-

@Library('ru.greensight@v1.0.6')_

import ru.greensight.HelmParams
import ru.greensight.Options

def options = new Options(script:this)
def helm = new HelmParams(script:this)

def configVarsList = [
    "K8S_NAMESPACE",         // неймспейс в который отгружать
    "HELM_RELEASE",          // название helm релиза
    "GIT_CREDENTIALS_ID",    // credentials id от гитлаба
    "VALUES_REPO",           // адрес репозитория values
    "VALUES_BRANCH",         // ветка в репозитории values
    "VALUES_PATH",           // путь до файла values в репозитории values
    "CHART_REPO",            // адрес репозитория чарта
    "CHART_BRANCH",          // ветка в репозитории чарта
    "DOCKER_IMAGE_ADDRESS",  // название образа с доменом (harbor.gs.ru/project/service)
    "DOCKER_IMAGE_NAME",     // название образа без домена (project/service)
    "HARBOR_ADDRESS",        // адрес реджистри с протоколом (https://harbor.gs.ru)
    "REGISTRY_CREDS",        // credentials id от реджистри
    "BASE_IMAGE",            // базовый образ для приложения (harbor.gs.ru/project/php:7.3)
    "BASE_CI_IMAGE",         // базовый образ для тестирования приложения
    "GITLAB_TOKEN_CREDS",    // credentials id с токеном гитлаба
    "HELM_IMAGE",            // образ helm
    "NEW_SOPS_IMAGE",        // образ sops
    "NEW_SOPS_URL",          // адрес sops keyservice
    "SOPS_KEY_CREDS",        // credentials c gpg ключом
    "K8S_CREDS",             // credentials id от kubeconfig
    "TESTING_DB_HOST",       // адрес СУБД для создания тестовых БД
    "POSTGRES_TEST_CREDS",   // credentials id от СУБД
    "PSQL_IMAGE",            // образ psql
    "AUTODEPLOY_BRANCHES",    // ветка для autodeploy
    "KAFKA_BOOTSTRAP_SERVER",
    "KAFKA_LOGIN",
    "KAFKA_PASSWORD",
    "KAFKA_TOOLS_IMAGE",
    "DTRACK_CREDS",
    "DTRACK_FOLDER"
]

properties([
    gitLabConnection('public-gitlab'),
    parameters([
       booleanParam(name: 'DEPLOY_K8S', defaultValue: false, description: 'Отгрузить в kubernetes'),
       booleanParam(name: 'PAUSE_BEFORE_DEPLOY', defaultValue: false, description: 'Ask user approvement before deploy'),
       booleanParam(name: 'RUN_PRE_INSTALL_HOOK', defaultValue: true, description: 'Execute migration before deploy'),
       string(name: 'VALUES_BRANCH', defaultValue: env.BRANCH_NAME, description: 'config-store branch'),
       string(name: 'RELEASE_NAME', defaultValue: env.BRANCH_NAME, description: 'name release branch')
   ]),
    buildDiscarder(logRotator (artifactDaysToKeepStr: '', artifactNumToKeepStr: '10', daysToKeepStr: '', numToKeepStr: '10')),
    disableConcurrentBuilds(),
])

def doDeploy = ''
def gitCommit = ''
def dockerTag = ''
def releaseName = ''
def valuesBranchRelease = ''

node('docker-agent'){
    lock(label: 'docker', quantity: 1) {
        stage('Checkout') {
            gitlabCommitStatus("checkout") {
                cleanWs()

                // Проверяем что параметры заполнены. Если нет, то позже заполним дефолтными значениями.
                // Нужно для ситуации, когда ветка собирается первый раз и параметры не заполнены.
                def paramsDefined = false
                if (options.get("VALUES_BRANCH") != null) {
                    paramsDefined = true
                }

                options.loadConfigFile("env-folder")
                options.loadConfigFile("env-service")

                if (!paramsDefined) {
                    options.vars["DEPLOY_K8S"] = false
                    options.vars["PAUSE_BEFORE_DEPLOY"] = false
                    options.vars["RUN_PRE_INSTALL_HOOK"] = false
                    options.vars["VALUES_BRANCH"] = env.BRANCH_NAME
                    options.vars["DELETE_AFTER"] = '336'
                }

                options.checkDefined(configVarsList)

                releaseName = "${options.get('HELM_RELEASE')}-${params.RELEASE_NAME}".replace("_", "-")

                def releaseExists = false
                docker.image(options.get("HELM_IMAGE")).inside('--entrypoint=""') {
                    withCredentials([file(credentialsId: options.get("K8S_CREDS"), variable: 'kubecfg')]) {
                        def status = sh(
                            script: "KUBECONFIG=${kubecfg} helm --namespace ${options.get('K8S_NAMESPACE')} status ${releaseName}",
                            returnStatus: true
                        )
                        releaseExists = status == 0

                        if (releaseExists){
                            valuesBranchRelease = sh(
                                script: "KUBECONFIG=${kubecfg} kubectl get configmap ${releaseName} --namespace ${options.get('K8S_NAMESPACE')} --output=json | jq -r '.metadata.labels.valuesBranchRelease' ",
                            returnStdout: true)?.trim()
                            if (valuesBranchRelease == "" || valuesBranchRelease == "null") {
                                options.vars["VALUES_BRANCH_DEPLOY"] = params.VALUES_BRANCH
                            } else {
                                if(params.DEPLOY_K8S) {
                                    options.vars["VALUES_BRANCH_DEPLOY"] = params.VALUES_BRANCH
                                }else{
                                    options.vars["VALUES_BRANCH_DEPLOY"] = valuesBranchRelease
                                }
                            }
                        } else {
                            options.vars["VALUES_BRANCH_DEPLOY"] = params.VALUES_BRANCH
                        }
                    }
                }
                doDeploy = params.DEPLOY_K8S || options.getAsList("AUTODEPLOY_BRANCHES").contains(BRANCH_NAME) || releaseExists

                echo "Debug: Название ветки конфига ${options.get('VALUES_BRANCH_DEPLOY')}"
                echo "Debug: Название релиза ${releaseName}"

                if (doDeploy) {
                    cloneToFolder('ms-helm-values', options.get("VALUES_REPO"), options.get("VALUES_BRANCH_DEPLOY"), options.get("GIT_CREDENTIALS_ID"))

                    helm.addFirstExistingOptional([
                        "ms-helm-values/${options.get("COMMON_VALUES_PATH")}/common-env.yaml",
                    ])
                    helm.addFirstExistingOptional([
                        "ms-helm-values/${options.get("COMMON_VALUES_PATH")}/common-env.sops.yaml",
                    ])

                    def branchFolder = "ms-helm-values/${options.get("VALUES_PATH")}/${env.BRANCH_NAME}/${options.get("HELM_RELEASE")}"
                    def masterFolder = "ms-helm-values/${options.get("VALUES_PATH")}/master/${options.get("HELM_RELEASE")}"
                    helm.addFirstExisting([
                        "${branchFolder}/${options.get("HELM_RELEASE")}.yaml",
                        "${masterFolder}/${options.get("HELM_RELEASE")}.yaml"
                    ])
                    helm.addFirstExistingOptional([
                        "${branchFolder}/${options.get("HELM_RELEASE")}.sops.yaml",
                        "${masterFolder}/${options.get("HELM_RELEASE")}.sops.yaml"
                    ])

                    cloneToFolder('ms-helm-chart', options.get("CHART_REPO"), options.get("CHART_BRANCH"), options.get("GIT_CREDENTIALS_ID"))
                }
                dir('src') {
                    checkout scm
                    gitCommit = sh(returnStdout: true, script: 'git log -1 --format=%h').trim();
                    dockerTag = "${env.BRANCH_NAME}-${gitCommit}"
                }
            }
        }

        stage('Security') {
            if (!options.get("PACKAGES_SKIP_CHECK")) {
                def dtrackCredentialsId = options.get("DTRACK_CREDS") ?: 'dt-token'
                dir('src') {
                    withCredentials([string(credentialsId: dtrackCredentialsId, variable: 'dtrack_api_key')]) {
                         docker.image(options.get("BASE_CI_IMAGE")).inside("--entrypoint=''") {
                             sh """
                                 composer global require --no-plugins --no-interaction cyclonedx/cyclonedx-php-composer
                                 composer global config --no-plugins --no-interaction allow-plugins.cyclonedx/cyclonedx-php-composer true
                                 composer CycloneDX:make-sbom composer.json > bom.xml
                             """
                            dependencyTrackPublisher(
                                artifact: 'bom.xml',
                                projectName: options.get("HELM_RELEASE"),
                                projectVersion: "master",
                                synchronous: true,
                                dependencyTrackApiKey: dtrack_api_key,
                                projectProperties: [parentId: options.get('DTRACK_FOLDER')],
                                failedTotalCritical: options.get("PACKAGES_FAIL_WHEN_CRITICAL") ?: 1,
                                failedTotalHigh: options.get("PACKAGES_FAIL_WHEN_HIGH") ?: 1,
                                failedTotalMedium: options.get("PACKAGES_FAIL_WHEN_MEDIUM") ?: 1,
                            )
                         }
                    }
                }
            }
        }

        stage('Test') {
            gitlabCommitStatus("test") {
                dir('src') {
                    if (!options.get("DISABLE_QA")) {
                        def dbPrefix = doDeploy ? "deploy" : "test";
                        def dbName = "ci_auto_${dbPrefix}_${options.get("HELM_RELEASE")}_${env.BRANCH_NAME}".replace("-", "_").toLowerCase()
                        def testStatus = 0

                        withCredentials([string(credentialsId: options.get("GITLAB_TOKEN_CREDS"), variable: 'gitlabToken')]) {
                            withCredentials([usernamePassword(credentialsId: options.get("POSTGRES_TEST_CREDS"), usernameVariable: 'username', passwordVariable: 'password')]) {
                                withPostgresDB(options.get("PSQL_IMAGE"), options.get("TESTING_DB_HOST"), username, password, dbName) {
                                    docker.image(options.get("BASE_CI_IMAGE")).inside("--entrypoint=''") {
                                        sh(script: """
                                            composer config gitlab-oauth.gitlab.com ${gitlabToken}

                                            composer install --no-ansi --no-interaction --no-suggest --ignore-platform-reqs
                                            composer dump -o

                                            npm ci
                                        """)

                                        testStatus = sh(script: """
                                            export DB_CONNECTION=pgsql
                                            export DB_HOST=${options.get("TESTING_DB_HOST")}
                                            export DB_PORT=5432
                                            export DB_DATABASE=${dbName}
                                            export DB_USERNAME=${username}
                                            export DB_PASSWORD=${password}

                                            run-parts --exit-on-error .git_hooks/ci/

                                            if [ \$(git status --porcelain | wc -l) -eq "0" ]; then
                                                echo "Git repo is clean."
                                            else
                                                echo "Git repo dirty. Quit."
                                                exit 1
                                            fi
                                        """, returnStatus: true)
                                    }
                                }
                            }
                        }
                        if (testStatus != 0) {
                            error("Test failed")
                        }
                    }
                }
            }
        }

        if (doDeploy) {
            gitlabCommitStatus("build") {
                stage('Build') {
                    dir('src') {
                        def imageExists = imageExistsInRegistry(
                            options.get("REGISTRY_CREDS"),
                            options.get("HARBOR_ADDRESS"),
                            options.get('DOCKER_IMAGE_NAME'),
                            dockerTag
                        )

                        if (!imageExists) {
                            withCredentials([string(credentialsId: options.get("GITLAB_TOKEN_CREDS"), variable: 'gitlabToken')]) {
                                docker.image(options.get("BASE_CI_IMAGE")).inside('--entrypoint=""') {
                                    sh(script: """
                                    composer install --no-ansi --no-interaction --no-suggest --no-dev --ignore-platform-reqs
                                    composer dump -o
                                """)
                                }
                            }
                            def fullImageNameWithTag = "${options.get('DOCKER_IMAGE_ADDRESS')}:${dockerTag}"
                            def image = docker.build(fullImageNameWithTag, "--build-arg BASE_IMAGE=${options.get("BASE_IMAGE")} .")

                            docker.withRegistry(options.get("HARBOR_ADDRESS"), options.get("REGISTRY_CREDS")) {
                                image.push(dockerTag)
                            }
                            sh """docker images |\
                              grep ${options.get('DOCKER_IMAGE_ADDRESS')} |\
                              grep ${env.BRANCH_NAME}- |\
                              grep -v ${gitCommit} |\
                              awk '{print \$1 ":" \$2 }' |\
                              xargs -r docker rmi"""

                        }
                    }
                }
            }

            stage('Check kafka') {
                gitlabCommitStatus("deploy") {
                    checkKafkaTopics("${options.get('DOCKER_IMAGE_ADDRESS')}:${dockerTag}", """
                        export KAFKA_BROKER_LIST=${options.get("KAFKA_BOOTSTRAP_SERVER")}
                        export KAFKA_SECURITY_PROTOCOL=SASL_PLAINTEXT
                        export KAFKA_SASL_MECHANISMS=PLAIN
                        export KAFKA_SASL_USERNAME=${options.get("KAFKA_LOGIN")}
                        export KAFKA_SASL_PASSWORD=${options.get("KAFKA_PASSWORD")}
                        export KAFKA_CONTOUR=${options.get("KAFKA_CONTOUR")}
                        """,
                    "missed-topics.txt")

                    tryCreateKafkaTopics(
                        options.get("KAFKA_TOOLS_IMAGE"),
                        options.get("KAFKA_BOOTSTRAP_SERVER"),
                        options.get("KAFKA_LOGIN"),
                        options.get("KAFKA_PASSWORD"),
                        "ms-helm-values/${options.get("COMMON_VALUES_PATH")}/kafka-topics.yaml",
                        "missed-topics.txt"
                    )
                }
            }

            stage('Deploy') {
                gitlabCommitStatus("deploy") {
                    def continueDeploy = false
                    if (params.PAUSE_BEFORE_DEPLOY) {
                        continueDeploy = input(
                            id: 'userInput',
                            message: 'Продолжить отгрузку?',
                            parameters: [
                                [$class: 'BooleanParameterDefinition', defaultValue: true, name: 'Deploy in k8s']
                            ]
                        )
                    } else {
                        continueDeploy = true
                    }

                    if (continueDeploy) {
                        def svcName = ""
                        def ingressHost = ""
                        def helmParamsStr = ""

                        helm
                            .setValue("app.image.repository", options.get('DOCKER_IMAGE_ADDRESS'))
                            .setValue("app.image.tag", dockerTag)
                            .setValue("web.service.name", releaseName)
                            .setValue("hook.enabled", params.RUN_PRE_INSTALL_HOOK)

                        withCredentials([file(credentialsId: options.get("SOPS_KEY_CREDS"), variable: 'gpgKeyPath')]) {
                            helmParamsStr = helm.buildParams(options.get('NEW_SOPS_IMAGE'), options.get('NEW_SOPS_URL'), gpgKeyPath)
                        }

                        docker.image(options.get("HELM_IMAGE")).inside('--entrypoint=""') {
                            withCredentials([file(credentialsId: options.get("K8S_CREDS"), variable: 'kubecfg')]) {
                                sh """KUBECONFIG=${kubecfg} \
                                      helm upgrade --install --timeout=30m \
                                      ${helmParamsStr} \
                                      --namespace ${options.get('K8S_NAMESPACE')} \
                                      ${releaseName} ms-helm-chart"""

                                if (!options.getAsList('NOT_AUTODELETE').contains(params.RELEASE_NAME)){
                                    sh(script:"""
                                        VERSIONRELEASE=`KUBECONFIG=${kubecfg} helm status ${releaseName} --namespace ${options.get('K8S_NAMESPACE')} --output json | jq -r '.version'`
                                        KUBECONFIG=${kubecfg} kubectl patch --namespace ${options.get('K8S_NAMESPACE')} --patch '{"metadata":{"labels":{"autodelete":"true"}}}' secret/sh.helm.release.v1.${releaseName}.v\$VERSIONRELEASE
                                    """)
                                }

                                if (params.RELEASE_NAME != "master"){
                                    sh(script:"""
                                        KUBECONFIG=${kubecfg} kubectl patch --namespace ${options.get('K8S_NAMESPACE')} --patch '{"metadata":{"labels":{"valuesBranchRelease": "${options.get('VALUES_BRANCH_DEPLOY')}"}}}' configmap/${releaseName}
                                    """)
                                }

                                svcName = sh(returnStdout: true, script: """helm template -s templates/web-svc.yaml \
                                    ${helmParamsStr} \
                                    ${releaseName} ms-helm-chart \
                                    |  awk '/^\\s+name:/ {print \$2}' | head -1
                                    """).trim()

                                ingressHost = sh(returnStdout: true, script: """helm template -s templates/web-ing.yaml \
                                    ${helmParamsStr} \
                                    ${releaseName} ms-helm-chart \
                                    | awk '/host:/ {print \$3}' | sed 's/"//g'
                                    """).trim()
                            }
                        }

                        currentBuild.displayName = "#${BUILD_NUMBER} - Release : $params.RELEASE_NAME | Values : ${options.get('VALUES_BRANCH_DEPLOY')}"

                        currentBuild.description = [
                            "Release Branch : $params.RELEASE_NAME",
                            "Values Branch : ${options.get('VALUES_BRANCH_DEPLOY')}",
                            "Docker image: ${options.get('DOCKER_IMAGE_ADDRESS')}:${dockerTag}",
                            "Internal host: http://${svcName}.${options.get('K8S_NAMESPACE')}.svc.cluster.local",
                            "Public host: https://${ingressHost}"
                        ].join("\n")

                    }
                }
            }
        }
    }
}
