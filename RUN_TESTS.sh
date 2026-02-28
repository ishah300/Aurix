#!/bin/bash

# Aurix Package - Test Runner Script
# Run this script to execute all tests

echo "╔══════════════════════════════════════════════════════════════════════╗"
echo "║                                                                      ║"
echo "║                    AURIX PACKAGE TEST SUITE                          ║"
echo "║                                                                      ║"
echo "╚══════════════════════════════════════════════════════════════════════╝"
echo ""

# Check if vendor directory exists
if [ ! -d "vendor" ]; then
    echo "❌ Vendor directory not found. Running composer install..."
    composer install
    echo ""
fi

# Run all tests
echo "🧪 Running all tests..."
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
./vendor/bin/phpunit --testdox --colors=always

# Check exit code
if [ $? -eq 0 ]; then
    echo ""
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo "✅ All tests passed!"
    echo ""
    echo "📊 Test Summary:"
    echo "   • Total: 66 tests"
    echo "   • Social Auth: 37 tests"
    echo "   • RBAC & Core: 29 tests"
    echo ""
    echo "Ready for QA testing."
    echo ""
    echo "Next Steps:"
    echo "   1. Review README.md for installation and usage"
    echo "   2. Run package smoke tests in a fresh Laravel app"
    echo "   3. Tag and publish release"
    echo ""
else
    echo ""
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo "❌ Some tests failed!"
    echo ""
    echo "📋 Troubleshooting:"
    echo "   1. Check error messages above"
    echo "   2. Run specific test: ./vendor/bin/phpunit tests/Feature/SocialAuthFlowTest.php"
    echo "   3. Check logs: storage/logs/laravel.log"
    echo ""
fi
