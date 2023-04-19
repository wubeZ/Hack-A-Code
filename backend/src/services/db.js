const sqlite3 = require('sqlite3').verbose();
require('../common/env')

const DB_FILE = process.env.DB_FILE;

async function connect() {
    const db = new sqlite3.Database(DB_FILE, sqlite3.OPEN_READWRITE,(err) => {
        if (err) {
            console.error(err.message);
            throw err;
        }
        console.log('Connected to the SQLite database.');
        
        const UserTable = `
                CREATE TABLE IF NOT EXISTS users (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    username TEXT NOT NULL,
                    password TEXT NOT NULL,
                    email TEXT NOT NULL,
                    full_name TEXT NOT NULL,
                    date_joined DATETIME DEFAULT CURRENT_TIMESTAMP
                );
                `;

        db.run(UserTable, (err) => {
        if (err) {
            console.error(err.message);
            throw err;
        }

        });

    });
   
    return db;

}

module.exports = {
    connect
};