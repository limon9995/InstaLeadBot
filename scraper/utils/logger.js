'use strict';

const fs = require('fs');
const path = require('path');
const config = require('../config');

const logFile = path.resolve(__dirname, '..', config.output.logFile);

function timestamp() {
  return new Date().toISOString();
}

function writeLog(level, message) {
  const line = `[${timestamp()}] [${level}] ${message}\n`;
  fs.appendFileSync(logFile, line, 'utf8');
}

const logger = {
  info: (msg) => {
    process.stderr.write(`\x1b[36m[INFO]\x1b[0m ${msg}\n`);
    writeLog('INFO', msg);
  },
  warn: (msg) => {
    process.stderr.write(`\x1b[33m[WARN]\x1b[0m ${msg}\n`);
    writeLog('WARN', msg);
  },
  error: (msg) => {
    process.stderr.write(`\x1b[31m[ERROR]\x1b[0m ${msg}\n`);
    writeLog('ERROR', msg);
  },
  success: (msg) => {
    process.stderr.write(`\x1b[32m[SUCCESS]\x1b[0m ${msg}\n`);
    writeLog('SUCCESS', msg);
  },
};

module.exports = logger;
