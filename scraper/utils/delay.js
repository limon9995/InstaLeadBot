'use strict';

/**
 * Returns a random integer between min and max (inclusive)
 */
function randomInt(min, max) {
  return Math.floor(Math.random() * (max - min + 1)) + min;
}

/**
 * Human-like delay between min and max ms
 */
function humanDelay(min = 2500, max = 6000) {
  const ms = randomInt(min, max);
  return new Promise(resolve => setTimeout(resolve, ms));
}

/**
 * Short micro-delay for typing simulation
 */
function typeDelay() {
  return humanDelay(80, 200);
}

/**
 * Simulate human typing into an input field
 */
async function humanType(page, selector, text) {
  await page.focus(selector);
  await humanDelay(300, 700);
  for (const char of text) {
    await page.keyboard.type(char);
    await typeDelay();
  }
}

/**
 * Simulate random scrolling on the page
 */
async function randomScroll(page, times = 3) {
  for (let i = 0; i < times; i++) {
    const scrollAmount = randomInt(300, 800);
    await page.evaluate(amount => {
      window.scrollBy({ top: amount, behavior: 'smooth' });
    }, scrollAmount);
    await humanDelay(800, 2000);
  }
}

module.exports = { humanDelay, typeDelay, humanType, randomScroll, randomInt };
