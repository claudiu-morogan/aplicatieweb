# Docker — quickstart

From the project root you can start the app using the Compose file in the `docker/` folder.

Build and run (from project root):

```bash
docker compose -f docker/docker-compose.yml up --build -d
```

Or from the `docker/` folder:

```bash
cd docker
docker compose up --build -d
```

Open the app in a browser at `http://localhost:8080` or test with curl:

```bash
curl -I http://localhost:8080
```

Check container status and logs:

```bash
docker compose -f docker/docker-compose.yml ps
docker compose -f docker/docker-compose.yml logs -f
```

Stop and remove containers:

```bash
docker compose -f docker/docker-compose.yml down
```

Notes:
- The Compose file uses the repo root as the build context so the top-level `.dockerignore` is still respected.
- The service mounts the project directory into the container for easy local development.
