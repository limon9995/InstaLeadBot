'use strict';

require('dotenv').config({ path: '../backend/.env' });

module.exports = {
  instagram: {
    username: process.env.INSTAGRAM_USERNAME || '',
    password: process.env.INSTAGRAM_PASSWORD || '',
  },
  scraping: {
    maxLeads: parseInt(process.env.MAX_LEADS_PER_RUN) || 10,
    minDelay: 2500,
    maxDelay: 6000,
    scrollDelay: 1500,
    headless: process.env.SCRAPER_HEADLESS !== 'false',
    sessionFile: './session.json',
    userAgent:
      'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
  },
  output: {
    logFile: './scraper.log',
  },
};
