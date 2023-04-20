const app = require('./app')
const connection = require('./services/db')
require('./common/env.js')
const logger = require('./common/logger')

const PORT = process.env.PORT_URI || '8000'

connection.connect()
  .then((db) => {
    app.listen(PORT, () => {
        logger.info(`up and running in ${process.env.NODE_ENV} mode on port ${PORT}`)
    });
  })
  .catch((err) => {
    logger.info(err.message);
  });
