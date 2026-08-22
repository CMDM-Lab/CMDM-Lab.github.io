// Tests for the publication override mechanism.
//
// These matter because both failure directions are silent. An exclusion that
// does not match leaves someone else's paper on the lab's publication list; an
// inclusion that does not match quietly drops a real paper. Neither shows up as
// an error, only as a wrong number in the sync PR that nobody counts.
//
// The last test guards the specific case that prompted this module: a PubMed-only
// record with no DOI, which the original DOI-only exclusion could not name.

import { test } from 'node:test';
import assert from 'node:assert/strict';
import { readFile } from 'node:fs/promises';
import path from 'node:path';
import YAML from 'yaml';
import { listed, normalizePatch, splitIdentifiers } from '../scripts/lib/overrides.mjs';
import { plainText } from '../scripts/lib/fetch-util.mjs';

const ROOT = path.resolve(import.meta.dirname, '..');
const OVERRIDES = path.join(ROOT, 'data', 'publications-overrides.yml');

test('splitIdentifiers separates DOIs from PMIDs', () => {
  const { dois, pmids } = splitIdentifiers([
    '10.1002/jcsm.12895',
    'pmid:23542986',
    'PMID: 12345',
    'https://doi.org/10.1016/j.envres.2021.111448',
  ]);
  assert.deepEqual([...dois].sort(), [
    '10.1002/jcsm.12895',
    '10.1016/j.envres.2021.111448',
  ]);
  assert.deepEqual([...pmids].sort(), ['12345', '23542986']);
});

test('splitIdentifiers normalises DOI case and resolver prefixes', () => {
  const { dois } = splitIdentifiers([
    'HTTPS://DOI.ORG/10.1111/BJD.18425',
    'doi:10.1111/bjd.18425',
    '  10.1111/bjd.18425  ',
  ]);
  // All three name the same paper, so they collapse to one entry.
  assert.deepEqual([...dois], ['10.1111/bjd.18425']);
});

test('splitIdentifiers reports junk instead of accepting or throwing', () => {
  // The file is hand-edited; one bad line must not take down the weekly sync.
  // But it must not be swallowed either: a mistyped DOI that is accepted as
  // valid becomes a rule that matches nothing, with no error to notice.
  const { dois, pmids, invalid } = splitIdentifiers([
    '', null, undefined, 'not-a-doi', 'pmid:abc', '10.1234', 'jcsm.12895',
  ]);
  assert.equal(dois.size, 0, 'nothing here is a well-formed DOI');
  assert.equal(pmids.size, 0);
  assert.deepEqual(invalid, ['not-a-doi', 'pmid:abc', '10.1234', 'jcsm.12895']);
});

test('the real overrides file contains no malformed identifiers', async () => {
  const parsed = YAML.parse(await readFile(OVERRIDES, 'utf8')) ?? {};
  for (const section of ['include', 'exclude']) {
    const { invalid } = splitIdentifiers(parsed[section]);
    assert.deepEqual(invalid, [], `${section}: has malformed entries`);
  }
});

test('listed matches on DOI, on PMID, and on neither', () => {
  const list = splitIdentifiers(['10.1111/bjd.18425', 'pmid:23542986']);

  assert.equal(listed(list, { doi: '10.1111/bjd.18425', pmid: '31408190' }), true);
  assert.equal(listed(list, { doi: '', pmid: '23542986' }), true, 'PMID-only record must match');
  assert.equal(listed(list, { doi: '10.1038/nbt.1234', pmid: '999' }), false);
  assert.equal(listed(list, { doi: '', pmid: '' }), false);
});

test('listed normalises the record DOI before comparing', () => {
  const list = splitIdentifiers(['10.1002/jcsm.12895']);
  assert.equal(listed(list, { doi: 'HTTPS://doi.org/10.1002/JCSM.12895' }), true);
});

test('normalizePatch lowercases its keys', () => {
  const patched = normalizePatch({ 'HTTPS://DOI.ORG/10.1038/NBT.1': { journal: 'x' } });
  assert.deepEqual(Object.keys(patched), ['10.1038/nbt.1']);
});

