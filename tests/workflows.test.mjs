import { test } from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { parse } from 'yaml';

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
