'use strict';

/**
 * LeadBot Instagram Scraper
 * -------------------------
 * Collects public profile data (username + bio) from Instagram
 * hashtag pages related to crypto/forex trading.
 *
 * Only collects PUBLIC data. Respects delays.
 * Outputs JSON array to stdout (for Laravel shell_exec).
 */

const puppeteer = require('puppeteer-extra');
const StealthPlugin = require('puppeteer-extra-plugin-stealth');
const fs = require('fs-extra');
const path = require('path');

const config = require('./config');
const logger = require('./utils/logger');
const { humanDelay, humanType, randomScroll, randomInt } = require('./utils/delay');

puppeteer.use(StealthPlugin());

const SESSION_FILE = path.resolve(__dirname, config.scraping.sessionFile);
const KEYWORDS_FILE = path.resolve(__dirname, './keywords.json');
const IS_DRY_RUN = process.argv.includes('--dry-run');

// ─── Session Management ────────────────────────────────────────────────────

async function saveSession(page) {
  const cookies = await page.cookies();
  const localStorage = await page.evaluate(() => {
    const data = {};
    for (let i = 0; i < window.localStorage.length; i++) {
      const key = window.localStorage.key(i);
      data[key] = window.localStorage.getItem(key);
    }
    return data;
  });
  await fs.writeJson(SESSION_FILE, { cookies, localStorage });
  logger.info('Session saved.');
}

async function loadSession(page) {
  if (!(await fs.pathExists(SESSION_FILE))) return false;
  try {
    const { cookies, localStorage } = await fs.readJson(SESSION_FILE);
    await page.setCookie(...cookies);
    await page.evaluate(data => {
      for (const [key, value] of Object.entries(data)) {
        window.localStorage.setItem(key, value);
      }
    }, localStorage || {});
    logger.info('Session restored from file.');
    return true;
  } catch {
    logger.warn('Failed to restore session. Will login fresh.');
    return false;
  }
}

// ─── Login ─────────────────────────────────────────────────────────────────

async function loginInstagram(page) {
  const { username, password } = config.instagram;

  if (!username || !password) {
    throw new Error(
      'INSTAGRAM_USERNAME and INSTAGRAM_PASSWORD must be set in backend/.env'
    );
  }

  logger.info('Navigating to Instagram login page...');
  await page.goto('https://www.instagram.com/accounts/login/', {
    waitUntil: 'networkidle2',
    timeout: 60000,
  });

  await humanDelay(2000, 4000);

  // Accept cookies if dialog appears
  try {
    const cookieBtn = await page.$x("//button[contains(text(), 'Allow')]");
    if (cookieBtn.length > 0) {
      await cookieBtn[0].click();
      await humanDelay(1000, 2000);
    }
  } catch {
    // No cookie dialog
  }

  // Type credentials
  await humanType(page, 'input[name="username"]', username);
  await humanDelay(500, 1200);
  await humanType(page, 'input[name="password"]', password);
  await humanDelay(800, 1500);

  // Submit
  await page.click('button[type="submit"]');
  await page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 30000 });

  // Handle "Save Login Info" dialog
  try {
    await page.waitForSelector("//button[contains(text(), 'Not Now')]", {
      timeout: 5000,
    });
    await page.click("//button[contains(text(), 'Not Now')]");
    await humanDelay(1000, 2000);
  } catch {
    // No dialog
  }

  // Handle notifications dialog
  try {
    const notNow = await page.$x("//button[contains(text(), 'Not Now')]");
    if (notNow.length > 0) {
      await notNow[0].click();
      await humanDelay(1000, 2000);
    }
  } catch {
    // No dialog
  }

  logger.success('Logged into Instagram successfully.');
  await saveSession(page);
}

