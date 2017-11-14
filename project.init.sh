#!/bin/sh

# vendor目录
# 如: /www/vendor/uniondrug/framework/project.init.sh
vendorDir=$(pwd)

# project目录
# 如: /www
cd ../../../
applicationDir=$(pwd)

# framework目录
# 1. make directories
# 2. add '.gitkeep' file
frameworkFolders="app \
    app/Controllers \
    app/Models \
    app/Services\
    config \
    docs \
    log\
    public\
    tmp"
for name in ${frameworkFolders} ; do
    mkdir -p ${applicationDir}/${name}
    echo "${applicationDir}/${name}" > ${applicationDir}/${name}/.gitkeep
done

# make '.gitignore'
gitIgnoreFile="${applicationDir}/.gitignore"
if [ ! -e "${gitIgnoreFile}" ] ; then
    echo '# git ignore configurations' > ${gitIgnoreFile}
    echo ".git/" >> ${gitIgnoreFile}
    echo ".gitignore" >> ${gitIgnoreFile}
    echo ".gitkeep" >> ${gitIgnoreFile}
    echo "*.bak" >> ${gitIgnoreFile}
    echo "*.log" >> ${gitIgnoreFile}
    echo "vendor/" >> ${gitIgnoreFile}
    echo "composer.lock" >> ${gitIgnoreFile}
fi

# create `public/index.php`
publicFile="${applicationDir}/public/index.php"
if [ ! -e "${publicFile}" ] ; then
    echo '<?php' > ${publicFile}
    echo 'error_reporting(E_ALL);' >> ${publicFile}
    echo '$phalconVersion = phpversion("phalcon");' >> ${publicFile}
    echo 'if (!$phalconVersion || version_compare($phalconVersion, "3.2.0") < 0) {' >> ${publicFile}
    echo '    echo "Phalcon v3.2.0+ Must Be Installed";' >> ${publicFile}
    echo '    exit;' >> ${publicFile}
    echo '}' >> ${publicFile}
    echo 'if (!file_exists(__DIR__ . "/../vendor/autoload.php")){' >> ${publicFile}
    echo '    echo "Composer not installed.";' >> ${publicFile}
    echo '    exit;' >> ${publicFile}
    echo '}' >> ${publicFile}
    echo 'require_once __DIR__ . "/../vendor/autoload.php";' >> ${publicFile}
    echo '$container = new Pails\Container(dirname(__DIR__));' >> ${publicFile}
    echo '$container->run(Pails\Application::class);' >> ${publicFile}
fi

echo "----success----"
echo "project: '${applicationDir}'"
echo "vendor: '${vendorDir}'"
