## BooksDB

A sample single page application to record books and authors, devloped in Laravel and Vue.js

## Deployment

The app is designed to run via `docker-compose`. The `Dockerfile` is multi-stage and builds the frontend assets (Vite), PHP vendor directory (Composer), and runtime images inside Docker — nothing needs to be pre-built before deploying.

### Updating an existing deployment

From a working local checkout, sync the source to the deployment host and rebuild:

```bash
# 1. Sync source. Excludes preserve the host's .env, storage/, and build artefacts.
rsync -a --delete \
  --exclude='.git' \
  --exclude='.env' \
  --exclude='node_modules' \
  --exclude='vendor' \
  --exclude='storage' \
  --exclude='public/build' \
  --exclude='public/hot' \
  ./ user@host:/path/to/booksdb/

# 2. Rebuild and restart.
ssh user@host "cd /path/to/booksdb && sudo docker-compose build && sudo docker-compose up -d"
```

### Files preserved on the deployment host

- `.env` — the host's own env (DB credentials, app key). Never overwrite from local.
- `storage/` — runtime state (cache, logs, sessions). The `app_storage` Docker volume is the source of truth for container storage.
- `public/build/` — regenerated inside the Docker build.

### Dependabot notes

This project pins Vue 2 via `@vitejs/plugin-vue2` and `@fortawesome/vue-fontawesome@^0.1`. Major bumps of `vue`, `vite`, or `@vitejs/plugin-vue2` will break the peer-dependency graph. Always run `npm install && npm run build` locally before merging a dependabot PR touching those packages.
