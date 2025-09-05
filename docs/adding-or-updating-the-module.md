# Adding or Updating a Module - INTERNAL USE ONLY

1. Upload the module to the `freescout/modules` folder
2. Run the following commands to update the module:

### Copy the module to the `data/Modules` folder

```bash
docker cp /root/freescout/modules/RedirectUnauthenticated freescout-app:/www/html/Modules/
docker exec freescout-app chown -R 80:82 /www/html/Modules/RedirectUnauthenticated
```

### Clear the cache

```bash
docker exec -u 80:82 freescout-app bash -c "cd /www/html && php artisan cache:clear && php artisan config:clear && php artisan route:clear"
```

### Restart the container

```bash
docker restart freescout-app
```

### Get the IP address of the container

We run this test to see if the IP has changed when restarting the container.

```bash
docker inspect -f '{{range.NetworkSettings.Networks}}{{.IPAddress}}{{end}}' freescout-app
```

TODO: find alternative solution that prevents the IP chagne.

### Edit the `nginx.conf` file to update the IP address of the container

If the IP address has changed, update it.

```bash
sudo nano /etc/nginx/sites-available/default
```

### Test the nginx configuration

```bash
sudo nginx -t
sudo service nginx reload
```

## When copying a specific file

`test-auth-check.php` used as an exmaple:

```bash
docker cp /root/freescout/modules/RedirectUnauthenticated/test-auth-check.php freescout-app:/www/html/Modules/RedirectUnauthenticated/Http/Middleware/RedirectToPortal.php
```
