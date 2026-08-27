## 2026-08-27 — hay-wordpress main (github.com/hay-chat/hay-wordpress)

**Works:** Hay.chat-branded plugin, top-level menu w/ logo, eu.hay.chat default, widget js/css
enqueued from {base}/v1/webchat/, "Connect with Hay.chat" redirect flow (core side: hay-core PR #74).
Plugin Check clean except zip-only findings (.gitignore/.github → excluded by .distignore).
CI: plugin-check on push; deploy.yml pushes to WP.org SVN on GitHub release (needs SVN_USERNAME/
SVN_PASSWORD secrets — set after WP.org approves the slug).
**Half-built:** No assets yet (icon-256.png, banner-1544x500.png, screenshots) for the WP.org listing.
**Next:** submit zip at wordpress.org/plugins/developers/add/ from a shared WP.org account; slug hay-chat.
