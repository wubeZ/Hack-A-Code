import express, {request,response}  from "express";
// import routes from '../src/common/routes.js'

const app = express()

app.use(express.urlencoded({extended : true}))
app.use(express.json())

app.get('/', (request, response)=>{
    response.status(200).json('health check: OK')
})

// app.use('/v1/', routes) :TODO setup the routes

export default app;


