// Parse the lab roster out of the CMDM-Lobe vault into publishable records.
//
// PRIVACY MODEL -- read before changing anything here.
//
// CMDM-Lobe is a PRIVATE repository. Its roster table mixes the handful of
// facts a public lab page should carry (who is here, what they work on) with
// material that must never leave the vault: oral-defense dates and outcomes,
// thesis-draft Google Drive links, certification and check-out progress, and
// leave-of-absence status.
//
// So this parser is deny-by-default in two directions:
//
//   1. A section is skipped entirely unless SECTION_POLICY names it. Adding a
//      new heading to the vault therefore publishes nothing until someone
//      updates this file on purpose.
//   2. Within a published section, a column is dropped unless it is in that
//      section's `columns` allowlist. DENIED_COLUMNS additionally exists as a
//      belt-and-braces tripwire: if one of those headers ever shows up in an
//      allowlist, the parser throws instead of quietly leaking it.
//
// tests/members-privacy.test.mjs enforces all of the above against the real
// vault file, so a regression fails CI rather than shipping.

/** Columns that must never reach the public site, whatever a policy says. */
export const DENIED_COLUMNS = new Set([
  '預計口試', '簡報', 'dry', '名單', '初稿', '寄送', '審定書', '離校',
  '執行', '撰寫', '口試', '投稿', 'revision', 'accepted',
  '負責人', '第一作者', '休學起始', '最後更新',
]);

/**
 * Which vault sections may be published, and which of their columns.
 *
 * `role` is the public grouping. Sections absent from this map -- the
 * manuscript submission tracker and the leave-of-absence table -- are private
 * in their entirety and are never emitted.
 */
export const SECTION_POLICY = {
  即將畢業: {
    role: 'current',
    // This table is almost entirely defense logistics. Only the name is safe;
    // degree level has to come from members-overrides.yml, so every person here
    // is flagged for review rather than guessed at.
    columns: ['姓名'],
    needsReview: 'graduating soon; assign degree level in members-overrides.yml',
  },
  博士班: { role: 'phd', columns: ['姓名', '年級', '所屬', '研究主題'] },
  碩士班: { role: 'masters', columns: ['姓名', '年級', '所屬', '研究主題'] },
  專題生: { role: 'undergraduate', columns: ['姓名', '所屬', '研究主題'] },
  '校友 / 早期成員': { role: 'alumni', columns: ['姓名', '研究主題', '備註'] },
};

/** Container headings that legitimately hold no table of their own. */
const CONTAINER_HEADINGS = new Set(['現役成員']);

/** Public field name for each allowlisted vault column. */
const COLUMN_FIELD = {
  姓名: 'name',
  年級: 'grade',
  所屬: 'department',
  研究主題: 'research',
  備註: 'note',
};

// A sentinel for an escaped pipe inside a cell. It was a NUL-delimited
// string, which made git classify this file as binary and print
// "Binary files differ" instead of a diff -- for the one file in the repo
// whose changes most need reading. U+E000 is private-use, so it cannot
// occur in the vault, and it keeps the file text.
const PIPE_TOKEN = '\uE000PIPE\uE000';

/** Split a markdown table row into cells, honouring escaped pipes inside links. */
export function splitRow(line) {
  return line
    .replace(/\\\|/g, PIPE_TOKEN)
    .replace(/^\s*\|/, '')
    .replace(/\|\s*$/, '')
    .split('|')
    .map((cell) => cell.split(PIPE_TOKEN).join('|').trim());
}

/**
 * Reduce vault markdown to plain public text.
 *
 * Wiki links keep their display label and every inline markdown link is dropped
 * outright -- those are Google Drive pointers to thesis drafts and slide decks,
 * which are exactly what must not be published.
 */
export function cleanCell(raw) {
  return String(raw ?? '')
    .replace(/\[\[[^\]|]*\|([^\]]*)\]\]/g, '$1')
    .replace(/\[\[([^\]]*)\]\]/g, '$1')
    .replace(/\[[^\]]*\]\([^)]*\)/g, '')
    .replace(/~~([^~]*)~~/g, '$1')
    .replace(/\*\*/g, '')
    .replace(/\s+/g, ' ')
    .trim();
}

/**
 * Department and degree-program codes the roster uses.
 *
 * The public site spells these out; DEPARTMENT_LABELS in src/lib/data.ts holds
 * the wording, and tests/members-privacy.test.mjs asserts the two lists agree.
 */
// PHARM is here for one alumna from 2013 rather than for anyone in the vault;
// the list is shared with DEPARTMENT_LABELS in src/lib/data.ts, which the
// alumni table reads, and tests/members-privacy.test.mjs asserts they agree.
export const DEPARTMENT_CODES = ['CSIE', 'BEBI', 'GSB', 'MHI', 'GINM', 'PHARM'];

