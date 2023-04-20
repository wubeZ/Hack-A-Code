const express = require('express');
const routes = require('./common/routers');
const helmet = require('helmet');
const cors = require('cors');
const compression = require('compression');


const app = express()

app.disable('x-powered-by')
app.use(helmet())
app.use(cors())
app.use(compression())
app.use(
    express.urlencoded({extended : true,
        limit: process.env.REQUEST_LIMIT || '100kb'}))

app.use(express.json())

app.get('/v1', (request, response)=>{
    response.status(200).json('health check: OK')
})

app.use('/v1/', routes);

module.exports = app;


