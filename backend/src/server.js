const app = require('./app')
const connection = require('./services/db')
const os = require('os')
require('./common/env.js')

const PORT = process.env.PORT_URI || '8000'

connection.connect()
  .then((db) => {
    // Do something with the database connection
    app.listen(PORT, () => {
        console.log(`up and running in ${process.env.NODE_ENV} mode @: ${os.hostname()} on port ${PORT}`)
    });
  })
  .catch((err) => {
    console.error(err.message);
  });
