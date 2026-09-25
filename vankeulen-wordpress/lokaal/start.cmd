@echo off
REM Start de website lokaal op http://127.0.0.1:9400 (vereist Node.js 20+).
cd /d "%~dp0.."
npx --yes @wp-playground/cli@latest server --mount=./wp-content/themes/vankeulen:/wordpress/wp-content/themes/vankeulen --mount=./wp-content/plugins/vankeulen-core:/wordpress/wp-content/plugins/vankeulen-core --blueprint=./lokaal/blueprint.json --port=9400
