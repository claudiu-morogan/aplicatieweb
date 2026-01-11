# 🐳 Docker Development Environment

Complete development setup with PHP, MySQL, and phpMyAdmin.

## 🚀 Quick Start

### From project root:

```bash
# Build and start all services
docker compose -f docker/docker-compose.yml up --build -d

# Check status
docker compose -f docker/docker-compose.yml ps

# View logs
docker compose -f docker/docker-compose.yml logs -f

# Stop all services
docker compose -f docker/docker-compose.yml down
```

### From docker folder:

```bash
cd docker
docker compose up --build -d
docker compose ps
docker compose logs -f
docker compose down
```

## 🌐 Access Services

- **Website**: http://localhost:8080
- **phpMyAdmin**: http://localhost:8081
- **MySQL**: localhost:3306

## 🔑 Default Credentials

### Admin Panel
- Username: `admin`
- Password: `admin123` ⚠️ **CHANGE THIS!**

### Database (phpMyAdmin)
- Server: `db`
- Username: `root`
- Password: `rootpassword`
- Database: `aplicatieweb`

### MySQL Direct Connection
- Host: `localhost` (from host machine) or `db` (from containers)
- Port: `3306`
- User: `root` or `aweb_user`
- Password: `rootpassword` or `aweb_pass`

## 📦 Services Included

### 1. **web** (PHP 8.2 + Apache)
- Container: `aweb_php`
- Port: `8080`
- Document root: `/var/www/html/public`
- Extensions: PDO, MySQLi, GD, ZIP, EXIF
- Auto-reload on code changes (volume mounted)

### 2. **db** (MySQL 8.0)
- Container: `aweb_mysql`
- Port: `3306`
- Auto-imports: `/database/schema.sql` on first run
- Persistent data: `mysql_data` volume

### 3. **phpmyadmin** (Latest)
- Container: `aweb_phpmyadmin`
- Port: `8081`
- Upload limit: 50MB
- Direct connection to MySQL

## 🔧 Common Commands

### View Logs

```bash
# All services
docker compose -f docker/docker-compose.yml logs -f

# Specific service
docker compose -f docker/docker-compose.yml logs -f web
docker compose -f docker/docker-compose.yml logs -f db
```

### Restart Services

```bash
# Restart all
docker compose -f docker/docker-compose.yml restart

# Restart specific service
docker compose -f docker/docker-compose.yml restart web
```

### Access Container Shell

```bash
# PHP container
docker exec -it aweb_php bash

# MySQL container
docker exec -it aweb_mysql bash
```

### Database Operations

```bash
# MySQL CLI
docker exec -it aweb_mysql mysql -uroot -prootpassword aplicatieweb

# Backup database
docker exec aweb_mysql mysqldump -uroot -prootpassword aplicatieweb > backup.sql

# Restore database
docker exec -i aweb_mysql mysql -uroot -prootpassword aplicatieweb < backup.sql
```

### Clean Up

```bash
# Stop and remove containers
docker compose -f docker/docker-compose.yml down

# Stop, remove containers and volumes (WARNING: deletes database!)
docker compose -f docker/docker-compose.yml down -v

# Remove everything including images
docker compose -f docker/docker-compose.yml down -v --rmi all
```

## 🐛 Troubleshooting

### Port Already in Use

```bash
# Check what's using port 8080
lsof -i :8080

# Change ports in docker-compose.yml:
# ports:
#   - "8081:80"  # Change 8080 to 8081
```

### Database Not Initializing

```bash
# Remove volume and recreate
docker compose -f docker/docker-compose.yml down -v
docker compose -f docker/docker-compose.yml up --build -d

# Check logs
docker compose -f docker/docker-compose.yml logs db
```

### Permission Issues

```bash
# Fix permissions on host
chmod -R 775 uploads/ public/media/ logs/ cache/ tmp/

# Fix inside container
docker exec -it aweb_php chown -R www-data:www-data /var/www/html
```

### Can't Connect to Database

1. Check if MySQL is healthy:
   ```bash
   docker compose -f docker/docker-compose.yml ps
   ```

2. Wait for health check to pass (may take 10-15 seconds on first start)

3. Check config.php uses `DB_HOST = 'db'` (not 'localhost')

## 📝 Notes

- **Auto-import**: Database schema automatically imports on first run
- **Volumes**: Code changes reflect immediately (no rebuild needed)
- **Persistence**: MySQL data persists in `mysql_data` volume
- **Networks**: All services on `aweb_network` bridge
- **Build Context**: Uses project root, respects `.dockerignore`

## 🔄 Development Workflow

1. Start containers: `docker compose -f docker/docker-compose.yml up -d`
2. Edit code in your IDE (changes auto-reload)
3. View in browser: `http://localhost:8080`
4. Check logs: `docker compose -f docker/docker-compose.yml logs -f web`
5. Access database: `http://localhost:8081` (phpMyAdmin)
6. Stop when done: `docker compose -f docker/docker-compose.yml down`

## 🚀 Ready to Deploy?

See `/DEPLOYMENT.md` for production deployment instructions.
