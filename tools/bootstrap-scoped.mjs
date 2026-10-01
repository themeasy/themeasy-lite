/**
 * Bootstrap Scoped — the plugin's Bootstrap build for third-party themes
 *
 * The plugin loads Bootstrap on every front-end page. Its reboot is a set of
 * element rules (body, h1-h6, p, a, lists, button, img, table, *) that tie a
 * host theme's own element rules on specificity and win by load order, so on
 * a third-party theme it restyled the whole page: system-ui instead of the
 * theme's font, #212529 text, blue underlined links (backlog #265). A Themeasy
 * theme is built on that reboot (its style.css loads after it and overrides
 * it), so it keeps the full vendor file.
 *
 * This script derives bootstrap-scoped.min.css from the vendored
 * bootstrap.min.css, rule by rule, on the minified text itself (every rule it
 * keeps is byte-identical to the vendor's):
 *  - a selector that needs a class or a data-bs-* attribute stays as is (the
 *    grid, the components, the utilities);
 *  - `body` and the `:root` page rules (scroll-behavior) leave the page: the
 *    host owns it. The widget wrappers take only the body's base metrics
 *    (size, weight, line-height), so a widget keeps its type scale on a host
 *    whose body text is large or light, while the font family and the color
 *    still come from the host (a dark theme keeps its light text);
 *  - `:root` rules that only declare custom properties (the --bs-* variables)
 *    stay: the components and core.min.css read them;
 *  - every other element rule is scoped to the Themeasy widget wrappers
 *    through :where(), which adds no specificity, so inside a widget the rule
 *    keeps the weight and the order it has in the full build.
 *
 * Rerun it after upgrading bootstrap.min.css, and never edit the output.
 *
 * Usage:
 *   node tools/bootstrap-scoped.mjs           → write the scoped build
 *   node tools/bootstrap-scoped.mjs --check   → fail if it is out of date
 *
 * Exit code 0 = written (or up to date), 1 = out of date or unparsable input.
 */

import { readFileSync, writeFileSync, existsSync } from 'fs';
import { join, dirname } from 'path';
import { fileURLToPath } from 'url';

// ─── Constants ───────────────────────────────────────────────────

const ROOT = join(dirname(fileURLToPath(import.meta.url)), '..');
const VENDOR_DIR = join(ROOT, 'assets/css/vendor');
const SOURCE = join(VENDOR_DIR, 'bootstrap.min.css');
const OUTPUT = join(VENDOR_DIR, 'bootstrap-scoped.min.css');

// Every Themeasy widget wrapper (Elementor prints elementor-widget-{name}).
const SCOPE = ':where([class*=elementor-widget-themeasy-])';

// Page-level selectors the host theme owns outright.
const DROPPED = new Set(['body']);

// The body declarations the widget wrappers keep: a declaration there cuts
// inheritance, so the color and the family (host theme, Elementor Site
// Settings) stay out.
const WIDGET_BASE = ['font-size', 'font-weight', 'line-height'];

// At-rules whose body is a list of rules to transform; any other block
// (@keyframes, @font-face) is copied verbatim.
const NESTED_AT_RULES = /^@(media|supports|container|layer)\b/;

const NOTICE = '/* Generated from bootstrap.min.css by tools/bootstrap-scoped.mjs in https://github.com/themeasy/themeasy-lite: '
  + 'the element rules only reach the Themeasy widgets. Do not edit. */\n';

// ─── Parsing ─────────────────────────────────────────────────────

/**
 * Index of the character that closes the string opened at `start`.
 */
function skipString(css, start) {
  const quote = css[start];
  let i = start + 1;

  while (i < css.length && css[i] !== quote) {
    i += css[i] === '\\' ? 2 : 1;
  }

  return i;
}

/**
 * Split a block of CSS into top-level items: `{ prelude, body }` for a block,
 * `{ prelude, body: null }` for a statement, `{ comment }` for a comment.
 */
function parseItems(css) {
  const items = [];
  let i = 0;
  let start = 0;

  while (i < css.length) {
    const ch = css[i];

    if (ch === '/' && css[i + 1] === '*') {
      const end = css.indexOf('*/', i + 2);

      if (end === -1) {
        throw new Error(`Unterminated comment at ${i}`);
      }

      if (css.slice(start, i).trim() === '') {
        items.push({ comment: css.slice(i, end + 2) });
        start = end + 2;
      }

      i = end + 2;
      continue;
    }

    if (ch === '"' || ch === '\'') {
      i = skipString(css, i) + 1;
      continue;
    }

    if (ch === ';') {
      items.push({ prelude: css.slice(start, i).trim(), body: null });
      start = ++i;
      continue;
    }

    if (ch === '{') {
      let depth = 1;
      let j = i + 1;

      while (j < css.length && depth > 0) {
        if (css[j] === '"' || css[j] === '\'') {
          j = skipString(css, j);
        } else if (css[j] === '{') {
          depth++;
        } else if (css[j] === '}') {
          depth--;
        }

        j++;
      }

      if (depth !== 0) {
        throw new Error(`Unbalanced block at ${i}`);
      }

      items.push({ prelude: css.slice(start, i).trim(), body: css.slice(i + 1, j - 1) });
      start = i = j;
      continue;
    }

    i++;
  }

  if (css.slice(start).trim() !== '') {
    throw new Error(`Trailing text: ${css.slice(start, start + 60)}`);
  }

  return items;
}

