services:
  - type: web
    name: saborxpress-backend
    env: php
    plan: free
    buildCommand: composer install --no-dev --optimize-autoloader
    startCommand: php artisan migrate --force && php artisan db:seed --class=SaborXpressProyectSeeder --force && apache2-foreground
    envVars:
      - key: APP_KEY
        generateValue: true
      - key: APP_ENV
        value: production
      - key: APP_DEBUG
        value: false
      - key: DB_CONNECTION
        value: pgsql
      - key: APP_URL
        fromContext: instanceUrl
