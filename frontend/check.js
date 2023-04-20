function main(){
const sqlite3 = require('sqlite3').verbose();

// open the database
let db = new sqlite3.Database('/Users/user/Downloads/sqlite.db');

// perform a query
db.all('SELECT * FROM demo WHERE ID = 1', [], (err, rows) => {
  if (err) {
    throw err;
  }

  // do something with the rows
//   console.log(rows[0]['ID']);
  return rows
});

// close the database connection
db.close();
}