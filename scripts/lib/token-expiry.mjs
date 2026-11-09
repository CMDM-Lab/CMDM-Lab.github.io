// Read the expiry of a fine-grained token from GitHub's response header.
//
// Every API call made with a token that has an expiry date returns
// `github-authentication-token-expiration`, e.g. `2027-11-10 08:00:00 UTC` or
// `2027-11-10 08:00:00 +0800`. A token with no expiry sends no header.

export const EXPIRY_WARNING_DAYS = 30;

const DAY_MS = 24 * 60 * 60 * 1000;
const HEADER = /^(\d{4}-\d{2}-\d{2}) (\d{2}:\d{2}:\d{2}) (UTC|[+-]\d{2}\d{2})$/;

/** @returns {Date|null} null when the token does not expire. */
export function parseTokenExpiration(value) {
  if (!value) return null;
  const m = HEADER.exec(value.trim());
  // An unknown format must not read as "no expiry": that is the silent case
  // this check exists to remove.
  if (!m) throw new Error(`unrecognised token expiration header: ${value}`);
  const offset = m[3] === 'UTC' ? 'Z' : `${m[3].slice(0, 3)}:${m[3].slice(3)}`;
  return new Date(`${m[1]}T${m[2]}${offset}`);
}

/** Whole days from `now` to `expiry`; negative once it has passed. */
export function daysUntil(expiry, now = new Date()) {
  return Math.floor((expiry - now) / DAY_MS);
}
