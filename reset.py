#!/usr/bin/env python3
import sqlite3

def remove_all_items_from_all_tables(db_path):
    # Connect to the SQLite database
    conn = sqlite3.connect(db_path)
    cursor = conn.cursor()

    # Get the list of all table names
    cursor.execute("SELECT name FROM sqlite_master WHERE type='table';")
    tables = cursor.fetchall()

    # Iterate over the tables and delete all rows from each
    for table in tables:
        table_name = table[0]
        try:
            cursor.execute(f"DELETE FROM {table_name};")
            print(f"Deleted all rows from table: {table_name}")
        except sqlite3.Error as e:
            print(f"Error deleting rows from {table_name}: {e}")
    
    # Commit the changes and close the connection
    conn.commit()
    conn.close()

# Example usage
db_path = "workout.db"
remove_all_items_from_all_tables(db_path)
