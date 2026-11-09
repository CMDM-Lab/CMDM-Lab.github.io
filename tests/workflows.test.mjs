import { test } from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { parse } from 'yaml';
import { parseTokenExpiration, daysUntil, EXPIRY_WARNING_DAYS } from '../scripts/lib/token-expiry.mjs';

// A missing CMDM_LOBE_TOKEN once skipped the member sync for three weeks while
// every run stayed green (翁菡隃 and 林榆涵 were on the roster, not on the site).
// The skip must raise something a person will see.
const sync = parse(readFileSync('.github/workflows/sync-data.yml', 'utf8'));
const steps = sync.jobs.sync.steps;

test('sync-data may open issues', () => {
  assert.equal(sync.permissions?.issues, 'write');
});

test('sync-data raises an issue when the vault token is missing', () => {
  const raise = steps.find(
    (s) => /HAS_VAULT_TOKEN\s*!=\s*'true'/.test(s.if ?? '')
      && s.uses?.startsWith('actions/github-script@')
      && /issues\.create\(/.test(s.with?.script ?? ''),
  );
  assert.ok(raise, 'no step opens an issue when HAS_VAULT_TOKEN is not true');
  assert.match(raise.with.script, /labels:\s*'vault-token'|labels:\s*\['vault-token'\]/);
});

test('sync-data closes the token issue once the member sync runs again', () => {
  const close = steps.find(
    (s) => /steps\.vault\.outcome\s*==\s*'success'/.test(s.if ?? '')
      && /state:\s*'closed'/.test(s.with?.script ?? ''),
  );
  assert.ok(close, 'no step closes the vault-token issue after a successful sync');
});

// The token expires on a fixed date. GitHub reports it in the
// `github-authentication-token-expiration` response header; the sync reads it
// and raises an issue in the last 30 days, so a renewal is not left to memory.

test('the expiration header parses in both forms GitHub sends', () => {
  assert.equal(parseTokenExpiration('2027-11-10 08:00:00 UTC').toISOString(), '2027-11-10T08:00:00.000Z');
  assert.equal(parseTokenExpiration('2027-11-10 08:00:00 +0800').toISOString(), '2027-11-10T00:00:00.000Z');
});

test('a token without an expiration date yields null, not a date', () => {
  assert.equal(parseTokenExpiration(null), null);
  assert.equal(parseTokenExpiration(''), null);
});

test('an unreadable expiration header is an error, not silence', () => {
  assert.throws(() => parseTokenExpiration('next November'));
});

test('days until expiry count whole days and go negative after it', () => {
  const exp = new Date('2027-11-10T00:00:00Z');
  assert.equal(daysUntil(exp, new Date('2027-10-11T00:00:00Z')), 30);
  assert.equal(daysUntil(exp, new Date('2027-10-10T12:00:00Z')), 30);
  assert.equal(daysUntil(exp, new Date('2027-11-11T00:00:00Z')), -1);
  assert.equal(EXPIRY_WARNING_DAYS, 30);
});

test('sync-data checks the token expiry after a successful vault checkout', () => {
  const check = steps.find(
    (s) => /steps\.vault\.outcome\s*==\s*'success'/.test(s.if ?? '')
      && /token-expiry\.mjs/.test(s.with?.script ?? '')
      && /github-authentication-token-expiration/.test(s.with?.script ?? ''),
  );
  assert.ok(check, 'no step reads the token expiration header');
  assert.match(check.with.script, /'vault-token-expiry'/);
  assert.match(check.env?.VAULT_TOKEN ?? '', /secrets\.CMDM_LOBE_TOKEN/);
});
