## 2026-10-09 — hay-wordpress main @ 8d5ce25 (pushed) — WP.org APPROVED, slug haychat

**Works:** WP.org approved (review P0TDX377478HGN). Listing assets built in .wordpress-org/ (icon 128/256/svg,
banner 772x250 + 1544x500 rendered from .wordpress-org-src/banner.html via headless Chrome, 3 screenshots from
local docker WP at localhost:8085). readme.txt has Screenshots section. 1.0.0 COMMITTED to SVN (trunk + tags/1.0.0 + assets);
wordpress.org/plugins/haychat is live, icon/banner/screenshots serving. SVN wc: ~/Documents/code/hay/haychat-svn.
SVN_USERNAME/SVN_PASSWORD GitHub secrets set.
**Next:** nothing pending. Future release = bump Version (haychat.php) + Stable tag/changelog (readme.txt),
publish a GitHub release → deploy.yml pushes to SVN.

## 2026-10-08 — hay-wordpress main (github.com/hay-chat/hay-wordpress), NOT pushed

**Works:** WP.org review R haychat/rgrjnr/4Oct26 flagged text domain "hay-chat" ≠ slug "haychat".
Fixed: all 34 gettext calls + Text Domain header → `haychat`; main file renamed haychat.php;
readme install path + CI slugs updated. Clean zip rebuilt at ../haychat.zip (folder haychat/).
**Not verified:** no local php / docker daemon down, so Plugin Check + WP_DEBUG test not re-run
(change is pure string replace, diff verified to touch only the domain string).
**Next:** push; start docker, run Plugin Check on ../haychat.zip; upload at
wordpress.org/plugins/developers/add/ ; reply to review email (concise: text domain fixed).

## 2026-09-28 — hay-wordpress main @ b0872c7 (github.com/hay-chat/hay-wordpress), NOT pushed

**Works:** Browser-tested settings page (save, notice, plugins link) + widget on frontend. Submission polish committed: Requires at least/PHP headers, theme whitelist, escaped
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
