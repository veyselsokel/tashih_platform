#!/bin/bash

# This script helps test if the paths are correctly resolved

# Laravel root directory
LARAVEL_ROOT=$(pwd)

# Config file path
CONFIG_PATH="${LARAVEL_ROOT}/storage/app/fiyat_takip_config.json"
LOG_PATH="${LARAVEL_ROOT}/storage/logs/price-tracker.log"
PYTHON_PATH="${LARAVEL_ROOT}/python/price-tracker.py"

echo "Testing path resolution..."
echo "Laravel root: ${LARAVEL_ROOT}"
echo ""

# Create test config
echo "Creating test config file at: ${CONFIG_PATH}"
mkdir -p $(dirname "${CONFIG_PATH}")
echo '{"test":"data"}' > "${CONFIG_PATH}"

# Create test log file
echo "Creating test log file at: ${LOG_PATH}"
mkdir -p $(dirname "${LOG_PATH}")
touch "${LOG_PATH}"
chmod 777 "${LOG_PATH}"

# Check if Python is installed
if command -v python3 &> /dev/null; then
    echo "Python 3 is available"
    PYTHON_CMD="python3"
elif command -v python &> /dev/null; then
    echo "Python is available"
    PYTHON_CMD="python"
else
    echo "ERROR: Python not found"
    exit 1
fi

# Test Python access to the config
echo ""
echo "Testing Python's ability to access the config file..."
$PYTHON_CMD -c "
import os
import json

config_path = '${CONFIG_PATH}'
print(f'Checking config path: {config_path}')
print(f'Path exists: {os.path.exists(config_path)}')

if os.path.exists(config_path):
    try:
        with open(config_path, 'r') as f:
            config = json.load(f)
        print(f'Config content: {config}')
    except Exception as e:
        print(f'Error reading config: {e}')
else:
    print('Path does not exist!')
    parent_dir = os.path.dirname(config_path)
    if os.path.exists(parent_dir):
        print(f'Parent directory exists: {parent_dir}')
        print('Contents:')
        for item in os.listdir(parent_dir):
            print(f' - {item}')
    else:
        print(f'Parent directory does not exist: {parent_dir}')
"

# Cleanup
echo ""
echo "Cleaning up test files..."
rm "${CONFIG_PATH}"
echo "Done!"