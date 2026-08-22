#!/usr/bin/env python3
"""One-time migration: lift content out of the legacy jQuery-loaded HTML fragments.

NOT part of the build and NOT run in CI. This exists so the move off the old
`contents/*.html` fragments is auditable -- it reports a count for every list it
extracts, so nothing is silently dropped -- and so it can be re-run if the
mapping turns out to be wrong. Once the YAML files it writes are reviewed, they
become the hand-maintained source and this script's job is done.

Why this matters: the automated ORCID + Crossref sync covers journal articles
only. Conference abstracts, patents and books exist nowhere but these legacy
pages, so migrating them by hand risks losing entries. Hence a script with
counts rather than copy-paste.

Usage:
    python3 scripts/migration/extract-legacy-content.py
"""

from __future__ import annotations

import html
import pathlib
import re
import sys

try:
    import yaml
except ImportError:  # pragma: no cover - developer convenience
    sys.exit("PyYAML required: pip install pyyaml")

ROOT = pathlib.Path(__file__).resolve().parents[2]
CONTENTS = ROOT / "contents"
DATA = ROOT / "data"

# Sections of contents/honor_zh.html, keyed by the label used in the page.
HONOR_SECTIONS = {
    "A": "achievements",
    "B": "journal_papers",   # superseded by the ORCID/Crossref sync
    "C": "conference_papers",
    "D": "patents",
    "E": "books",
}


def text_of(fragment: str) -> str:
    """Strip tags and collapse whitespace, keeping the human-readable citation."""
    without_tags = re.sub(r"<[^>]+>", "", fragment)
    return html.unescape(re.sub(r"\s+", " ", without_tags)).strip()


def links_of(fragment: str) -> list[dict[str, str]]:
    """Collect the anchors in a list item so citations keep their references."""
    out = []
    for href, label in re.findall(r'<a[^>]*href=["\']?([^"\'>\s]+)["\']?[^>]*>(.*?)</a>',
                                  fragment, re.S):
        text = text_of(label).strip("[]")
        if text:
            out.append({"label": text, "url": html.unescape(href)})
    return out


def list_items(block: str) -> list[str]:
    """Extract <li> contents from a block of HTML."""
    return re.findall(r"<li>(.*?)</li>", block, re.S)


def read(name: str) -> str:
    return (CONTENTS / name).read_text(encoding="utf-8")


def dump(path: pathlib.Path, header: str, payload: dict) -> None:
    with path.open("w", encoding="utf-8") as handle:
        handle.write(header.rstrip() + "\n")
        yaml.safe_dump(payload, handle, allow_unicode=True, sort_keys=False, width=100)
    print(f"  wrote {path.relative_to(ROOT)}")


def extract_honor() -> dict[str, list]:
    """Split honor_zh.html into its five lettered sections."""
    src = read("honor_zh.html")
    sections: dict[str, list] = {}
    chunks = re.split(r'<h5 class="text-danger">', src)
    for chunk in chunks[1:]:
        label = text_of(chunk.split("</h5>")[0])
        letter = label.split(".")[0].strip()
        key = HONOR_SECTIONS.get(letter)
        if not key:
            print(f"  !! unmapped honor section {label!r}")
            continue
        body = chunk.split("</h5>", 1)[1]
        entries = []
        for item in list_items(body):
            citation = text_of(item)
            if not citation:
                continue
            entry: dict = {"citation": citation}
            refs = links_of(item)
            if refs:
                entry["links"] = refs
            entries.append(entry)
        sections[key] = entries
        print(f"  honor {label!r}: {len(entries)} items")
    return sections


def extract_legacy_publications() -> dict[str, list]:
    """Read the orphaned publication_zh.html, which no route ever reached."""
    src = read("publication_zh.html")
    mapping = {"A": "journal_papers", "B": "conference_papers", "C": "patents"}
    out: dict[str, list] = {}
    chunks = re.split(r"<h5>", src)
    for chunk in chunks[1:]:
        label = text_of(chunk.split("</h5>")[0])
        key = mapping.get(label.split(".")[0].strip())
        if not key:
            print(f"  !! unmapped publication section {label!r}")
            continue
        entries = [text_of(i) for i in list_items(chunk) if text_of(i)]
        out[key] = entries
        print(f"  legacy publication {label!r}: {len(entries)} items")
    return out


def extract_services() -> list[dict]:
    """Each software tool is an <h4> title plus following prose and links."""
    src = read("service_zh.html")
    services = []
    chunks = re.split(r"<h4[^>]*>", src)
    for chunk in chunks[1:]:
        title = text_of(chunk.split("</h4>")[0])
        if not title:
            continue
        body = chunk.split("</h4>", 1)[1]
        # Stop at the next section boundary so descriptions do not run together.
        body = re.split(r"<h[1-4]|<hr", body)[0]
        entry = {"name": title, "description": text_of(body)[:600]}
        refs = links_of(body)
        if refs:
            entry["links"] = refs
        img = re.search(r'<img[^>]*src=["\']([^"\']+)["\']', body)
        if img:
            entry["image"] = "/" + img.group(1).lstrip("./")
        services.append(entry)
        print(f"  service {title!r}: {len(entry.get('links', []))} links")
    return services


