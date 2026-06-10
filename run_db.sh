#!/usr/bin/env bash
set -e

# Ensure mise variables are present
if [ -z "$MYSQL_DATA_DIR" ]; then
    echo "Error: mise environment variables not loaded."
    exit 1
fi

# Trap exits, interrupts, and terminations to kill any lingering processes
trap 'echo "Stopping local database components..."; kill 0' EXIT INT TERM

# 1. Initialization Step
if [ ! -d "$MYSQL_DATA_DIR" ]; then
    echo "Initializing isolated MySQL data directory..."
    mkdir -p "$MYSQL_DATA_DIR"
    
    mysqld --no-defaults \
        --initialize-insecure \
        --user="$USER" \
        --datadir="$MYSQL_DATA_DIR"
        
    echo "Database directory initialized. Bootstrapping configurations..."
    
    # Start the engine bound directly to this shell session's job control
    mysqld --no-defaults --user="$USER" --datadir="$MYSQL_DATA_DIR" \
        --port="$MYSQL_TCP_PORT" --socket="$MYSQL_UNIX_SOCKET" --skip-networking &
    BOOTSTRAP_PID=$!
    
    # Wait for execution availability
    until mysqladmin -u root --socket="$MYSQL_UNIX_SOCKET" ping &>/dev/null; do
        sleep 0.5
    done
    
    # Execute structural user assignments
    mysql -u root --socket="$MYSQL_UNIX_SOCKET" <<-EOSQL
        ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '${MYSQL_PASSWORD}';
        CREATE DATABASE IF NOT EXISTS \`${MYSQL_DATABASE}\`;
        CREATE USER IF NOT EXISTS '${MYSQL_USER}'@'127.0.0.1' IDENTIFIED WITH mysql_native_password BY '${MYSQL_PASSWORD}';
        GRANT ALL PRIVILEGES ON \`${MYSQL_DATABASE}\`.* TO '${MYSQL_USER}'@'127.0.0.1';
        FLUSH PRIVILEGES;
EOSQL

    # Kill bootstrap process cleanly via trapped PID
    kill "$BOOTSTRAP_PID"
    wait "$BOOTSTRAP_PID" 2>/dev/null
    echo "Initialization complete!"
fi

# 2. Direct Foreground Execution (Attached to current shell)
echo "Starting local MySQL server on port $MYSQL_TCP_PORT..."
echo "Press Ctrl+C to stop the database."

# Using 'exec' replaces the current shell process with mysqld.
# It inherits the shell's process ID (PID) directly, meaning if the shell dies, MySQL dies instantly.
exec mysqld \
    --defaults-file="$MYSQL_REPO_ROOT/my.cnf" \
    --user="$USER" \
    --datadir="$MYSQL_DATA_DIR" \
    --port="$MYSQL_TCP_PORT" \
    --socket="$MYSQL_UNIX_SOCKET" \
    --pid-file="$MYSQL_PID_FILE" \
    --mysqlx=OFF \
    "$@"

sleep 2

pkill -9 mysqld
pkill -9 mysql