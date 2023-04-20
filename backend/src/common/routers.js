const router = require('express').Router()

// routers import
userRouter = require('../resources/User/routes')
questionRouter = require('../resources/Question/routes')
submissionRouter = require('../resources/Submission/routes')


// higher level routing
router.use('/user', userRouter);
router.use('/question', questionRouter);
router.use('/submission', submissionRouter);


module.exports = router;