# LeadBot — Instagram Lead Generation System

A personal lead generation system that automatically collects Instagram profiles of crypto/forex traders, filters them by country and gender, and displays them in a clean dashboard.

---

## Architecture

```
LeadBot/
├── scraper/            # Node.js + Puppeteer Instagram scraper
│   ├── scraper.js      # Main scraper (outputs JSON to stdout)
│   ├── config.js       # Config (reads from backend/.env)
│   ├── keywords.json   # Hashtag rotation list
│   └── utils/
│       ├── delay.js    # Human-like delays & typing simulation
│       └── logger.js   # Logging to stderr + log file
│
├── backend/            # Laravel 10 application
│   ├── app/
│   │   ├── Models/
│   │   │   ├── Lead.php              # Lead model + scopes
│   │   │   └── ActivityLog.php       # Activity logging
│   │   ├── Http/Controllers/
│   │   │   ├── DashboardController   # Stats overview
│   │   │   ├── LeadController        # CRUD + export
│   │   │   ├── AuthController        # Login/logout
│   │   │   └── Api/LeadApiController # POST /api/leads/import
│   │   ├── Services/
│   │   │   └── LeadFilterService     # Crypto/country/gender detection
│   │   └── Console/Commands/
│   │       └── ScrapeInstagram       # php artisan leadbot:scrape
│   ├── database/migrations/
│   ├── resources/views/
│   │   ├── layouts/app.blade.php     # Dark sidebar layout
│   │   ├── dashboard.blade.php       # Stats + charts
│   │   ├── leads/index.blade.php     # Lead table + filters
│   │   └── leads/show.blade.php      # Lead detail + notes
│   └── routes/
│       ├── web.php                   # Dashboard routes
│       └── api.php                   # API routes
│
└── setup.sh            # One-command setup script
```

---

## Quick Start

### 1. Prerequisites

```bash
# Ubuntu/Debian
sudo apt install php8.2 php8.2-{cli,mbstring,xml,mysql,curl,zip} composer mysql-server nodejs npm

# Install Chrome for Puppeteer
sudo apt install chromium-browser
```

### 2. Run Setup

```bash
cd /path/to/LeadBot
bash setup.sh
```

The script will:
- Create the Laravel project and install dependencies
- Create the MySQL database and run migrations
- Create the admin user
- Install Node.js scraper dependencies
- Set up the cron job

### 3. Configure Credentials

Edit `backend/.env`:

```dotenv
# Database
DB_DATABASE=leadbot
DB_USERNAME=root
DB_PASSWORD=your_password

# Instagram credentials (for scraper)
INSTAGRAM_USERNAME=your_ig_username
INSTAGRAM_PASSWORD=your_ig_password

# Paths
SCRAPER_PATH=/absolute/path/to/LeadBot/scraper/scraper.js
NODE_BINARY=/usr/bin/node
```

### 4. Start the Server

```bash
cd backend
php artisan serve
# Open: http://localhost:8000
```

---

## Manual Setup (without setup.sh)

```bash
# 1. Create Laravel project
composer create-project laravel/laravel:^10.0 backend

# 2. Copy custom files
cp -r [this repo's backend files] backend/

# 3. Configure
cd backend
cp .env.example .env
php artisan key:generate

# 4. Create DB and migrate
mysql -u root -p -e "CREATE DATABASE leadbot;"
php artisan migrate
php artisan db:seed --class=AdminUserSeeder

# 5. Install scraper
cd ../scraper
npm install
```

---

## Usage

### Dashboard
Visit `http://localhost:8000/dashboard` to see:
- Total leads, today's count, hot/warm/cold breakdown
- Country distribution chart
- Tag doughnut chart
- Recent leads table
- Activity log

### Leads Page
`http://localhost:8000/leads`
- Filter by country, gender, tag, keyword
- Search by username or bio text
- Inline tag updates (hot/warm/cold dropdown)
- Export filtered results to CSV

### Lead Detail
- View full bio
- Tag management
- Mark as contacted
- Add private notes

---

## Running the Scraper

### Dry Run (no real scraping, uses mock data)
```bash
cd backend
php artisan leadbot:scrape --dry-run
```

