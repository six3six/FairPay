#/bin/bash

# define path to custom docker environment
DOCKER_ENVVARS=/etc/apache2/docker_envvars

# write variables to DOCKER_ENVVARS
cat << EOF > "$DOCKER_ENVVARS"
export APACHE_RUN_USER=www-data
export APACHE_RUN_GROUP=www-data
export APACHE_LOG_DIR=/var/log/apache2
export APACHE_LOCK_DIR=/var/lock/apache2
export APACHE_PID_FILE=/var/run/apache2.pid
export APACHE_RUN_DIR=/var/run/apache2
export APACHE_DOCUMENT_ROOT=/fairpay/web
EOF

PARAMETERS_FILE=app/config/parameters.yml

cat << EOF > "$PARAMETERS_FILE"
parameters:
    database_driver:   pdo_mysql
    database_host:     $DB_HOST
    database_port:     ~
    database_name:     $DB_NAME
    database_user:     $DB_USER
    database_password: $DB_PASSWORD

    mailer_transport:  smtp
    mailer_host:       $MAILER_HOST
    mailer_user:       $MAILER_USER
    mailer_password:   $MAILER_PASSWORD
    mailer_secondary_transport: smtp
    mailer_secondary_host: 127.0.0.1
    mailer_secondary_user: ~
    mailer_secondary_password: ~
    mailer_aws1_host: 127.0.0.1
    mailer_aws1_user: null
    mailer_aws1_password: null
    mailer_aws2_host: 127.0.0.1
    mailer_aws2_user: null
    mailer_aws2_password: null
    mailer_aws3_host: 127.0.0.1
    mailer_aws3_user: null
    mailer_aws3_password: null

    locale:            fr
    secret:            $SECRET_KEY
    application_version: 1.0.0
    hostname: $HOSTNAME
    piwik_site_id: $PIWIK_ID
    piwik_url: $PIWIK_URL
    piwik_enabled: true
    use_proxy: false

    client_id     : $GOOGLE_CLIENT_ID
    client_secret : $GOOGLE_CLIENT_SECRET
    proxy: ""

    debug_toolbar:          true
    debug_redirects:        false
    use_assetic_controller: true

    imap_mailbox:   $IMAP_MAILBOX
    imap_username:  $IMAP_USERNAME
    imap_password:  $IMAP_PASSWORD
    imap_port:      ~

    router.request_context.host: ~
    router.request_context.scheme: ~
    router.request_context.base_url: ~
EOF

chown www-data:www-data /fairpay/app/logs/ -R
mkdir /fairpay/app/cache/
chown www-data:www-data /fairpay/app/cache/ -R

# source environment variables to get APACHE_PID_FILE
. "$DOCKER_ENVVARS"

# only delete pidfile if APACHE_PID_FILE is defined
if [ -n "$APACHE_PID_FILE" ]; then
   rm -f "$APACHE_PID_FILE"
fi

# line copied from /etc/init.d/apache2
ENV="env -i LANG=C PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin"

# use apache2ctl instead of /usr/sbin/apache2
$ENV APACHE_ENVVARS="$DOCKER_ENVVARS" apache2ctl -DFOREGROUND