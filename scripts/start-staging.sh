#!/bin/sh

set -eu

APP_DIR="/Users/icanacademy/icaneduspace-booking-staging"
CONTAINER_NAME="icaneduspace-booking-staging"
IMAGE_NAME="php:8.4-cli-alpine"
PORT="${STAGING_PORT:-8622}"
BIND_ADDRESS="${STAGING_BIND:-127.0.0.1}"
RECREATE="${STAGING_RECREATE:-no}"

detect_lan_ip() {
  if command -v ipconfig >/dev/null 2>&1; then
    for iface in en1 en0; do
      ipconfig getifaddr "$iface" 2>/dev/null && return 0
    done
  fi

  if command -v ifconfig >/dev/null 2>&1; then
    ifconfig | awk '/inet / && $2 !~ /^127\./ { print $2; exit }'
  fi
}

if ! command -v docker >/dev/null 2>&1; then
  echo "Docker CLI not found." >&2
  exit 1
fi

if ! docker info >/dev/null 2>&1; then
  echo "Docker is not ready." >&2
  exit 1
fi

if [ "$BIND_ADDRESS" != "127.0.0.1" ] && [ "$BIND_ADDRESS" != "0.0.0.0" ]; then
  echo "Unsupported STAGING_BIND value: $BIND_ADDRESS" >&2
  echo "Use 127.0.0.1 for local-only or 0.0.0.0 for LAN access." >&2
  exit 2
fi

if docker ps --format '{{.Names}}' | grep -qx "$CONTAINER_NAME" && [ "$RECREATE" = "yes" ]; then
  docker stop "$CONTAINER_NAME" >/dev/null
  docker rm "$CONTAINER_NAME" >/dev/null
fi

if docker ps --format '{{.Names}}' | grep -qx "$CONTAINER_NAME"; then
  echo "Staging container already running: $CONTAINER_NAME"
elif docker ps -a --format '{{.Names}}' | grep -qx "$CONTAINER_NAME"; then
  if [ "$RECREATE" = "yes" ]; then
    docker rm "$CONTAINER_NAME" >/dev/null
    docker run -d \
      --name "$CONTAINER_NAME" \
      --restart unless-stopped \
      -p "$BIND_ADDRESS:$PORT:$PORT" \
      -v "$APP_DIR:/app" \
      -w /app \
      --user "$(id -u):$(id -g)" \
      "$IMAGE_NAME" \
      php -d upload_max_filesize=20M -d post_max_size=24M artisan serve --host=0.0.0.0 --port="$PORT" >/dev/null
  else
    docker start "$CONTAINER_NAME" >/dev/null
  fi
else
  docker run -d \
    --name "$CONTAINER_NAME" \
    --restart unless-stopped \
    -p "$BIND_ADDRESS:$PORT:$PORT" \
    -v "$APP_DIR:/app" \
    -w /app \
    --user "$(id -u):$(id -g)" \
    "$IMAGE_NAME" \
    php -d upload_max_filesize=20M -d post_max_size=24M artisan serve --host=0.0.0.0 --port="$PORT" >/dev/null
fi

docker exec "$CONTAINER_NAME" php artisan optimize:clear >/dev/null || true

echo "Staging local URL: http://127.0.0.1:$PORT"
if [ "$BIND_ADDRESS" = "0.0.0.0" ]; then
  LAN_IP=$(detect_lan_ip || true)
  if [ -n "$LAN_IP" ]; then
    echo "Staging LAN URL:   http://$LAN_IP:$PORT"
  else
    echo "Staging LAN URL:   http://<this-mac-ip>:$PORT"
  fi
fi
