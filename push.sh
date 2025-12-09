#!/bin/bash

# Script configuration
CWD=$(pwd)
CONFIG_DIR="${CWD}/.config"
TIMESTAMP=$(date +%Y%m%d%H%M%S)

# Database configurations
MYSQL_USER="root"
MYSQL_PASSWORD="NOV.2014.TEN"
MYSQL_DB="tpsys"
MONGODB_DB="tpsys"
MONGODB_USER="devOps"
MONGODB_PASS="working.Dev2"

# Export file paths
MYSQL_PROC_FILE="${CONFIG_DIR}/${TIMESTAMP}.proc.sql"
MYSQL_DB_FILE="${CONFIG_DIR}/${TIMESTAMP}.db.sql"
MONGODB_DUMP_DIR="${CONFIG_DIR}/${TIMESTAMP}.tpsys"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print messages
print_message() {
    echo -e "${2}${1}${NC}"
}

# Function to check if command was successful
check_status() {
    if [ $? -eq 0 ]; then
        print_message "$1" "${GREEN}"
    else
        print_message "$2" "${RED}"
        exit 1
    fi
}

# Function to clean previous dumps
clean_previous_dumps() {
    print_message "Cleaning previous database dumps..." "${YELLOW}"

    # Remove MySQL dumps
    if ls ${CONFIG_DIR}/*.sql 1> /dev/null 2>&1; then
        rm -rf ${CONFIG_DIR}/*.sql
        check_status "Previous MySQL dumps removed successfully" "Error removing MySQL dumps"
    else
        print_message "No previous MySQL dumps found" "${YELLOW}"
    fi

    # Remove MongoDB dumps
    if ls ${CONFIG_DIR}/*.tpsys 1> /dev/null 2>&1; then
        rm -rf ${CONFIG_DIR}/*.tpsys
        check_status "Previous MongoDB dumps removed successfully" "Error removing MongoDB dumps"
    else
        print_message "No previous MongoDB dumps found" "${YELLOW}"
    fi
}

# Function to export MySQL
export_mysql() {
    print_message "\nStarting MySQL export..." "${YELLOW}"

    # Export stored procedures
    print_message "Exporting stored procedures..." "${YELLOW}"
    mysqldump -u${MYSQL_USER} -p${MYSQL_PASSWORD} --no-data --routines --no-create-info --comments ${MYSQL_DB} > ${MYSQL_PROC_FILE}
    check_status "Stored procedures exported successfully to ${MYSQL_PROC_FILE}" "Error exporting stored procedures"

    # Export full database
    print_message "Exporting full database..." "${YELLOW}"
    mysqldump -u${MYSQL_USER} -p${MYSQL_PASSWORD} --comments --routines --triggers --events ${MYSQL_DB} > ${MYSQL_DB_FILE}
    check_status "Database exported successfully to ${MYSQL_DB_FILE}" "Error exporting database"
}

# Function to export MongoDB
export_mongodb() {
    print_message "\nStarting MongoDB export..." "${YELLOW}"
    # mongodump -d ${MONGODB_DB} -o ${MONGODB_DUMP_DIR}
    mongodump -d "$MONGODB_DB" -o "$MONGODB_DUMP_DIR" --username="$MONGODB_USER" --password="$MONGODB_PASS" --authenticationDatabase="admin"

    check_status "MongoDB exported successfully to ${MONGODB_DUMP_DIR}" "Error exporting MongoDB database"
}

# Function to handle git operations
git_push() {
    print_message "\nStarting Git operations..." "${YELLOW}"

    # Get commit message
    read -p "What are you committing? (Press Enter for default message) " title
    title=${title:-"System backup at $(date +"%Y-%m-%d %H:%M")"}

    # Get branch name
    read -p "To which branch? [dev] " branch
    branch=${branch:-"dev"}

    # Perform git operations
    git add -A
    check_status "Files staged successfully" "Error staging files"

    git commit -m "$title"
    check_status "Changes committed successfully" "Error committing changes"

    git push origin "$branch"
    check_status "Changes pushed to $branch successfully" "Error pushing to remote"
}

# Function to set file permissions
set_permissions() {
    print_message "\nSetting file permissions..." "${YELLOW}"
    find . -type d -not -path "./.log*" -exec chmod 0755 {} \;
    find . -type f -not -path "./.log*" -exec chmod 0644 {} \;    
    check_status "File permissions set successfully" "Error setting file permissions"
}

# Main execution
main() {
    # Create config directory if it doesn't exist
    mkdir -p ${CONFIG_DIR}

    # Execute functions in sequence
    clean_previous_dumps
    #export_mysql
    #export_mongodb
    git_push
    set_permissions

    print_message "\nBackup and push completed successfully!" "${GREEN}"
}

# Execute main function
main