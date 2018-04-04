// Folder on server where the backup of the old site saved
def backupFolder = '~/deploy-backups'

// Name of the folder where the new application is being prepared.
// Must not exist in the application that is being deployed.
def deployFolder = "deployment"

// Name of the archive that contains the new application.
// Must not exist in the application that is being deployed.
def deployTar = 'deployment.tar.gz'

// Folder on server where the applications exist
def targetFolder = '~/webapps-data'

// A map of deployment environments.
// Each element is a map with the following keys:
//  - host: The ssh host url in format user@some.server.com
//  - folder: The folder where the app is located on server
//  - dbName: Name of the database to back up
//  - siteUrl: Url where the deployed site is accessible
def deployBranches = [
    develop: [
        host: 'synergic@lutra.visionapps.cz',
        folder: 'ditt_api_dev',
        dbName: 'ditt_api_dev',
        siteUrl: 'http://ditt-api.dev.visionapps.cz',
        credentials: 'deploy_lutra',
    ],
]



node {
     try {
        stage('Checkout code') {
            timeout(1) {
                checkout scm
            }
        }

        stage('Prepare docker containers') {
            timeout(20) {
                sh 'docker-compose up -d db'
                sleep 10
                sh 'docker run -d -it --link $(docker-compose ps -q db | head -1):db --volume ${PWD}:/www --name=web visionappscz/apache-php bash'
            }
        }

        stage('Build app and run tests') {
            timeout(20) {
                sh 'docker-compose run web bash -c "sh /root/init-container.sh /www && su www-data -c \'composer install && vendor/bin/robo install\'"'
            }
        }

        if (deployBranches.containsKey(env.BRANCH_NAME)) {
            stage('Deploy') {
                timeout(5) {
                    createDeployTar(deployTar)
                    deploy(deployBranches[env.BRANCH_NAME], targetFolder, deployTar, deployFolder, backupFolder)
                    checkStatus(deployBranches[env.BRANCH_NAME].siteUrl, 200)
                    notifyOfDeploy(deployBranches, env.BRANCH_NAME)
                }
            }
        }

    } catch (err) {
        currentBuild.result = 'FAILURE'
        echo "BUILD ERROR: ${err.toString()}"
        emailext (
            recipientProviders: [[$class: 'DevelopersRecipientProvider']],
            subject: "Build ${env.JOB_NAME} [${env.BUILD_NUMBER}] failed",
            body: err.toString(),
            attachLog: true,
        )
        if (deployBranches.containsKey(env.BRANCH_NAME)) {
            slackSend(color: 'danger', message: "DITT API: Build a nasazení větve `${env.BRANCH_NAME}` selhaly :thunder_cloud_and_rain:")
        }

    } finally {
        stage('Cleanup') {
            timeout(5) {
                sh 'docker stop web'
                sh 'docker rm --force web'
                sh 'docker-compose stop'
                sh 'docker-compose rm --all --force'
            }
        }
    }
}



def deploy(deployBranch, targetFolder, deployTar, deployFolder, backupFolder) {
    sshagent (credentials: [deployBranch.credentials]) {
        sh """ssh ${deployBranch.host} /bin/bash << EOF
            set -e

            cd ${targetFolder}/${deployBranch.folder}

            echo 'Creating deploy folder'
            rm -rf ${deployFolder}
            mkdir ${deployFolder}
            cp .env ${deployFolder}/.env
            mkdir --parents ${deployFolder}/var
            cp -r var/log ${deployFolder}/var/log
            cp php.ini ${deployFolder}/php.ini
            cp .htaccess ${deployFolder}/.htaccess
            cp .htpasswd ${deployFolder}/.htpasswd 2>/dev/null || :

            echo 'Moving deploy folder to targetFolder'
            rm -rf ${targetFolder}/${deployBranch.folder}.deploy
            mv ${targetFolder}/${deployBranch.folder}/${deployFolder} ${targetFolder}/${deployBranch.folder}.deploy
        """

        sh "scp ${deployTar} ${deployBranch.host}:${targetFolder}/${deployBranch.folder}.deploy"

        sh """ssh ${deployBranch.host} /bin/bash << EOF
            set -e
            cd ${targetFolder}/${deployBranch.folder}.deploy

            echo 'Extracting deployTar'
            tar -mxzf ${deployTar}
            chmod 744 bin/console

            echo 'Running post upload Robo tasks'
            vendor/bin/robo deploy:finalize /usr/local/bin/php70

            echo 'Backing up old deploy'
            cp public/static/503.php ${targetFolder}/${deployBranch.folder}/public/index.php
            pg_dump ${deployBranch.dbName} > ${targetFolder}/${deployBranch.folder}/db_dump.sql
            rm -rf ${backupFolder}/${deployBranch.folder}
            mv ${targetFolder}/${deployBranch.folder} ${backupFolder}/${deployBranch.folder}
            
            echo 'Switching to new deploy'
            mv ${targetFolder}/${deployBranch.folder}.deploy ${targetFolder}/${deployBranch.folder}
            rm -f ${targetFolder}/${deployBranch.folder}/${deployTar}
        """
    }
}

def createDeployTar(deployTar) {
    sh """tar \
        -zcf ${deployTar} \
        --add-file=bin \
        --add-file=config \
        --add-file=public \
        --add-file=src \
        --add-file=templates \
        --add-file=var \
        --exclude=var/log/* \
        --exclude=var/cache/* \
        --exclude=var/sessions/* \
        --add-file=vendor \
        --add-file=composer.json \
        --add=RoboFile.php"""
}

def notifyOfDeploy(deployBranches, currentBranch) {
    echo 'DEPLOY SUCCESSFUL'
    slackSend(
        color: 'good',
        message: "DITT API: Větev `${currentBranch}` byla nasazena na: ${deployBranches[currentBranch].siteUrl} :sunny:"
    )
}

def checkStatus(url, code) {
    echo 'Checking website status'
    sh """
        httpCode=\$(curl -sL --connect-timeout 50 -w "%{http_code}\\n" $url -o /dev/null)
        if [ \$httpCode -eq $code ]; then
            echo 'Website status check passed.'
            exit 0
        else
            echo 'Website status check failed.'
            exit 1
        fi
    """
}