### Real Scrape
```bash
php artisan leadbot:scrape
# or limit leads:
php artisan leadbot:scrape --max=5
```

### Import via API
```bash
curl -X POST http://localhost:8000/api/leads/import \
  -H "Content-Type: application/json" \
  -d '{"leads":[{"username":"cryptojohn","bio":"🇺🇸 Crypto trader | BTC investor","source_keyword":"crypto trader"}]}'
```

---

## Cron Job (Automation)

The setup script adds this to crontab automatically:

```cron
* * * * * /usr/bin/php /path/to/backend/artisan schedule:run >> /dev/null 2>&1
```

The Laravel scheduler runs `leadbot:scrape --max=10` **daily at 9:00 AM**.

### Check current cron:
```bash
crontab -l
```

### Manual crontab entry:
```bash
crontab -e
# Add:
* * * * * /usr/bin/php /path/to/LeadBot/backend/artisan schedule:run >> /dev/null 2>&1
```

---

## Filtering Logic

The `LeadFilterService` analyzes each scraped bio and applies 3 filters:

| Filter | Details |
|--------|---------|
| **Crypto Interest** | Must contain: crypto, bitcoin, btc, forex, trading, trader, investor, defi, nft, blockchain, altcoin, hodl, web3, etc. |
| **Country** | Detects from text (USA, Germany, Canada, UK, Australia, Brazil, UAE, Switzerland) and flag emojis (🇺🇸 🇩🇪 🇨🇦 🇬🇧 🇦🇺 🇧🇷 🇦🇪 🇨🇭) |
| **Gender** | Must be probable male (name-based + pronoun detection) |

Only leads that pass **all 3 filters** are saved.

### Lead Scoring

| Score | Tag | Criteria |
|-------|-----|----------|
| 60–100 | 🔥 Hot | Many crypto keywords + premium country (USA/UK/UAE/Switzerland) |
| 30–59  | ☀️ Warm | Some crypto keywords + target country |
| 0–29   | ❄️ Cold | Minimal crypto signal |

---

## Keyword Rotation

`scraper/keywords.json` has a `rotation_index` that advances each run.
Each run uses the next hashtag in the list, so different keywords are
targeted daily without repetition.

---

## Security

- All dashboard routes require login (`auth` middleware)
- API routes are rate-limited (60 req/min per IP)
- CSRF protection on all forms
- Scraper only collects **public** profile data
- Human-like delays prevent aggressive scraping
- Max 10 leads per run by default

---

## Deployment (Production)

```bash
# 1. On your server
cd backend
composer install --no-dev --optimize-autoloader
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 2. Set APP_ENV=production and APP_DEBUG=false in .env

# 3. Use a proper web server (nginx + php-fpm)
# Example nginx config:
# server {
#     listen 80;
#     root /var/www/LeadBot/backend/public;
#     index index.php;
#     location / { try_files $uri $uri/ /index.php?$query_string; }
#     location ~ \.php$ { fastcgi_pass unix:/var/run/php/php8.2-fpm.sock; ... }
# }

# 4. Set up supervisor or systemd to keep the scheduler running
# Or rely on the cron job added by setup.sh
```

---

## Troubleshooting

| Problem | Fix |
|---------|-----|
| Scraper outputs empty JSON | Check `INSTAGRAM_USERNAME`/`INSTAGRAM_PASSWORD` in .env |
| Instagram login fails | Clear `scraper/session.json`, try with `SCRAPER_HEADLESS=false` |
| `php artisan leadbot:scrape` fails | Check `SCRAPER_PATH` and `NODE_BINARY` in .env |
| Database connection error | Verify DB_* credentials in .env, ensure MySQL is running |
| Charts not showing | Check browser console; Tailwind CDN + Chart.js CDN require internet |

---

## Ethics & Legal

- This tool is for **personal use only**
- Only collects **public** Instagram data (username + bio)
- Respects rate limits with human-like delays
- Scraper is limited to 10 leads/day by default
- You are responsible for compliance with Instagram's Terms of Service
  and applicable data protection laws (GDPR, CCPA, etc.)