/**
 * Split on `separator` outside strings, parentheses and brackets.
 */
function splitTopLevel(text, separator) {
  const parts = [];
  let depth = 0;
  let start = 0;

  for (let i = 0; i < text.length; i++) {
    const ch = text[i];

    if (ch === '"' || ch === '\'') {
      i = skipString(text, i);
    } else if (ch === '(' || ch === '[') {
      depth++;
    } else if (ch === ')' || ch === ']') {
      depth--;
    } else if (ch === separator && depth === 0) {
      parts.push(text.slice(start, i));
      start = i + 1;
    }
  }

  parts.push(text.slice(start));

  return parts.map((part) => part.trim()).filter((part) => part !== '');
}

/**
 * The selector with its (...) and [...] contents removed, so only the
 * compounds it actually matches on are left.
 */
function topLevelOf(selector) {
  let out = '';
  let depth = 0;

  for (let i = 0; i < selector.length; i++) {
    const ch = selector[i];

    if (ch === '"' || ch === '\'') {
      i = skipString(selector, i);
    } else if (ch === '(' || ch === '[') {
      // A data-bs-* attribute scopes the rule: leave a marker for it.
      if (depth === 0 && selector.startsWith('[data-bs-', i)) {
        out += '[data-bs-';
      }

      depth++;
    } else if (ch === ')' || ch === ']') {
      depth--;
    } else if (depth === 0) {
      out += ch;
    }
  }

  return out;
}

/**
 * Whether the selector can only match Bootstrap's own markup: a class, or a
 * data-bs-* attribute, outside any :not()/:is() argument.
 */
function isOwnMarkup(selector) {
  const top = topLevelOf(selector);

  return /\.[_a-zA-Z-]/.test(top) || top.includes('[data-bs-');
}

/**
 * Whether a declaration block only declares custom properties.
 */
function isCustomPropertiesOnly(body) {
  return splitTopLevel(body, ';').every((declaration) => declaration.startsWith('--'));
}

// ─── Transform ───────────────────────────────────────────────────

const stats = { kept: 0, scoped: 0, dropped: [] };

/**
 * The body's base metrics, as a rule on the widget wrappers.
 */
function widgetBaseRule(body) {
  const declarations = splitTopLevel(body, ';')
    .filter((declaration) => WIDGET_BASE.includes(declaration.split(':')[0].trim()));

  return declarations.length ? `${SCOPE}{${declarations.join(';')}}` : '';
}

/**
 * Rewrite one style rule; returns '' when no selector survives.
 */
function transformRule(prelude, body) {
  let extra = '';
  const selectors = splitTopLevel(prelude, ',').flatMap((selector) => {
    if (selector === 'body') {
      extra += widgetBaseRule(body);
    }

    if (isOwnMarkup(selector)) {
      stats.kept++;
      return [selector];
    }

    if (selector === ':root' && isCustomPropertiesOnly(body)) {
      stats.kept++;
      return [selector];
    }

    if (selector === ':root' || DROPPED.has(selector)) {
      stats.dropped.push(selector);
      return [];
    }

    stats.scoped++;
    return [`${SCOPE} ${selector}`];
  });

  return (selectors.length ? `${selectors.join(',')}{${body}}` : '') + extra;
}

/**
 * Rewrite a list of items; empty nested at-rules disappear with their rules.
 */
function transform(css) {
  return parseItems(css).map((item) => {
    if (item.comment !== undefined) {
      return '';
    }

    if (item.body === null) {
      return `${item.prelude};`;
    }

    if (NESTED_AT_RULES.test(item.prelude)) {
      const inner = transform(item.body);
      return inner ? `${item.prelude}{${inner}}` : '';
    }

    if (item.prelude.startsWith('@')) {
      return `${item.prelude}{${item.body}}`;
    }

    return transformRule(item.prelude, item.body);
  }).join('');
}

// ─── Main ────────────────────────────────────────────────────────

const source = readFileSync(SOURCE, 'utf8').replace(/\r\n/g, '\n');
const banner = source.match(/^\/\*![\s\S]*?\*\/\n?/);

if (!banner) {
  console.error('✗ bootstrap.min.css has no license banner — is it the vendor file?');
  process.exit(1);
}

let output;

try {
  output = `${banner[0].trimEnd()}\n${NOTICE}${transform(source.slice(banner[0].length))}\n`;
} catch (error) {
  console.error(`✗ Could not parse bootstrap.min.css: ${error.message}`);
  process.exit(1);
}

if (process.argv.includes('--check')) {
  const current = existsSync(OUTPUT) ? readFileSync(OUTPUT, 'utf8').replace(/\r\n/g, '\n') : '';

  if (current !== output) {
    console.error('✗ bootstrap-scoped.min.css is out of date: run node tools/bootstrap-scoped.mjs');
    process.exit(1);
  }

  console.log('✓ bootstrap-scoped.min.css is up to date');
  process.exit(0);
}

writeFileSync(OUTPUT, output);

console.log(`✓ Wrote ${OUTPUT.slice(ROOT.length + 1)} (${output.length} bytes)`);
console.log(`  ${stats.kept} selectors kept, ${stats.scoped} scoped to the widgets, `
  + `${stats.dropped.length} dropped (${[...new Set(stats.dropped)].join(', ')})`);