async function ensureLoggedIn(page) {
  await page.goto('https://www.instagram.com/', {
    waitUntil: 'networkidle2',
    timeout: 60000,
  });
  await humanDelay(2000, 3000);

  const url = page.url();
  if (url.includes('/accounts/login')) {
    logger.info('Not logged in. Attempting login...');
    await loginInstagram(page);
  } else {
    logger.info('Already logged in via session.');
  }
}

// ─── Keyword Rotation ──────────────────────────────────────────────────────

function getNextHashtag() {
  const data = fs.readJsonSync(KEYWORDS_FILE);
  const { hashtags, rotation_index } = data;
  const index = rotation_index % hashtags.length;
  const hashtag = hashtags[index];
  const keyword = data.keywords[index % data.keywords.length];

  // Update rotation index
  data.rotation_index = index + 1;
  fs.writeJsonSync(KEYWORDS_FILE, data, { spaces: 2 });

  return { hashtag, keyword };
}

// ─── Profile Extraction ────────────────────────────────────────────────────

async function extractProfileData(page, username) {
  try {
    const profileUrl = `https://www.instagram.com/${username}/`;
    await page.goto(profileUrl, { waitUntil: 'networkidle2', timeout: 30000 });
    await humanDelay(1500, 3000);

    const data = await page.evaluate(() => {
      // Try multiple selectors for bio
      const bioSelectors = [
        'span._ap3a',
        'div._aa_c span',
        'div.x7a106z span',
        'section main header section div span',
        'header section div:nth-child(3) span',
      ];

      let bio = '';
      for (const sel of bioSelectors) {
        const el = document.querySelector(sel);
        if (el && el.innerText.trim()) {
          bio = el.innerText.trim();
          break;
        }
      }

      // Check if account is private
      const isPrivate =
        document.querySelector('h2._aacl') !== null &&
        document.body.innerHTML.includes('This account is private');

      return { bio, isPrivate };
    });

    if (data.isPrivate) {
      logger.info(`@${username} is private. Skipping.`);
      return null;
    }

    return {
      username,
      bio: data.bio || '',
      profile_url: profileUrl,
    };
  } catch (err) {
    logger.warn(`Failed to extract profile @${username}: ${err.message}`);
    return null;
  }
}

// ─── Hashtag Scraper ───────────────────────────────────────────────────────

