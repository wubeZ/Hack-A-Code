const router = require('express').Router()

// routers import
userRouter = require('../resources/User/routes')
questionRouter = require('../resources/Question/routes')


// higher level routing
router.use('/user', userRouter);
router.use('/question', questionRouter);



module.exports = router;