/**
 * Reduce a 所屬 cell to the code alone.
 *
 * The column allowlist checks which *column* may be published; it cannot see
 * what a cell actually holds. One undergraduate's cell holds the code followed
 * by their student id, and the id was reaching data/members.yml and the rendered
 * members page, because the label helper prints anything it does not recognise
 * verbatim. So a department publishes as a known code or not at all, and
 * anything else about the cell is raised for review rather than emitted.
 *
 * The id is not quoted anywhere here, in the review file, or in a commit
 * message: this repository is public, and describing the shape of the problem is
 * enough to fix it.
 *
 * Returns `{ code, extra }`: `extra` is true when the cell held more than the
 * code. Its content is deliberately not returned -- the caller writes review
 * notes to a tracked file, and moving a student ID from one published file to
 * another is not a fix.
 */
export function normalizeDepartment(value) {
  const text = cleanCell(value);
  if (!text) return { code: '', extra: false };
  const tokens = text.split(/[\s,;·／/]+/).filter(Boolean);
  const code = tokens.find((token) => DEPARTMENT_CODES.includes(token.toUpperCase()));
  if (!code) return { code: '', extra: !isPlaceholder(text) };
  return { code: code.toUpperCase(), extra: tokens.length > 1 };
}

/** True when a cell is a placeholder rather than real content. */
export function isPlaceholder(text) {
  const value = cleanCell(text);
  if (!value) return true;
  if (value === '—' || value === '-') return true;
  if (/^TBD/i.test(value)) return true;
  return /待補|待定案/.test(value);
}

/** Pull a 4-digit graduation year out of an alumni note, if one is stated. */
export function graduationYear(note) {
  const match = cleanCell(note).match(/畢業\s*(\d{4})/);
  return match ? Number(match[1]) : null;
}

/** Split a trailing parenthesised English name or nickname off a name cell. */
function splitName(value) {
  const match = value.match(/^(.*?)\s*[（(]([^)）]*)[)）]\s*$/);
  if (!match) return { name: value, alias: '' };
  return { name: match[1].trim(), alias: match[2].trim() };
}

/**
 * Parse the roster markdown.
 *
 * Returns `{ members, review, skippedSections }` -- `review` carries rows that
 * parsed cleanly but need a human decision before they read well in public
 * (placeholder research topics, people with no degree level).
 */
export function parseRoster(markdown) {
  for (const [section, policy] of Object.entries(SECTION_POLICY)) {
    for (const column of policy.columns) {
      if (DENIED_COLUMNS.has(column)) {
        throw new Error(
          `SECTION_POLICY["${section}"] allowlists denied column "${column}". `
          + 'Private fields must never be published.',
        );
      }
    }
  }

  const members = [];
  const review = [];
  const skippedSections = [];

  let section = null;
  let policy = null;
  let header = null;

  for (const line of markdown.split('\n')) {
    const heading = line.match(/^#{2,3}\s+(.*?)\s*$/);
    if (heading) {
      const title = cleanCell(heading[1]);
      policy = SECTION_POLICY[title] ?? null;
      section = title;
      header = null;
      if (!policy && !CONTAINER_HEADINGS.has(title) && !skippedSections.includes(title)) {
        skippedSections.push(title);
      }
      continue;
    }
    if (!policy || !line.trim().startsWith('|')) continue;

    const cells = splitRow(line);
    if (!header) {
      header = cells.map((cell) => cleanCell(cell));
      continue;
    }
    if (/^[\s:-]+$/.test(cells.join(''))) continue; // separator row

    const record = { role: policy.role, section };
    for (const [index, column] of header.entries()) {
      if (!policy.columns.includes(column)) continue; // deny by default
      const field = COLUMN_FIELD[column];
      if (!field) continue;
      const value = cleanCell(cells[index]);

      if (field === 'name') {
        const { name, alias } = splitName(value);
        record.name = name;
        if (alias) record.alias = alias;
      } else if (field === 'note') {
        const year = graduationYear(value);
        if (year) record.graduated = year;
        // The remainder of the note is internal status and is discarded.
      } else if (field === 'department') {
        const { code, extra } = normalizeDepartment(value);
        if (code) record.department = code;
        if (extra) {
          // Never quote the surplus: this note is written to a tracked file.
          review.push({
            name: record.name ?? cleanCell(cells[0]),
            reason: 'the 所屬 cell holds more than a department code; only the code was published',
          });
        }
      } else if (!isPlaceholder(value)) {
        record[field] = value;
      }
    }
    if (!record.name) continue;

    if (policy.needsReview) {
      review.push({ name: record.name, reason: policy.needsReview });
    } else if (record.role !== 'alumni' && !record.research) {
      review.push({ name: record.name, reason: 'research topic is a placeholder in the vault' });
    }
    members.push(record);
  }

  return { members, review, skippedSections };
}