def extract_prof_activities() -> dict[str, list]:
    src = read("prof_acts_zh.html")
    mapping = {
        "A": "program_committee",
        "B": "talks",
        "C": "paper_review",
    }
    out: dict[str, list] = {}
    for chunk in re.split(r"<h5[^>]*>", src)[1:]:
        label = text_of(chunk.split("</h5>")[0])
        key = mapping.get(label.split(".")[0].strip())
        if not key:
            print(f"  !! unmapped activity section {label!r}")
            continue
        entries = [text_of(i) for i in list_items(chunk) if text_of(i)]
        out[key] = entries
        print(f"  activity {label!r}: {len(entries)} items")
    return out


def extract_home_lists() -> dict[str, list]:
    """News and award highlights currently hard-coded into the home fragment."""
    src = read("index_zh.html")
    out: dict[str, list] = {}
    for div_id, key in (("publications", "latest_publications"), ("honors", "award_highlights")):
        match = re.search(rf'<div id="{div_id}"[^>]*>(.*?)</div>', src, re.S)
        if not match:
            continue
        out[key] = [text_of(i) for i in list_items(match.group(1)) if text_of(i)]
        print(f"  home {key}: {len(out[key])} items")

    # The news list is the first `.summary` block and has no id of its own.
    news = re.search(r'<div class="row summary">(.*?)</div>', src, re.S)
    if news:
        items = []
        for item in list_items(news.group(1)):
            text = text_of(item)
            if not text:
                continue
            entry: dict = {"text": text}
            refs = links_of(item)
            if refs:
                entry["links"] = refs
            year = re.match(r"(\d{4})", text)
            if year:
                entry["year"] = int(year.group(1))
            items.append(entry)
        out["news"] = items
        print(f"  home news: {len(items)} items")
    return out


def main() -> None:
    print("extracting legacy content…")
    honor = extract_honor()
    legacy_pubs = extract_legacy_publications()
    services = extract_services()
    activities = extract_prof_activities()
    home = extract_home_lists()

    print()
    dump(
        DATA / "honors.yml",
        "# Awards and highlighted achievements.\n"
        "#\n"
        "# Migrated from contents/honor_zh.html section A by\n"
        "# scripts/migration/extract-legacy-content.py, then hand-maintained. New awards\n"
        "# go here; there is no API to pull them from.\n"
        "#\n"
        "# `award_highlights` is the shorter list the old home page showed. It overlaps\n"
        "# `achievements` on purpose -- the home page wants a teaser, not the full list.",
        {
            "achievements": honor.get("achievements", []),
            "award_highlights": home.get("award_highlights", []),
        },
    )

    # Conference abstracts, patents and books have no DOI-based source, so the
    # legacy pages are the only record. Merge both pages, de-duplicating on the
    # citation text.
    def merged(key: str) -> list[str]:
        seen: dict[str, str] = {}
        for entry in honor.get(key, []):
            citation = entry["citation"] if isinstance(entry, dict) else entry
            seen.setdefault(re.sub(r"[^\w]+", "", citation.lower())[:90], citation)
        for citation in legacy_pubs.get(key, []):
            seen.setdefault(re.sub(r"[^\w]+", "", citation.lower())[:90], citation)
        return list(seen.values())

    conference = merged("conference_papers")
    patents = merged("patents")
    books = [e["citation"] if isinstance(e, dict) else e for e in honor.get("books", [])]

    dump(
        DATA / "publications-legacy.yml",
        "# Publications that the automated ORCID + Crossref sync cannot reach.\n"
        "#\n"
        "# Conference abstracts, patents and books are not registered as DOIs the way\n"
        "# journal articles are, so scripts/fetch-publications.mjs will never find them.\n"
        "# This file is their only record: it was migrated out of contents/honor_zh.html\n"
        "# and the never-routed contents/publication_zh.html, and is now hand-maintained.\n"
        "#\n"
        "# Entries are free-text citations, matching how the old site rendered them. The\n"
        "# trailing asterisk on an author marks the corresponding author.",
        {
            "conference_papers": conference,
            "patents": patents,
            "books": books,
        },
    )

    dump(
        DATA / "services.yml",
        "# Web servers and software tools published by the lab.\n"
        "#\n"
        "# Migrated from contents/service_zh.html. These are the lab's most-cited public\n"
        "# artifacts and the pages most likely to be searched for by name, so each one\n"
        "# gets its own indexable section.",
        {"services": services},
    )

    dump(
        DATA / "professional-activities.yml",
        "# Program-committee service, invited talks and journal review activity.\n"
        "#\n"
        "# Migrated from contents/prof_acts_zh.html -- a page that existed but was never\n"
        "# reachable: no router entry and no navigation link pointed at it.",
        activities,
    )

    dump(
        DATA / "news.yml",
        "# Lab news, newest first.\n"
        "#\n"
        "# Migrated from the hard-coded list in contents/index_zh.html. Hand-maintained:\n"
        "# add new items at the top.",
        {"news": home.get("news", [])},
    )

    print()
    print("summary of migrated item counts:")
    print(f"  achievements        {len(honor.get('achievements', []))}")
    print(f"  conference papers   {len(conference)}")
    print(f"  patents             {len(patents)}")
    print(f"  books               {len(books)}")
    print(f"  services            {len(services)}")
    print(f"  news                {len(home.get('news', []))}")
    for key, entries in activities.items():
        print(f"  activities/{key:<17} {len(entries)}")
    print(f"\n  legacy journal papers seen: {len(honor.get('journal_papers', []))} (honor) + "
          f"{len(legacy_pubs.get('journal_papers', []))} (publication page)")
    print("  -> not migrated: superseded by data/publications.yml from ORCID + Crossref")


if __name__ == "__main__":
    main()
