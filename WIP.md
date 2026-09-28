## 2026-09-28 — hay-wordpress main @ 583d676 (github.com/hay-chat/hay-wordpress), NOT pushed

**Works:** Submission polish committed: Requires at least/PHP headers, theme whitelist, escaped
settings link, translatable placeholders, uninstall.php, Contributors: rgrjnr. Plugin Check (WP 7.1
in docker, excludes per .distignore) = no errors. Clean zip built via .distignore at
../hay-chat.zip (LICENSE, hay-chat.php, readme.txt, uninstall.php only — no .git).
**Gotcha:** ~/Documents/code/hay/hay-wordpress is a STALE uncommitted copy (old "Hay Chat" 1.0.0,
app.usehay.com); it also got edits this session. Delete it or replace with a clone of this repo.
**Next:** push; upload ../hay-chat.zip at wordpress.org/plugins/developers/add/ (account rgrjnr,
email rogerjunior.com — expect an ownership question for the "Hay.chat" name); then listing assets.

## 2026-08-27 — hay-wordpress main (github.com/hay-chat/hay-wordpress)

**Works:** Hay.chat-branded plugin, top-level menu w/ logo, eu.hay.chat default, widget js/css
enqueued from {base}/v1/webchat/, "Connect with Hay.chat" redirect flow (core side: hay-core PR #74).
Plugin Check clean except zip-only findings (.gitignore/.github → excluded by .distignore).
CI: plugin-check on push; deploy.yml pushes to WP.org SVN on GitHub release (needs SVN_USERNAME/
SVN_PASSWORD secrets — set after WP.org approves the slug).
**Half-built:** No assets yet (icon-256.png, banner-1544x500.png, screenshots) for the WP.org listing.
**Next:** submit zip at wordpress.org/plugins/developers/add/ from a shared WP.org account; slug hay-chat.
