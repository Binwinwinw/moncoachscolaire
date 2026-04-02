#!/bin/bash
npx tailwindcss -i ./assets/css/input.css -o ./public/css/output.css --watch &
tailwind_pid=$!
npx php-cs-fixer fix --dry-run .
npx stylelint "**/*.css"
kill $tailwind_pid
