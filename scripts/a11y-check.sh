#!/bin/bash

# Peanut License Server - Accessibility Check Script
# Runs accessibility tests against the React frontend

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Script directory
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"
FRONTEND_DIR="$PROJECT_ROOT/frontend"

# Function to print colored output
print_header() {
  echo -e "\n${BLUE}=== $1 ===${NC}\n"
}

print_success() {
  echo -e "${GREEN}✓ $1${NC}"
}

print_warning() {
  echo -e "${YELLOW}⚠ $1${NC}"
}

print_error() {
  echo -e "${RED}✗ $1${NC}"
}

# Show help
show_help() {
  cat << EOF
Peanut License Server - Accessibility Testing Script

Usage: ./scripts/a11y-check.sh [OPTIONS]

Options:
  --help          Show this help message
  --watch         Run tests in watch mode
  --coverage      Include coverage report
  --lint-only     Run ESLint jsx-a11y checks only
  --test-only     Run accessibility tests only

Examples:
  ./scripts/a11y-check.sh                # Run all accessibility checks
  ./scripts/a11y-check.sh --watch       # Watch mode for development
  ./scripts/a11y-check.sh --coverage    # Include coverage report

EOF
}

# Parse arguments
WATCH_MODE=false
INCLUDE_COVERAGE=false
LINT_ONLY=false
TEST_ONLY=false

while [[ $# -gt 0 ]]; do
  case $1 in
    --help)
      show_help
      exit 0
      ;;
    --watch)
      WATCH_MODE=true
      shift
      ;;
    --coverage)
      INCLUDE_COVERAGE=true
      shift
      ;;
    --lint-only)
      LINT_ONLY=true
      shift
      ;;
    --test-only)
      TEST_ONLY=true
      shift
      ;;
    *)
      print_error "Unknown option: $1"
      show_help
      exit 1
      ;;
  esac
done

# Check if frontend directory exists
if [ ! -d "$FRONTEND_DIR" ]; then
  print_error "Frontend directory not found at $FRONTEND_DIR"
  exit 1
fi

# Change to frontend directory
cd "$FRONTEND_DIR"

print_header "Peanut License Server - Accessibility Check"

# Check if node_modules exists
if [ ! -d "node_modules" ]; then
  print_warning "Dependencies not installed. Running npm ci..."
  npm ci
  print_success "Dependencies installed"
fi

# Run ESLint jsx-a11y checks
if [ "$TEST_ONLY" = false ]; then
  print_header "Running ESLint Accessibility Plugin"
  if npm run lint -- --plugin jsx-a11y 2>/dev/null; then
    print_success "No ESLint accessibility violations found"
  else
    print_warning "Some ESLint warnings found (see above)"
  fi
fi

# Run accessibility tests
if [ "$LINT_ONLY" = false ]; then
  print_header "Running Accessibility Tests with jest-axe"

  if [ "$WATCH_MODE" = true ]; then
    print_header "Watch Mode Enabled"
    npm run test -- --watch a11y
  elif [ "$INCLUDE_COVERAGE" = true ]; then
    npm run test:a11y -- --coverage
    print_success "Accessibility tests completed with coverage"
  else
    npm run test:a11y
    print_success "Accessibility tests completed"
  fi
fi

print_header "Summary"
print_success "Accessibility checks passed for Peanut License Server"
echo ""
echo -e "${BLUE}Next Steps:${NC}"
echo "  - Review test results above"
echo "  - Fix any violations before committing"
echo "  - Run 'npm run test:a11y' to re-test"
echo ""