test('the real overrides file parses and has no contradictions', async () => {
  const parsed = YAML.parse(await readFile(OVERRIDES, 'utf8')) ?? {};
  const include = splitIdentifiers(parsed.include);
  const exclude = splitIdentifiers(parsed.exclude);

  for (const doi of include.dois) {
    assert.ok(!exclude.dois.has(doi), `${doi} is in both include and exclude`);
  }
  for (const pmid of include.pmids) {
    assert.ok(!exclude.pmids.has(pmid), `pmid:${pmid} is in both include and exclude`);
  }
  assert.ok(include.dois.size > 0, 'expected at least one confirmed inclusion');
});

test('every confirmed inclusion actually reached data/publications.yml', async () => {
  // An include entry that matches nothing is indistinguishable from a typo, and
  // the symptom -- a paper quietly missing from the site -- is easy to miss.
  const parsed = YAML.parse(await readFile(OVERRIDES, 'utf8')) ?? {};
  const include = splitIdentifiers(parsed.include);
  const published = YAML.parse(
    await readFile(path.join(ROOT, 'data', 'publications.yml'), 'utf8'),
  ).publications ?? [];

  const publishedDois = new Set(published.map((p) => p.doi).filter(Boolean));
  const publishedPmids = new Set(published.map((p) => String(p.pmid)).filter(Boolean));

  for (const doi of include.dois) {
    assert.ok(publishedDois.has(doi), `${doi} is in include: but not in publications.yml`);
  }
  for (const pmid of include.pmids) {
    assert.ok(publishedPmids.has(pmid), `pmid:${pmid} is in include: but not in publications.yml`);
  }
});

test('nothing excluded slipped into data/publications.yml', async () => {
  const parsed = YAML.parse(await readFile(OVERRIDES, 'utf8')) ?? {};
  const exclude = splitIdentifiers(parsed.exclude);
  const published = YAML.parse(
    await readFile(path.join(ROOT, 'data', 'publications.yml'), 'utf8'),
  ).publications ?? [];

  for (const publication of published) {
    assert.ok(
      !listed(exclude, publication),
      `"${publication.title}" is excluded but appears in publications.yml`,
    );
  }
});

// ---------------------------------------------------------------------------
// Publisher markup, and the authoritative include list
// ---------------------------------------------------------------------------

test('plainText strips publisher markup from titles', () => {
  // Crossref really returns this. Left alone it rendered on the live page as
  // literal "&lt;i&gt;Tetrahymena pyriformis&lt;/i&gt;".
  assert.equal(
    plainText('Protocols for Exploring hERG Classification and <i>Tetrahymena pyriformis</i> End Point Predictions'),
    'Protocols for Exploring hERG Classification and Tetrahymena pyriformis End Point Predictions',
  );
  assert.equal(plainText('CO<sub>2</sub> capture'), 'CO2 capture');
  assert.equal(plainText('<scp>DNA</scp> repair'), 'DNA repair');
});

test('plainText decodes entities and collapses whitespace', () => {
  assert.equal(plainText('Drug &amp; Device   Research'), 'Drug & Device Research');
  assert.equal(plainText('&#8722;80 &#x00B0;C storage'), '−80 °C storage');
  assert.equal(plainText(''), '');
  assert.equal(plainText(undefined), '');
});

test('no published title or journal contains markup', async () => {
  // The tripwire for the bug above: it must not come back as new papers arrive.
  const published = YAML.parse(
    await readFile(path.join(ROOT, 'data', 'publications.yml'), 'utf8'),
  ).publications ?? [];
  for (const publication of published) {
    for (const field of ['title', 'journal']) {
      assert.ok(
        !/<[a-zA-Z/][^>]*>/.test(publication[field] ?? ''),
        `${field} of "${publication.title}" contains HTML markup`,
      );
    }
  }
});

test('publications reached by include: alone are fully enriched', async () => {
  // `include:` can name a paper that no source returned, in which case the only
  // metadata comes from Crossref. An entry that stayed a bare stub would render
  // as an empty list item.
  const published = YAML.parse(
    await readFile(path.join(ROOT, 'data', 'publications.yml'), 'utf8'),
  ).publications ?? [];
  const adopted = published.filter((p) => (p.sources ?? []).includes('manual-include'));

  for (const publication of adopted) {
    assert.ok(publication.title, `${publication.doi} was adopted but has no title`);
    assert.ok(publication.journal, `${publication.doi} was adopted but has no journal`);
    assert.ok(publication.year, `${publication.doi} was adopted but has no year`);
    assert.ok(
      (publication.authors ?? []).length > 0,
      `${publication.doi} was adopted but has no authors`,
    );
  }
});
