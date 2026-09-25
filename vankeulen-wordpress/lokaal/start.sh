#!/usr/bin/env sh
# Start de website lokaal op http://127.0.0.1:9400 (vereist Node.js 20+).
# Inloggen gebeurt automatisch (beheer: http://127.0.0.1:9400/wp-admin/).
cd "$(dirname "$0")/.." || exit 1
npx --yes @wp-playground/cli@latest server \
	--mount=./wp-content/themes/vankeulen:/wordpress/wp-content/themes/vankeulen \
	--mount=./wp-content/plugins/vankeulen-core:/wordpress/wp-content/plugins/vankeulen-core \
	--blueprint=./lokaal/blueprint.json \
	--port=9400 "$@"
