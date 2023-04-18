import app from './app.js';
import './common/env.js'

const PORT = process.env.PORT_URI || '8000'
app.listen(PORT, (req, res)=>{
    console.log(`up and running in ${process.env.NODE_ENV} mode on port ${PORT}`)
})