async function scrapeHashtag(page, hashtag, keyword, maxLeads) {
  const leads = [];
  const seenUsernames = new Set();

  const tagUrl = `https://www.instagram.com/explore/tags/${hashtag}/`;
  logger.info(`Navigating to hashtag: #${hashtag}`);

  await page.goto(tagUrl, { waitUntil: 'networkidle2', timeout: 60000 });
  await humanDelay(3000, 5000);

  // Scroll to load more posts
  await randomScroll(page, 3);
  await humanDelay(2000, 4000);

  // Extract post links from the hashtag page
  const postLinks = await page.evaluate(() => {
    const anchors = Array.from(document.querySelectorAll('a[href*="/p/"]'));
    return [...new Set(anchors.map(a => a.href))].slice(0, 30);
  });

  logger.info(`Found ${postLinks.length} posts on #${hashtag}`);

  for (const postUrl of postLinks) {
    if (leads.length >= maxLeads) break;

    try {
      logger.info(`Visiting post: ${postUrl}`);
      await page.goto(postUrl, { waitUntil: 'networkidle2', timeout: 30000 });
      await humanDelay(2000, 4000);

      // Extract username from the post
      const username = await page.evaluate(() => {
        const selectors = [
          'header a._acan',
          'a.x1i10hfl[href^="/"][href$="/"]',
          'header section h2 a',
          'article header a',
        ];
        for (const sel of selectors) {
          const el = document.querySelector(sel);
          if (el) {
            const href = el.getAttribute('href');
            if (href && href.startsWith('/') && !href.includes('/p/')) {
              return href.replace(/\//g, '');
            }
          }
        }
        return null;
      });

      if (!username || seenUsernames.has(username)) continue;
      seenUsernames.add(username);

      logger.info(`Extracting profile: @${username}`);
      await humanDelay(1500, 3000);

      const profile = await extractProfileData(page, username);
      if (profile) {
        leads.push({
          ...profile,
          source_keyword: keyword,
          scraped_at: new Date().toISOString(),
        });
        logger.success(`Collected lead: @${username}`);
      }

      await humanDelay(config.scraping.minDelay, config.scraping.maxDelay);
    } catch (err) {
      logger.warn(`Error processing post ${postUrl}: ${err.message}`);
      await humanDelay(2000, 4000);
    }
  }

  return leads;
}

// ─── Main ──────────────────────────────────────────────────────────────────

async function main() {
  if (IS_DRY_RUN) {
    logger.info('DRY RUN mode — outputting mock data (Brazil male crypto leads).');
    const mockData = [
      {
        username: 'gabriel_btc_br',
        bio: '🇧🇷 Trader de crypto | Bitcoin investor | São Paulo | 28 anos | 💼 Analista financeiro | hodl gang',
        source_keyword: 'crypto trader brazil',
        scraped_at: new Date().toISOString(),
      },
      {
        username: 'pedro_forex_brasil',
        bio: '🇧🇷 Forex trader | BTC & ETH | Rio de Janeiro | ele/dele | born 1995 | 💼 Trader independente',
        source_keyword: 'forex brasil',
        scraped_at: new Date().toISOString(),
      },
      {
        username: 'lucas_cripto_sp',
        bio: 'Investidor de criptomoeda 🇧🇷 | Bitcoin | DeFi | Campinas, Brasil | 25yo | 💼 Engenheiro de software',
        source_keyword: 'investidor bitcoin brasil',
        scraped_at: new Date().toISOString(),
      },
      {
        username: 'rafael_hodl_brasil',
        bio: '🇧🇷 Crypto enthusiast | Binance trader | Belo Horizonte | ele | 30 anos | sinais gratuitos',
        source_keyword: 'crypto brasil',
        scraped_at: new Date().toISOString(),
      },
      {
        username: 'thiago_btc_rio',
        bio: 'Bitcoin & Ethereum 🇧🇷 | Trader desde 2018 | Rio de Janeiro | 💼 CEO at CriptoHub | DOB: 1993',
        source_keyword: 'bitcoinbrasil',
        scraped_at: new Date().toISOString(),
      },
    ];
    process.stdout.write(JSON.stringify(mockData));
    return;
  }

  const browser = await puppeteer.launch({
    headless: config.scraping.headless ? 'new' : false,
    args: [
      '--no-sandbox',
      '--disable-setuid-sandbox',
      '--disable-blink-features=AutomationControlled',
      '--disable-infobars',
      '--window-size=1366,768',
    ],
    defaultViewport: { width: 1366, height: 768 },
  });

  try {
    const page = await browser.newPage();

    // Set realistic user agent
    await page.setUserAgent(config.scraping.userAgent);

    // Set extra HTTP headers
    await page.setExtraHTTPHeaders({
      'Accept-Language': 'en-US,en;q=0.9',
    });

    // Try to restore session, otherwise login
    const sessionRestored = await loadSession(page);
    await ensureLoggedIn(page);

    // Get next hashtag to scrape (rotation system)
    const { hashtag, keyword } = getNextHashtag();
    logger.info(`Using hashtag: #${hashtag} | keyword: "${keyword}"`);

    const leads = await scrapeHashtag(
      page,
      hashtag,
      keyword,
      config.scraping.maxLeads
    );

    logger.success(`Scraped ${leads.length} leads.`);

    // Output JSON to stdout (consumed by Laravel)
    process.stdout.write(JSON.stringify(leads));
  } catch (err) {
    logger.error(`Fatal error: ${err.message}`);
    process.stdout.write(JSON.stringify([]));
    process.exit(1);
  } finally {
    await browser.close();
  }
}

main();
