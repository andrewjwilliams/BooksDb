## BooksDB

A sample single page application to record books and authors, devloped in Laravel and Vue.js

## Configuration

In addition to the standard Laravel `.env` variables, this app uses:

- `LIBRARY_NAME` — printed in small text at the top of each book's barcode label (`/books/{id}/label`). Falls back to `APP_NAME` when unset. The site domain shown next to it is derived from `APP_URL`.

## Printing barcode labels

Each book has a printable label at `GET /books/{id}/label`. The label contains a Code 128 barcode of the book's numeric ID, the library name + domain, the Dewey classification (printed vertically), and the book title + author. The page is sized for 51 × 19 mm library barcode stock via `@page` CSS; override `--label-w` / `--label-h` in `resources/views/labels/book.blade.php` for other sizes.

Buttons on the book list (datatable) and book detail view open the label page in a new tab; a "Print" button in the page triggers the browser print dialog.

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
