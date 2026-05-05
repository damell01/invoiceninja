# Private Repo Deploy Options

If your Bellflow repo is private, there are three practical ways to deploy it.

## Easiest: copy the repo to the VPS

This is the easiest option if you do not want to deal with Git auth on the server yet.

Typical flow:

1. Clone or keep the repo on your own machine
2. Upload it to the VPS with SCP, SFTP, rsync, or a zip file
3. SSH into the VPS
4. Go to the uploaded project folder
5. Run the Bellflow Docker or CLI install script

This avoids setting up Git credentials on the VPS.

## Good long-term option: add an SSH deploy key

Use this if you want the VPS to run `git clone` or `git pull` directly from your private repo.

Typical flow:

1. Generate an SSH key on the VPS
2. Copy the public key
3. Add it to your Git host as a deploy key or machine-user key
4. Clone the repo on the VPS using the SSH URL

Example:

```bash
ssh-keygen -t ed25519 -C "bellflow-vps"
cat ~/.ssh/id_ed25519.pub
```

Then add that public key to:

- GitHub deploy keys, or
- GitLab deploy keys, or
- another Git provider's SSH key section

After that, the VPS can use:

```bash
git clone git@github.com:your-org/your-private-repo.git
```

## Alternative: HTTPS with a personal access token

This also works, but many people prefer SSH keys over keeping a token on the server.

## Recommended choice

If you want the easiest first install:

- upload or copy the repo to the VPS
- run the install script there

If you want easier future updates:

- set up an SSH deploy key on the VPS
- use `git pull` for updates

If you later want a cleaner Bellflow UI, you can also adjust the module visibility env vars after deploy without uninstalling the code.

## Bellflow deploy scripts after the repo is on the VPS

Docker:

```bash
./scripts/install-bellflow-docker.sh
./scripts/update-bellflow-docker.sh
```

Host / CLI:

```bash
./scripts/install-bellflow.sh
```
