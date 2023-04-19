const router = require('express').Router()

// routers import
userRouter = require('../resources/User/routes')


// higher level routing
router.use('/user', userRouter);



module.exports = router;