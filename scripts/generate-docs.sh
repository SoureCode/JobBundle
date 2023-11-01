#!/usr/bin/env bash

CURRENT_DIRECTORY="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIRECTORY="$(dirname "$CURRENT_DIRECTORY")"
DOCS_DIRECTORY="$PROJECT_DIRECTORY/docs"
TESTS_DIRECTORY="$PROJECT_DIRECTORY/tests"
BIN_DIRECTORY="$TESTS_DIRECTORY/app/bin"
CONSOLE="$BIN_DIRECTORY/console"

COMMANDS="job:run"

NEWLINE=$'\n'

for COMMAND in $COMMANDS; do
  MD_FILE_NAME="$(echo "$COMMAND" | tr ':' '-')"
  MARKDOWN="${NEWLINE}# Command: ${COMMAND}${NEWLINE}${NEWLINE}## Usage${NEWLINE}${NEWLINE}\`\`\`shell${NEWLINE}"
  USAGE="$("$CONSOLE" "$COMMAND" --help -vvv)"
  MARKDOWN="${MARKDOWN}${USAGE}${NEWLINE}\`\`\`${NEWLINE}${NEWLINE}"

  echo "$MARKDOWN" >"$DOCS_DIRECTORY/$MD_FILE_NAME.md"
done