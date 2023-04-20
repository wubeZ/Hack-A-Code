const sqlite3 = require('sqlite3').verbose();
require('../common/env')
const logger = require('./../common/logger')

const DB_FILE = process.env.DB_FILE;

async function connect() {
    const db = new sqlite3.Database(DB_FILE, sqlite3.OPEN_READWRITE,(err) => {
        if (err) {
            logger.info(err.message);
            throw err;
        }
        logger.info('Connected to the SQLite database.');
        
        const UserTable = `
                CREATE TABLE IF NOT EXISTS users (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    username TEXT NOT NULL,
                    password TEXT NOT NULL,
                    email TEXT NOT NULL UNIQUE,
                    full_name TEXT NOT NULL,
                    question_count INTEGER DEFAULT 0,
                    date_joined DATETIME DEFAULT CURRENT_TIMESTAMP
                );
                `;

        db.run(UserTable, (err) => {
        if (err) {
            logger.info(err.message);
            throw err;
        }

        });

        const QuestionTable = `
                CREATE TABLE IF NOT EXISTS question (
                    id INTEGER PRIMARY KEY,
                    title TEXT NOT NULL UNIQUE,
                    description TEXT NOT NULL,
                    topic TEXT NOT NULL,
                    difficulty TEXT NOT NULL,
                    acceptance FLOAT NOT NULL,
                    status INTEGER DEFAULT 0
                );
                `;

        db.run(QuestionTable, (err) => {
            if (err){
                logger.info(err.message);
                throw err;
            }
        });

        const SubmissionTable = `
                CREATE TABLE IF NOT EXISTS submission (
                    id INTEGER PRIMARY KEY,
                    question INTEGER NOT NULL,
                    status INTEGER DEFAULT 0,
                    path TEXT NOT NULL
                );
                `;

        db.run(SubmissionTable, (err) => {
            if (err){
                logger.info(err.message);
                throw err;
            }
        });

    });
   
    return db;

}

module.exports = {
    connect
};