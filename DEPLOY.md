# RMC portal — release workflow

This repo holds the **application overlay** for the Retro Motel Collective portal:
`config/`, `app/`, `resources/`, `routes/`, `public/css/`, and the DB
`migrations/` + `seeders/`. The full Laravel install and `vendor/` live on the
server and are **not** in Git — so releases never re-run Composer.

## Environments

| Env      | URL                             | Server path                                 | DB     |
|----------|----------------------------------|---------------------------------------------|--------|
| staging  | https://staging.retromotels.com | `~/public_html/staging.retromotels.com`     | SQLite |
| prod     | https://portal.retromotels.com  | `~/public_html/portal.retromotels.com`      | MySQL  |

The server also holds a bare-ish working clone of this repo at `~/repos/rmc-portal`,
which `deploy.sh` pulls from and overlays onto the env above.

## Making a change (the loop)

1. **Edit** the files in this overlay.
2. **Commit & push:**
   ```bash
   git add -A && git commit -m "describe the change" && git push
   ```
3. **Deploy to staging** (cPanel Terminal):
   ```bash
   ~/repos/rmc-portal/deploy.sh staging
   ```
   Check https://staging.retromotels.com.
4. **Promote to prod** when happy:
   ```bash
   ~/repos/rmc-portal/deploy.sh prod
   ```

That's it — no more base64 file transfers. `deploy.sh` pulls the latest commit,
copies the overlay onto the live install, runs any new migrations, and clears
the caches.

## One-time server setup

```bash
mkdir -p ~/repos
git clone git@github.com:retromotels/rmc-portal.git ~/repos/rmc-portal
chmod +x ~/repos/rmc-portal/deploy.sh
```
(Requires the server's read deploy key to be registered on the GitHub repo.)

## Rolling back

```bash
git -C ~/repos/rmc-portal log --oneline    # find a good commit
BRANCH=<good-sha> ~/repos/rmc-portal/deploy.sh prod
```
