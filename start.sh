#!/bin/bash
echo "============================================"
echo "  Cinema Booking - PHP Server"
echo "============================================"
echo ""
echo "Starting server at http://localhost:8080"
echo "Press Ctrl+C to stop the server"
echo ""

cd "$(dirname "$0")"
php -S 0.0.0.0:8080 -t .

