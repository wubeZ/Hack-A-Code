const express = require('express');
const routes = require('./common/routers')

const app = express()

app.use(express.urlencoded({extended : true}))
app.use(express.json())

app.get('/v1', (request, response)=>{
    response.status(200).json('health check: OK')
})

app.use('/v1/', routes);



module.exports = app;


