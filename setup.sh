#!/usr/bin/env bash
# =============================================================================
# LeadBot Setup Script
# Run once: bash setup.sh
# =============================================================================

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
BACKEND_DIR="$SCRIPT_DIR/backend"
SCRAPER_DIR="$SCRIPT_DIR/scraper"

RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'; BLUE='\033[0;34m'; NC='\033[0m'

log()    { echo -e "${GREEN}[✓]${NC} $1"; }
warn()   { echo -e "${YELLOW}[!]${NC} $1"; }
error()  { echo -e "${RED}[✗]${NC} $1"; exit 1; }
header() { echo -e "\n${BLUE}══════════════════════════════════${NC}"; echo -e "${BLUE}  $1${NC}"; echo -e "${BLUE}══════════════════════════════════${NC}"; }

header "LeadBot Setup"

# ─── Check Prerequisites ─────────────────────────────────────────────────────

header "Checking Prerequisites"

command -v php      >/dev/null 2>&1 || error "PHP is not installed. Install PHP 8.1+"
command -v composer >/dev/null 2>&1 || error "Composer is not installed."
command -v mysql    >/dev/null 2>&1 || error "MySQL is not installed or not in PATH."
command -v node     >/dev/null 2>&1 || error "Node.js is not installed."
command -v npm      >/dev/null 2>&1 || error "npm is not installed."

PHP_VERSION=$(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;")
log "PHP $PHP_VERSION detected"
log "Node $(node --version) detected"

# ─── Laravel Backend ─────────────────────────────────────────────────────────

header "Setting Up Laravel Backend"

if [ ! -f "$BACKEND_DIR/artisan" ]; then
    warn "Laravel not found. Creating fresh Laravel 10 project..."
    cd "$SCRIPT_DIR"
    composer create-project laravel/laravel:^10.0 backend_tmp --prefer-dist --no-interaction
    # Copy our custom files over the fresh install
    cp -r "$SCRIPT_DIR/backend/app"        "$SCRIPT_DIR/backend_tmp/"
    cp -r "$SCRIPT_DIR/backend/database"   "$SCRIPT_DIR/backend_tmp/"
    cp -r "$SCRIPT_DIR/backend/routes"     "$SCRIPT_DIR/backend_tmp/"
    cp -r "$SCRIPT_DIR/backend/resources"  "$SCRIPT_DIR/backend_tmp/"
    cp -r "$SCRIPT_DIR/backend/config"     "$SCRIPT_DIR/backend_tmp/"
    cp    "$SCRIPT_DIR/backend/.env.example" "$SCRIPT_DIR/backend_tmp/.env.example"
    # Rename
    rm -rf "$BACKEND_DIR"
    mv "$SCRIPT_DIR/backend_tmp" "$BACKEND_DIR"
    log "Laravel project created and custom files applied."
else
    log "Laravel backend already exists. Skipping creation."
    cd "$BACKEND_DIR"
    composer install --no-interaction --prefer-dist
fi

cd "$BACKEND_DIR"

# ─── Environment Setup ───────────────────────────────────────────────────────

if [ ! -f "$BACKEND_DIR/.env" ]; then
    cp "$BACKEND_DIR/.env.example" "$BACKEND_DIR/.env"
    log "Created .env from .env.example"
    warn "Please edit backend/.env with your database credentials and Instagram credentials."
else
    log ".env already exists"
fi

# Generate app key if not set
grep -q "APP_KEY=$" "$BACKEND_DIR/.env" && php artisan key:generate --ansi && log "App key generated."

# ─── Database Setup ───────────────────────────────────────────────────────────

header "Database Setup"

# Prompt for DB credentials
echo ""
read -p "Enter MySQL username [root]: " DB_USER
DB_USER="${DB_USER:-root}"
read -sp "Enter MySQL password: " DB_PASS
echo ""
read -p "Enter database name [leadbot]: " DB_NAME
DB_NAME="${DB_NAME:-leadbot}"

# Update .env
sed -i "s/^DB_USERNAME=.*/DB_USERNAME=${DB_USER}/" "$BACKEND_DIR/.env"
sed -i "s/^DB_PASSWORD=.*/DB_PASSWORD=${DB_PASS}/" "$BACKEND_DIR/.env"
sed -i "s/^DB_DATABASE=.*/DB_DATABASE=${DB_NAME}/" "$BACKEND_DIR/.env"

# Create database
mysql -u"$DB_USER" -p"$DB_PASS" -e "CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null \
    && log "Database '${DB_NAME}' ready." \
    || warn "Could not create database automatically. Create it manually: CREATE DATABASE ${DB_NAME};"

# Run migrations
php artisan migrate --force && log "Migrations run."

# ─── Admin User ──────────────────────────────────────────────────────────────

header "Creating Admin User"

read -p "Admin email [admin@leadbot.local]: " ADMIN_EMAIL
ADMIN_EMAIL="${ADMIN_EMAIL:-admin@leadbot.local}"
read -sp "Admin password [Admin@12345]: " ADMIN_PASS
ADMIN_PASS="${ADMIN_PASS:-Admin@12345}"
echo ""

sed -i "s/^ADMIN_EMAIL=.*/ADMIN_EMAIL=${ADMIN_EMAIL}/" "$BACKEND_DIR/.env"
sed -i "s/^ADMIN_PASSWORD=.*/ADMIN_PASSWORD=${ADMIN_PASS}/" "$BACKEND_DIR/.env"

php artisan db:seed --class=AdminUserSeeder --force && log "Admin user created."

# ─── Storage Symlink ──────────────────────────────────────────────────────────

php artisan storage:link 2>/dev/null || true
mkdir -p "$BACKEND_DIR/storage/logs"
log "Storage configured."

# ─── Scraper ─────────────────────────────────────────────────────────────────

header "Setting Up Node.js Scraper"

cd "$SCRAPER_DIR"

if [ ! -d "node_modules" ]; then
    npm install && log "Scraper dependencies installed."
else
    log "Scraper dependencies already installed."
fi

# Set SCRAPER_PATH in .env
SCRAPER_JS_PATH="$SCRAPER_DIR/scraper.js"
sed -i "s|^SCRAPER_PATH=.*|SCRAPER_PATH=${SCRAPER_JS_PATH}|" "$BACKEND_DIR/.env"

# Set Instagram credentials
echo ""
warn "Now set your Instagram credentials in: backend/.env"
read -p "Instagram username (leave blank to skip): " IG_USER
read -sp "Instagram password (leave blank to skip): " IG_PASS
echo ""

if [ -n "$IG_USER" ]; then
    sed -i "s/^INSTAGRAM_USERNAME=.*/INSTAGRAM_USERNAME=${IG_USER}/" "$BACKEND_DIR/.env"
    sed -i "s/^INSTAGRAM_USERNAME=.*/INSTAGRAM_USERNAME=${IG_USER}/" "$SCRAPER_DIR/../backend/.env" 2>/dev/null || true
fi
if [ -n "$IG_PASS" ]; then
    sed -i "s/^INSTAGRAM_PASSWORD=.*/INSTAGRAM_PASSWORD=${IG_PASS}/" "$BACKEND_DIR/.env"
fi

# ─── Cron Setup ──────────────────────────────────────────────────────────────

header "Cron Job Setup"

PHP_BIN=$(which php)
ARTISAN_PATH="$BACKEND_DIR/artisan"

CRON_LINE="* * * * * ${PHP_BIN} ${ARTISAN_PATH} schedule:run >> /dev/null 2>&1"

if crontab -l 2>/dev/null | grep -q "schedule:run"; then
    log "Cron job already configured."
else
    (crontab -l 2>/dev/null; echo "$CRON_LINE") | crontab -
    log "Cron job added: runs Laravel scheduler every minute."
fi

# ─── Done ─────────────────────────────────────────────────────────────────────

header "Setup Complete!"

cat << EOF

${GREEN}LeadBot is ready!${NC}

  Start the server:
    cd backend && php artisan serve

  Open in browser:
    http://localhost:8000

  Login:
    Email:    ${ADMIN_EMAIL}
    Password: [what you set above]

  Manual scrape test (dry-run):
    cd backend && php artisan leadbot:scrape --dry-run

  Real scrape:
    cd backend && php artisan leadbot:scrape

  The scheduler will run daily at 9 AM automatically via cron.

  Edit keywords: scraper/keywords.json
  Edit credentials: backend/.env

${YELLOW}Remember: Only scrape public data. Respect Instagram's ToS.${NC}
EOF
